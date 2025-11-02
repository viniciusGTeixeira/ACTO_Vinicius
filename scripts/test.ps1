# Script de testes para o projeto ACTO Maps

param(
    [string]$Type = "all",
    [string]$Filter = "",
    [switch]$Coverage,
    [switch]$Parallel,
    [switch]$Profile,
    [switch]$Watch
)

$ErrorActionPreference = "Stop"

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  ACTO Maps - Test Runner" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se está no diretório correto
if (-not (Test-Path "artisan")) {
    Write-Host "[ERRO] Execute este script no diretório raiz do projeto" -ForegroundColor Red
    exit 1
}

# Montar comando base
$command = "php artisan test"

# Tipo de teste
switch ($Type) {
    "unit" {
        $command += " --testsuite=Unit"
        Write-Host "[INFO] Executando testes unitários" -ForegroundColor Yellow
    }
    "feature" {
        $command += " --testsuite=Feature"
        Write-Host "[INFO] Executando testes de feature" -ForegroundColor Yellow
    }
    "integration" {
        $command += " --testsuite=Integration"
        Write-Host "[INFO] Executando testes de integração" -ForegroundColor Yellow
    }
    "all" {
        Write-Host "[INFO] Executando todos os testes" -ForegroundColor Yellow
    }
    default {
        Write-Host "[ERRO] Tipo inválido. Use: all, unit, feature, integration" -ForegroundColor Red
        exit 1
    }
}

# Filtro
if ($Filter) {
    $command += " --filter=$Filter"
    Write-Host "[INFO] Filtro aplicado: $Filter" -ForegroundColor Yellow
}

# Coverage
if ($Coverage) {
    $command += " --coverage --min=80"
    Write-Host "[INFO] Coverage ativado (mínimo 80%)" -ForegroundColor Yellow
}

# Parallel
if ($Parallel) {
    $command += " --parallel --processes=4"
    Write-Host "[INFO] Execução paralela ativada (4 processos)" -ForegroundColor Yellow
}

# Profile
if ($Profile) {
    $command += " --profile"
    Write-Host "[INFO] Profiling ativado" -ForegroundColor Yellow
}

# Watch
if ($Watch) {
    $command += " --watch"
    Write-Host "[INFO] Watch mode ativado" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Comando: $command" -ForegroundColor Gray
Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Executar
Invoke-Expression $command

$exitCode = $LASTEXITCODE

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan

if ($exitCode -eq 0) {
    Write-Host "  Testes concluídos com sucesso!" -ForegroundColor Green
} else {
    Write-Host "  Alguns testes falharam" -ForegroundColor Red
}

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

exit $exitCode

