# Convierte el logo en la imagen de 1 bit que entiende la ticketera (comando ESC/POS "GS v 0").
# Se ejecuta una sola vez, o cuando cambie el logo:
#   powershell -ExecutionPolicy Bypass -File resources\ticketera\generar-logo.ps1
# Crea resources\ticketera\logo.bin (lo usa TicketeraService) y logo-vista.png (para revisar cómo saldrá).
param(
    [string]$Imagen = (Join-Path $PSScriptRoot '..\..\public\img\logo-claro.png'),
    [string]$Salida = (Join-Path $PSScriptRoot 'logo.bin'),
    [int]$Ancho = 384,          # puntos; entra en papel de 58 mm (384) y de 80 mm (576)
    [int]$Umbral = 200,         # 0-255: más alto = más negro
    [switch]$Tramado            # difuminado Floyd-Steinberg en vez de umbral fijo
)

Add-Type -AssemblyName System.Drawing

$original = [System.Drawing.Image]::FromFile((Resolve-Path $Imagen))
$alto = [int][Math]::Round($original.Height * $Ancho / $original.Width)

# Fondo blanco: lo transparente del PNG no se imprime
$lienzo = New-Object System.Drawing.Bitmap $Ancho, $alto
$g = [System.Drawing.Graphics]::FromImage($lienzo)
$g.Clear([System.Drawing.Color]::White)
$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$g.DrawImage($original, 0, 0, $Ancho, $alto)
$g.Dispose()
$original.Dispose()

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

Write-Output "Logo listo: $Salida ($Ancho x $alto puntos)"
