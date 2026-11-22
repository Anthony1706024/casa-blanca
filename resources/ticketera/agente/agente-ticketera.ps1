# Agente de la ticketera - Casa Blanca
# Corre en la PC donde está conectada la ticketera. Cada pocos segundos pide a la app los tickets
# pendientes (voucher de cobro y tickets de cocina), los imprime en orden con imprimir-raw.ps1
# y avisa a la app si salieron o si hubo un error. Si no hay internet, espera y sigue.
# Configuración: config.json en esta misma carpeta (ver config.ejemplo.json y LEEME.txt).

$ErrorActionPreference = 'Stop'
$carpeta = $PSScriptRoot
$carpetaLogs = Join-Path $carpeta 'logs'
$rutaConfig = Join-Path $carpeta 'config.json'

# imprimir-raw.ps1 va en esta carpeta; en el repositorio está un nivel arriba
$scriptImprimir = Join-Path $carpeta 'imprimir-raw.ps1'
if (-not (Test-Path $scriptImprimir)) { $scriptImprimir = Join-Path (Split-Path $carpeta -Parent) 'imprimir-raw.ps1' }

New-Item -ItemType Directory -Force $carpetaLogs | Out-Null
[Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12

function Escribir-Registro([string]$texto) {
    $linea = '{0}  {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $texto
    Write-Host $linea
    try {
        Add-Content -Path (Join-Path $carpetaLogs ('agente-{0}.log' -f (Get-Date -Format 'yyyy-MM-dd'))) -Value $linea -Encoding UTF8
    } catch { }
}

# Borra los registros de más de 15 días
function Limpiar-Registros {
    Get-ChildItem $carpetaLogs -Filter 'agente-*.log' -ErrorAction SilentlyContinue |
        Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-15) } |
        Remove-Item -Force -ErrorAction SilentlyContinue
}

function Leer-Config {
    if (-not (Test-Path $rutaConfig)) { throw "Falta config.json en $carpeta. Copia config.ejemplo.json como config.json y complétalo." }
    $c = Get-Content $rutaConfig -Raw -Encoding UTF8 | ConvertFrom-Json
    foreach ($campo in 'url', 'clave', 'impresora') {
        if (-not $c.$campo -or $c.$campo -like '*PEGA*') { throw "Falta '$campo' en config.json." }
    }
    if (-not $c.intervalo -or [int]$c.intervalo -lt 1) { $c | Add-Member -NotePropertyName intervalo -NotePropertyValue 3 -Force }
    return $c
}

function Llamar([string]$metodo, [string]$ruta, $cuerpo = $null) {
    $parametros = @{
        Uri             = $config.url.TrimEnd('/') + $ruta
        Method          = $metodo
        Headers         = @{ Authorization = "Bearer $($config.clave)"; Accept = 'application/json' }
        TimeoutSec      = 20
        UseBasicParsing = $true
    }
    if ($null -ne $cuerpo) {
        $parametros.Body = [Text.Encoding]::UTF8.GetBytes(($cuerpo | ConvertTo-Json -Compress))
        $parametros.ContentType = 'application/json; charset=utf-8'
    }
    return Invoke-RestMethod @parametros
}

# Explica el problema en palabras para el registro
function Describir-Error($registro) {
    $respuesta = $registro.Exception.Response
    if ($null -ne $respuesta) {
        $codigo = [int]$respuesta.StatusCode
        switch ($codigo) {
            401 { return 'CLAVE INCORRECTA: la clave de config.json no coincide con TICKETERA_CLAVE de la app. No se imprimirá nada hasta corregirla.' }
            403 { return 'La app no tiene la cola de impresión activada (falta TICKETERA_CLAVE en su .env o en Railway).' }
            404 { return "La app respondió 404: revisa la url de config.json ($($config.url))." }
            429 { return 'La app pidió esperar (demasiadas consultas). Se reintenta solo.' }
            default { return "La app respondió con error $codigo. Se reintenta solo." }
        }
    }
    return "Sin conexión con la app ($($config.url)): $($registro.Exception.Message) Se reintenta solo."
}

# Avisa a la app; reintenta unas veces por si se cortó internet justo en ese momento
function Avisar([string]$ruta, $cuerpo = $null) {
    for ($i = 1; $i -le 3; $i++) {
        try { Llamar 'POST' $ruta $cuerpo | Out-Null; return $true } catch { Start-Sleep -Seconds 2 }
    }
    Escribir-Registro "No se pudo avisar a la app ($ruta). Se volverá a intentar cuando la vuelva a ofrecer."
    return $false
}

