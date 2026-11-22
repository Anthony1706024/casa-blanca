# Registra la tarea programada "Casa Blanca - Ticketera": arranca el agente oculto cada vez que
# este usuario inicia sesión en Windows (así tiene acceso a la impresora) y lo reinicia si se cae.
# Uso (en esta carpeta): powershell -ExecutionPolicy Bypass -File instalar-tarea.ps1
$ErrorActionPreference = 'Stop'

$nombre = 'Casa Blanca - Ticketera'
$script = Join-Path $PSScriptRoot 'agente-ticketera.ps1'
$usuario = "$env:USERDOMAIN\$env:USERNAME"

if (-not (Test-Path (Join-Path $PSScriptRoot 'config.json'))) {
    Write-Warning 'Todavía no hay config.json en esta carpeta. El agente no imprimirá hasta que lo crees (ver LEEME.txt).'
}

$accion = New-ScheduledTaskAction -Execute 'powershell.exe' `
    -Argument "-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$script`"" `
    -WorkingDirectory $PSScriptRoot
$disparador = New-ScheduledTaskTrigger -AtLogOn -User $usuario
$ajustes = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable `
    -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) `
    -ExecutionTimeLimit ([TimeSpan]::Zero) -MultipleInstances IgnoreNew
$principal = New-ScheduledTaskPrincipal -UserId $usuario -LogonType Interactive -RunLevel Limited

Register-ScheduledTask -TaskName $nombre -Action $accion -Trigger $disparador -Settings $ajustes -Principal $principal `
    -Description 'Imprime en la ticketera los vouchers y tickets de cocina del sistema Casa Blanca.' -Force | Out-Null

Start-ScheduledTask -TaskName $nombre
Write-Output "Listo: la tarea '$nombre' quedó instalada para $usuario y ya está corriendo."
Write-Output "Registro del agente: $(Join-Path $PSScriptRoot 'logs')"
