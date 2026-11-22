# Envía un archivo tal cual (comandos ESC/POS) a una impresora de Windows, sin pasar por el driver.
# Lo usa App\Services\TicketeraService. Uso: imprimir-raw.ps1 -Impresora "Nombre" -Archivo "ruta.bin"
param(
    [Parameter(Mandatory = $true)][string]$Impresora,
    [Parameter(Mandatory = $true)][string]$Archivo
)

$ErrorActionPreference = 'Stop'

Add-Type -TypeDefinition @'
using System;
using System.Runtime.InteropServices;
public static class CasaBlancaRaw {
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    public class DOCINFO { public string pDocName; public string pOutputFile; public string pDataType; }
    [DllImport("winspool.drv", CharSet = CharSet.Unicode, SetLastError = true)] static extern bool OpenPrinter(string n, out IntPtr h, IntPtr d);
    [DllImport("winspool.drv", SetLastError = true)] static extern bool ClosePrinter(IntPtr h);
    [DllImport("winspool.drv", CharSet = CharSet.Unicode, SetLastError = true)] static extern bool StartDocPrinter(IntPtr h, int l, DOCINFO di);
    [DllImport("winspool.drv", SetLastError = true)] static extern bool EndDocPrinter(IntPtr h);
    [DllImport("winspool.drv", SetLastError = true)] static extern bool StartPagePrinter(IntPtr h);
    [DllImport("winspool.drv", SetLastError = true)] static extern bool EndPagePrinter(IntPtr h);
    [DllImport("winspool.drv", SetLastError = true)] static extern bool WritePrinter(IntPtr h, byte[] b, int c, out int w);
    public static int Enviar(string impresora, byte[] datos) {
        IntPtr h;
        if (!OpenPrinter(impresora, out h, IntPtr.Zero)) return -1;
        try {
            if (!StartDocPrinter(h, 1, new DOCINFO { pDocName = "Voucher Casa Blanca", pDataType = "RAW" })) return -2;
            StartPagePrinter(h);
            int w; WritePrinter(h, datos, datos.Length, out w);
            EndPagePrinter(h); EndDocPrinter(h);
            return w;
        } finally { ClosePrinter(h); }
    }
}
'@

$datos = [System.IO.File]::ReadAllBytes($Archivo)
$enviados = [CasaBlancaRaw]::Enviar($Impresora, $datos)

if ($enviados -ne $datos.Length) {
    Write-Error "No se pudo imprimir en '$Impresora' (resultado $enviados)."
    exit 1
}
Write-Output "OK $enviados"