# Ids ya impresos aquí: si la app los vuelve a ofrecer (no le llegó el aviso), no se imprimen de nuevo
$impresasAqui = New-Object 'System.Collections.Generic.List[int]'

function Imprimir-Trabajo($trabajo) {
    $id = [int]$trabajo.id

    if ($impresasAqui.Contains($id)) {
        Avisar "/ticketera/$id/impresa" | Out-Null
        return $true
    }

    $archivo = [IO.Path]::GetTempFileName()
    try {
        [IO.File]::WriteAllBytes($archivo, [Convert]::FromBase64String($trabajo.datos))

        # Proceso aparte: imprimir-raw.ps1 termina con exit 1 si falla
        $ErrorActionPreference = 'Continue'
        $salida = & powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File $scriptImprimir -Impresora $config.impresora -Archivo $archivo 2>&1
        $codigo = $LASTEXITCODE
        $ErrorActionPreference = 'Stop'

        if ($codigo -eq 0) {
            $impresasAqui.Add($id)
            if ($impresasAqui.Count -gt 300) { $impresasAqui.RemoveAt(0) }
            Escribir-Registro "Impreso #${id}: $($trabajo.descripcion)"
            Avisar "/ticketera/$id/impresa" | Out-Null
            return $true
        }

        $mensaje = (($salida | ForEach-Object { "$_" }) -join ' ').Trim()
        if (-not $mensaje) { $mensaje = "imprimir-raw.ps1 terminó con código $codigo" }
        $mensaje = "No se pudo imprimir en '$($config.impresora)': $mensaje"
        Escribir-Registro "ERROR #${id}: $($trabajo.descripcion). $mensaje"
        Avisar "/ticketera/$id/error" @{ mensaje = $mensaje } | Out-Null
        return $false
    } finally {
        Remove-Item $archivo -Force -ErrorAction SilentlyContinue
    }
}

# Un solo agente a la vez en esta sesión de Windows
$mutex = New-Object System.Threading.Mutex($false, 'Local\CasaBlancaAgenteTicketera')
if (-not $mutex.WaitOne(0)) {
    Escribir-Registro 'Ya hay un agente de la ticketera corriendo en esta PC. Este se cierra.'
    exit 0
}

Escribir-Registro "Agente iniciado. Carpeta: $carpeta"
Limpiar-Registros
$diaLimpieza = (Get-Date).Date
$ultimoProblema = ''
$ultimoAvisoProblema = [datetime]::MinValue
$config = $null

while ($true) {
    $espera = 3
    try {
        $config = Leer-Config
        $espera = [int]$config.intervalo

        # PowerShell 5.1 devuelve la lista JSON como un solo objeto: ForEach-Object la separa en trabajos
        $trabajos = @(Llamar 'GET' '/ticketera/pendientes?limite=5' | ForEach-Object { $_ })

        if ($ultimoProblema) {
            Escribir-Registro "Conectado de nuevo con $($config.url)."
            $ultimoProblema = ''
        }

        $huboError = $false
        foreach ($trabajo in $trabajos) {
            if ($null -eq $trabajo -or $null -eq $trabajo.id) { continue }
            if (-not (Imprimir-Trabajo $trabajo)) { $huboError = $true }
        }
        # Si la impresora falló, se espera un poco más antes de reintentar
        if ($huboError) { $espera = 30 }
    } catch {
        $problema = if ($_.Exception.Response -or $_.Exception -is [System.Net.WebException]) { Describir-Error $_ } else { "$($_.Exception.Message)" }
        # El mismo problema se anota al aparecer y luego cada 10 minutos, para no llenar el registro
        if ($problema -ne $ultimoProblema -or (Get-Date) -gt $ultimoAvisoProblema.AddMinutes(10)) {
            Escribir-Registro $problema
            $ultimoProblema = $problema
            $ultimoAvisoProblema = Get-Date
        }
        $espera = [Math]::Max($espera, 10)
    }

    if ((Get-Date).Date -ne $diaLimpieza) {
        Limpiar-Registros
        $diaLimpieza = (Get-Date).Date
    }

    Start-Sleep -Seconds $espera
}
