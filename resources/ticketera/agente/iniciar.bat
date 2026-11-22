@echo off
title Agente de la ticketera - Casa Blanca
cd /d "%~dp0"
echo Agente de la ticketera Casa Blanca. Cierra esta ventana para detenerlo.
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0agente-ticketera.ps1"
echo.
echo El agente se detuvo.
pause
