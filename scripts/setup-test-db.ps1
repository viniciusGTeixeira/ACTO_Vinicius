# Script para configurar banco de dados de testes

$ErrorActionPreference = "Stop"

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  ACTO Maps - Setup Test Database" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se PostgreSQL está rodando
Write-Host "[1/5] Verificando PostgreSQL..." -ForegroundColor Yellow
$pgRunning = podman ps | Select-String "acto-postgres"

if (-not $pgRunning) {
    Write-Host "[ERRO] PostgreSQL não está rodando" -ForegroundColor Red
    Write-Host "Execute: podman-compose up -d postgres" -ForegroundColor Yellow
    exit 1
}

Write-Host "[OK] PostgreSQL está rodando" -ForegroundColor Green

# Criar banco de teste
Write-Host ""
Write-Host "[2/5] Criando banco de teste laravel_test..." -ForegroundColor Yellow

$createDbCmd = @"
podman exec acto-postgres psql -U laravel_user -c \"SELECT 1 FROM pg_database WHERE datname = 'laravel_test'\" | Select-String "1" | Out-Null
if (`$LASTEXITCODE -ne 0) {
    podman exec acto-postgres psql -U laravel_user -c \"CREATE DATABASE laravel_test\"
    Write-Host \"[OK] Banco laravel_test criado\" -ForegroundColor Green
} else {
    Write-Host \"[OK] Banco laravel_test já existe\" -ForegroundColor Green
}
"@

Invoke-Expression $createDbCmd

# Instalar extensões PostGIS
Write-Host ""
Write-Host "[3/5] Instalando extensões PostGIS..." -ForegroundColor Yellow

podman exec acto-postgres psql -U laravel_user -d laravel_test -c "CREATE EXTENSION IF NOT EXISTS postgis;" 2>&1 | Out-Null
podman exec acto-postgres psql -U laravel_user -d laravel_test -c "CREATE EXTENSION IF NOT EXISTS pg_trgm;" 2>&1 | Out-Null
podman exec acto-postgres psql -U laravel_user -d laravel_test -c 'CREATE EXTENSION IF NOT EXISTS "uuid-ossp";' 2>&1 | Out-Null

Write-Host "[OK] Extensões instaladas" -ForegroundColor Green

# Criar .env.testing
Write-Host ""
Write-Host "[4/5] Criando .env.testing..." -ForegroundColor Yellow

$envTesting = @"
APP_ENV=testing
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_test
DB_USERNAME=laravel_user
DB_PASSWORD=secret

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array

AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=acto-maps-test
AWS_ENDPOINT=http://127.0.0.1:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

TWOFACTOR_WHATSAPP_ENABLED=false
GEOIP_ENABLED=false
ANOMALY_DETECTION_ENABLED=false
RSA_ENCRYPTION_ENABLED=false
"@

$envTesting | Out-File -FilePath ".env.testing" -Encoding UTF8

Write-Host "[OK] .env.testing criado" -ForegroundColor Green

# Gerar chave
Write-Host ""
Write-Host "[5/5] Gerando APP_KEY..." -ForegroundColor Yellow

php artisan key:generate --env=testing

Write-Host "[OK] APP_KEY gerado" -ForegroundColor Green

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  Setup concluído!" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Cyan
Write-Host "1. Executar migrations: php artisan migrate --env=testing" -ForegroundColor White
Write-Host "2. Executar testes: php artisan test" -ForegroundColor White
Write-Host "3. Ou use: .\scripts\test.ps1" -ForegroundColor White
Write-Host ""

