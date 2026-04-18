# Instala MailHog em <Laragon>\bin\mailhog\MailHog.exe (GitHub releases).
# Executar PowerShell como utilizador normal (preferir "Executar como administrador" apenas se falhar permissões).
param(
    [string]$LaragonRoot = "C:\laragon",
    [switch]$Force
)

$ErrorActionPreference = "Stop"
$mailhogDir = Join-Path $LaragonRoot "bin\mailhog"
$exePath = Join-Path $mailhogDir "MailHog.exe"

if ((Test-Path $exePath) -and -not $Force) {
    Write-Host "MailHog ja existe em: $exePath"
    Write-Host "Para reinstalar: .\install_mailhog_laragon.ps1 -Force"
    exit 0
}

New-Item -ItemType Directory -Force -Path $mailhogDir | Out-Null

Write-Host "A obter ultima release no GitHub..."
$release = Invoke-RestMethod -Uri "https://api.github.com/repos/mailhog/MailHog/releases/latest" `
    -Headers @{ "User-Agent" = "authentication-page-mailhog-setup" }
$asset = $release.assets | Where-Object { $_.name -eq "MailHog_windows_amd64.exe" } | Select-Object -First 1
if (-not $asset) {
    throw "Asset MailHog_windows_amd64.exe nao encontrado na ultima release."
}

$tempFile = Join-Path $env:TEMP ("mailhog_dl_" + [Guid]::NewGuid().ToString("n") + ".exe")
Write-Host "A descarregar: $($asset.browser_download_url)"
Invoke-WebRequest -Uri $asset.browser_download_url -OutFile $tempFile -UseBasicParsing

if (Test-Path $exePath) {
    Remove-Item -Force $exePath
}

Move-Item -Force $tempFile $exePath
Write-Host ""
Write-Host "MailHog instalado em: $exePath"
Write-Host "  Interface web: http://localhost:8025"
Write-Host "  SMTP:          127.0.0.1:1025"
Write-Host ""
Write-Host "Proximos passos:"
Write-Host "  1. Arrancar MailHog (duplo clique em MailHog.exe ou use start_mailhog.bat nesta pasta)."
Write-Host "  2. Opcional Laragon: Menu -> Laragon -> Procfile (ver README.md secao MailHog)"
