@echo off
REM Arranca MailHog se existir na pasta padra do Laragon.
set "MH=C:\laragon\bin\mailhog\MailHog.exe"
if exist "%MH%" (
    start "" "%MH%"
    echo MailHog iniciado. Abra http://localhost:8025
    exit /b 0
)

echo MailHog nao encontrado em %MH%
echo Execute primeiro: powershell -ExecutionPolicy Bypass -File "%~dp0install_mailhog_laragon.ps1"
pause
exit /b 1
