# Encerra o servidor PHP e o MySQL local iniciados pelo projeto Jandira.
# Uso: powershell -ExecutionPolicy Bypass -File .\parar-local.ps1

$ferramentas = Join-Path $env:USERPROFILE '.local\jandira'

$processosPhp = Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" -ErrorAction SilentlyContinue |
    Where-Object { $_.CommandLine -like '*localhost:8000*' }

foreach ($processo in $processosPhp) {
    Write-Host "Encerrando PHP (PID $($processo.ProcessId))..." -ForegroundColor Yellow
    Stop-Process -Id $processo.ProcessId -Force -ErrorAction SilentlyContinue
}

$processosMysql = Get-CimInstance Win32_Process -Filter "Name = 'mysqld.exe'" -ErrorAction SilentlyContinue |
    Where-Object { $_.CommandLine -like "*$ferramentas*" }

foreach ($processo in $processosMysql) {
    Write-Host "Encerrando MySQL local (PID $($processo.ProcessId))..." -ForegroundColor Yellow
    Stop-Process -Id $processo.ProcessId -Force -ErrorAction SilentlyContinue
}

Write-Host 'Ambiente local encerrado.' -ForegroundColor Green
