# Arma el encabezado del voucher de cobro como imagen de 1 bit para la ticketera (comando ESC/POS "GS v 0"):
# el camarón del logo a la izquierda y al lado "Restaurante Campestre" / "Casa Blanca" con letras de adorno.
# Se ejecuta una sola vez, o cuando cambie el diseño:
#   powershell -ExecutionPolicy Bypass -File resources\ticketera\generar-logo.ps1
# Crea resources\ticketera\logo.bin (lo usa TicketeraService) y logo-vista.png (para revisar cómo saldrá).
# camaron.png es el camarón recortado de public\img\logo-claro.png, sin las letras.
param(
    [string]$Camaron = (Join-Path $PSScriptRoot 'camaron.png'),
    [string]$Salida = (Join-Path $PSScriptRoot 'logo.bin'),
    [string]$Linea1 = 'Restaurante Campestre',
    [string]$Linea2 = 'Casa Blanca',
    [string]$Fuente1 = 'Monotype Corsiva',
    [string]$Fuente2 = 'Brush Script MT',
    [float]$Tamano1 = 28,       # si el texto no entra al lado del camarón, se achica solo
    [float]$Tamano2 = 60,
    [int]$Ancho = 384,          # puntos; entra en papel de 58 mm (384) y de 80 mm (576)
    [int]$AltoCamaron = 140,
    [int]$Separacion = 10,      # puntos entre el camarón y el texto
    [int]$Umbral = 150,         # 0-255: más alto = más negro
    [switch]$SinTramado         # umbral fijo en vez del difuminado Floyd-Steinberg (el camarón sale como una mancha)
)
$Tramado = -not $SinTramado

Add-Type -AssemblyName System.Drawing

function Nueva-Fuente([string]$nombre, [float]$tamano) {
    $f = New-Object System.Drawing.Font($nombre, $tamano, [System.Drawing.FontStyle]::Regular, [System.Drawing.GraphicsUnit]::Pixel)
    if ($f.Name -ne $nombre) { Write-Warning "No está instalada la fuente '$nombre'; se usa '$($f.Name)'." }
    return $f
}

# (PowerShell no distingue mayúsculas: la imagen no puede llamarse $camaron como el parámetro)
$imagenCamaron = [System.Drawing.Image]::FromFile((Resolve-Path $Camaron))
$anchoCamaron = [int][Math]::Round($imagenCamaron.Width * $AltoCamaron / $imagenCamaron.Height)
$disponible = $Ancho - $anchoCamaron - $Separacion

# Medidas del texto; si no entra al lado del camarón, se achican las dos líneas por igual
$medidor = [System.Drawing.Graphics]::FromImage((New-Object System.Drawing.Bitmap 1, 1))
$formato = [System.Drawing.StringFormat]::GenericTypographic
$escala = 1.0
do {
    $f1 = Nueva-Fuente $Fuente1 ($Tamano1 * $escala)
    $f2 = Nueva-Fuente $Fuente2 ($Tamano2 * $escala)
    $t1 = $medidor.MeasureString($Linea1, $f1, 2000, $formato)
    $t2 = $medidor.MeasureString($Linea2, $f2, 2000, $formato)
    $anchoTexto = [Math]::Ceiling([Math]::Max($t1.Width, $t2.Width))
    if ($anchoTexto -gt $disponible) { $f1.Dispose(); $f2.Dispose(); $escala -= 0.04 }
} while ($anchoTexto -gt $disponible -and $escala -gt 0.4)
$medidor.Dispose()

$altoTexto = [Math]::Ceiling($t1.Height) + [Math]::Ceiling($t2.Height)
$alto = [int][Math]::Max($AltoCamaron, $altoTexto) + 2

# Fondo blanco: lo transparente del PNG no se imprime
$lienzo = New-Object System.Drawing.Bitmap $Ancho, $alto
$g = [System.Drawing.Graphics]::FromImage($lienzo)
$g.Clear([System.Drawing.Color]::White)
$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

# Camarón + texto, juntos y centrados en el ancho del papel; el texto centrado en el alto del camarón
$xInicio = [int](($Ancho - ($anchoCamaron + $Separacion + $anchoTexto)) / 2)
$g.DrawImage($imagenCamaron, $xInicio, [int](($alto - $AltoCamaron) / 2), $anchoCamaron, $AltoCamaron)

$xTexto = $xInicio + $anchoCamaron + $Separacion
$y = [Math]::Max(0, ($alto - $altoTexto) / 2)
$g.DrawString($Linea1, $f1, [System.Drawing.Brushes]::Black, [float]($xTexto + ($anchoTexto - $t1.Width) / 2), [float]$y, $formato)
$y += [Math]::Ceiling($t1.Height)
$g.DrawString($Linea2, $f2, [System.Drawing.Brushes]::Black, [float]($xTexto + ($anchoTexto - $t2.Width) / 2), [float]$y, $formato)
$g.Dispose()
$imagenCamaron.Dispose(); $f1.Dispose(); $f2.Dispose()

# Luminancia de cada punto
$gris = New-Object 'double[,]' $alto, $Ancho
for ($y = 0; $y -lt $alto; $y++) {
    for ($x = 0; $x -lt $Ancho; $x++) {
        $c = $lienzo.GetPixel($x, $y)
        $gris[$y, $x] = 0.299 * $c.R + 0.587 * $c.G + 0.114 * $c.B
    }
}

$bytesFila = [int][Math]::Ceiling($Ancho / 8)
$datos = New-Object byte[] ($bytesFila * $alto)
$vista = New-Object System.Drawing.Bitmap $Ancho, $alto

for ($y = 0; $y -lt $alto; $y++) {
    for ($x = 0; $x -lt $Ancho; $x++) {
        $valor = $gris[$y, $x]
        $negro = $valor -lt $Umbral
        if ($Tramado) {
            $nuevo = if ($negro) { 0 } else { 255 }
            $dif = $valor - $nuevo
            if ($x + 1 -lt $Ancho) { $gris[$y, ($x + 1)] += $dif * 7 / 16 }
            if ($y + 1 -lt $alto) {
                if ($x -gt 0) { $gris[($y + 1), ($x - 1)] += $dif * 3 / 16 }
                $gris[($y + 1), $x] += $dif * 5 / 16
                if ($x + 1 -lt $Ancho) { $gris[($y + 1), ($x + 1)] += $dif * 1 / 16 }
            }
        }
        if ($negro) {
            $i = $y * $bytesFila + [Math]::Floor($x / 8)
            $datos[$i] = $datos[$i] -bor (0x80 -shr ($x % 8))
        }
        $vista.SetPixel($x, $y, $(if ($negro) { [System.Drawing.Color]::Black } else { [System.Drawing.Color]::White }))
    }
}
$lienzo.Dispose()

# GS v 0 m xL xH yL yH + puntos
$cabecera = [byte[]](0x1D, 0x76, 0x30, 0x00, ($bytesFila -band 0xFF), ($bytesFila -shr 8), ($alto -band 0xFF), ($alto -shr 8))
[System.IO.File]::WriteAllBytes($Salida, [byte[]]($cabecera + $datos))

$vista.Save([System.IO.Path]::ChangeExtension($Salida, $null).TrimEnd('.') + '-vista.png', [System.Drawing.Imaging.ImageFormat]::Png)
$vista.Dispose()

Write-Output "Encabezado listo: $Salida ($Ancho x $alto puntos)"
