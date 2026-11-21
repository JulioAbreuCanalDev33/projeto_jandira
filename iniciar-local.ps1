# Inicia o ambiente local do Projeto Jandira (MySQL + PHP).
# Uso: clique com o botao direito > "Executar com PowerShell"
# ou no terminal: powershell -ExecutionPolicy Bypass -File .\iniciar-local.ps1

$ErrorActionPreference = 'Stop'

$raiz = $PSScriptRoot
$ferramentas = Join-Path $env:USERPROFILE '.local\jandira'
$php = Join-Path $ferramentas 'php\php.exe'
$mysqld = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe'
$mysqlIni = Join-Path $ferramentas 'mysql\my.ini'
$portaPhp = 8000
$portaMysql = 3306

function Testar-Porta {
    param([int]$Porta)
    try {
        $cliente = [System.Net.Sockets.TcpClient]::new()
        $resultado = $cliente.BeginConnect('127.0.0.1', $Porta, $null, $null)
        $ativo = $resultado.AsyncWaitHandle.WaitOne(500)
        $cliente.Close()
        return $ativo
    } catch {
        return $false
    }
}

if (-not (Test-Path $php)) {
    throw "PHP nao encontrado em $php. Reinstale o ambiente local."
}

if (-not (Test-Path $mysqld)) {
    throw "MySQL nao encontrado em $mysqld."
}

if (-not (Testar-Porta $portaMysql)) {
    Write-Host 'Iniciando MySQL local...' -ForegroundColor Cyan
    Start-Process -FilePath $mysqld -ArgumentList ('--defaults-file="' + $mysqlIni + '"') -WindowStyle Hidden
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep -Seconds 1
        if (Testar-Porta $portaMysql) { break }
    }
}

if (-not (Testar-Porta $portaMysql)) {
    throw "Nao foi possivel iniciar o MySQL na porta $portaMysql. Veja o log em $ferramentas\mysql\error.log"
}

Write-Host "MySQL ativo na porta $portaMysql." -ForegroundColor Green

if (Testar-Porta $portaPhp) {
    Write-Host "A porta $portaPhp ja esta em uso. Abrindo o navegador..." -ForegroundColor Yellow
} else {
    Write-Host "Iniciando servidor PHP em http://localhost:$portaPhp ..." -ForegroundColor Cyan
    Start-Process -FilePath $php -ArgumentList @('-S', "127.0.0.1:$portaPhp", '-t', "`"$raiz`"") -WorkingDirectory $raiz -WindowStyle Minimized
    for ($i = 0; $i -lt 15; $i++) {
        Start-Sleep -Milliseconds 500
        if (Testar-Porta $portaPhp) { break }
    }
}

if (-not (Testar-Porta $portaPhp)) {
    throw "Nao foi possivel iniciar o servidor PHP na porta $portaPhp."
}

Write-Host "Ambiente pronto: http://localhost:$portaPhp" -ForegroundColor Green
Start-Process "http://localhost:$portaPhp"
