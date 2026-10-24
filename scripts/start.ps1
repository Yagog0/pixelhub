# Servidor de desenvolvimento: dados persistem fora da pasta pública.
$projectRoot = Split-Path -Parent $PSScriptRoot
$phpExecutable = 'D:\php\php.exe'
if (-not (Test-Path -LiteralPath $phpExecutable)) { $phpExecutable = (Get-Command php -ErrorAction Stop).Source }
Write-Host 'PixelHub: http://127.0.0.1:8081/ — Ctrl+C para encerrar.'
& $phpExecutable -d upload_max_filesize=20M -d post_max_size=22M -S 127.0.0.1:8081 -t (Join-Path $projectRoot 'public')
