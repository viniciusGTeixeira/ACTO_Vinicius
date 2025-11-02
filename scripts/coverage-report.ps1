# Script para gerar relatório de coverage

param(
    [switch]$Open
)

$ErrorActionPreference = "Stop"

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  ACTO Maps - Coverage Report" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se xdebug está instalado
$xdebugInstalled = php -m | Select-String "xdebug"

if (-not $xdebugInstalled) {
    Write-Host "[AVISO] Xdebug não está instalado" -ForegroundColor Yellow
    Write-Host "Para gerar relatório de coverage, instale o Xdebug:" -ForegroundColor Yellow
    Write-Host "  https://xdebug.org/docs/install" -ForegroundColor White
    Write-Host ""
    Write-Host "Gerando relatório sem coverage detalhado..." -ForegroundColor Yellow
    Write-Host ""
}

# Executar testes com coverage
Write-Host "[INFO] Executando testes com coverage..." -ForegroundColor Yellow
Write-Host ""

php artisan test --coverage --coverage-html coverage-report --min=80

$exitCode = $LASTEXITCODE

if ($exitCode -eq 0) {
    Write-Host ""
    Write-Host "===============================================" -ForegroundColor Cyan
    Write-Host "  Relatório gerado com sucesso!" -ForegroundColor Green
    Write-Host "===============================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Relatório HTML: coverage-report/index.html" -ForegroundColor White
    Write-Host ""
    
    if ($Open) {
        Write-Host "[INFO] Abrindo relatório no navegador..." -ForegroundColor Yellow
        Start-Process "coverage-report/index.html"
    } else {
        Write-Host "Use -Open para abrir o relatório automaticamente" -ForegroundColor Gray
    }
} else {
    Write-Host ""
    Write-Host "===============================================" -ForegroundColor Cyan
    Write-Host "  Falha ao gerar relatório" -ForegroundColor Red
    Write-Host "===============================================" -ForegroundColor Cyan
    Write-Host ""
}

exit $exitCode

