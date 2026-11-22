# Quita la tarea programada "Casa Blanca - Ticketera" y detiene el agente.
# Uso (en esta carpeta): powershell -ExecutionPolicy Bypass -File desinstalar-tarea.ps1
$nombre = 'Casa Blanca - Ticketera'

if (Get-ScheduledTask -TaskName $nombre -ErrorAction SilentlyContinue) {
    Stop-ScheduledTask -TaskName $nombre -ErrorAction SilentlyContinue
    Unregister-ScheduledTask -TaskName $nombre -Confirm:$false
    Write-Output "Se quitó la tarea '$nombre'."
} else {
    Write-Output "La tarea '$nombre' no estaba instalada."
}

# Por si quedó un agente abierto (por ejemplo, desde iniciar.bat)
Get-CimInstance Win32_Process -Filter "Name = 'powershell.exe'" |
    Where-Object { $_.CommandLine -like '*agente-ticketera.ps1*' } |
    ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue; Write-Output "Se cerró el agente (proceso $($_.ProcessId))." }
