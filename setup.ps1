# ACTO Maps - Setup Automatizado
# 
# @license MIT
# @author Kemersson Vinicius Gonçalves Teixeira
# @date 10/2025
#
# Script de instalação completa do projeto ACTO Maps
# Execute: .\setup.ps1

$ErrorActionPreference = "Stop"

# Cores para output
function Write-Step {
    param([string]$Message)
    Write-Host "`n[STEP] $Message" -ForegroundColor Cyan
}

function Write-Success {
    param([string]$Message)
    Write-Host "[OK] $Message" -ForegroundColor Green
}

function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Yellow
}

function Write-Failed {
    param([string]$Message)
    Write-Host "[ERRO] $Message" -ForegroundColor Red
}

# Banner
Clear-Host
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "     ACTO Maps - Setup Automatizado" -ForegroundColor White
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# ETAPA 1: Verificar Requisitos
# ============================================================================
Write-Step "Verificando requisitos do sistema..."

# PHP
try {
    $phpVersion = php -v 2>&1 | Select-String -Pattern "PHP (\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value }
    if ([double]$phpVersion -lt 8.2) {
        Write-Failed "PHP 8.2+ necessário. Versão encontrada: $phpVersion"
        exit 1
    }
    Write-Success "PHP $phpVersion encontrado"
} catch {
    Write-Failed "PHP não encontrado. Instale o PHP 8.2+"
    exit 1
}

# Composer
try {
    $composerVersion = composer --version 2>&1 | Select-String -Pattern "Composer version (\S+)" | ForEach-Object { $_.Matches.Groups[1].Value }
    Write-Success "Composer $composerVersion encontrado"
} catch {
    Write-Failed "Composer não encontrado. Instale em: https://getcomposer.org"
    exit 1
}

# Node.js
try {
    $nodeVersion = node --version 2>&1
    Write-Success "Node.js $nodeVersion encontrado"
} catch {
    Write-Failed "Node.js não encontrado. Instale em: https://nodejs.org"
    exit 1
}

# Podman
try {
    $podmanVersion = podman --version 2>&1
    Write-Success "Podman encontrado: $podmanVersion"
} catch {
    Write-Failed "Podman não encontrado. Instale em: https://podman.io"
    exit 1
}

# Podman Compose
try {
    $composeVersion = podman-compose --version 2>&1
    Write-Success "Podman Compose encontrado"
} catch {
    Write-Failed "Podman Compose não encontrado. Instale: pip install podman-compose"
    exit 1
}

# ============================================================================
# ETAPA 2: Configurar Ambiente
# ============================================================================
Write-Step "Configurando arquivo .env..."

if (-not (Test-Path ".env")) {
    if (Test-Path "env.example") {
        Copy-Item "env.example" ".env"
        Write-Success "Arquivo .env criado"
    } else {
        Write-Failed "Arquivo env.example não encontrado"
        exit 1
    }
} else {
    Write-Info "Arquivo .env já existe. Pulando..."
}

# ============================================================================
# ETAPA 3: Instalar Dependências PHP
# ============================================================================
Write-Step "Instalando dependências PHP com Composer..."

try {
    composer install --no-interaction --prefer-dist --optimize-autoloader
    Write-Success "Dependências PHP instaladas"
} catch {
    Write-Failed "Erro ao instalar dependências PHP"
    exit 1
}

# ============================================================================
# ETAPA 4: Instalar Dependências Node.js
# ============================================================================
Write-Step "Instalando dependências Node.js..."

try {
    npm install
    Write-Success "Dependências Node.js instaladas"
} catch {
    Write-Failed "Erro ao instalar dependências Node.js"
    exit 1
}

# ============================================================================
# ETAPA 5: Gerar Application Key
# ============================================================================
Write-Step "Gerando chave da aplicação..."

try {
    php artisan key:generate --force
    Write-Success "Chave da aplicação gerada"
} catch {
    Write-Failed "Erro ao gerar chave da aplicação"
    exit 1
}

# ============================================================================
# ETAPA 6: Iniciar Containers (PostgreSQL + PostGIS, MinIO)
# ============================================================================
Write-Step "Iniciando containers (PostgreSQL + MinIO)..."

try {
    podman-compose up -d postgres minio
    Write-Success "Containers iniciados"
    Write-Info "Aguardando containers ficarem prontos..."
    Start-Sleep -Seconds 15
} catch {
    Write-Failed "Erro ao iniciar containers"
    exit 1
}

# ============================================================================
# ETAPA 7: Verificar PostgreSQL
# ============================================================================
Write-Step "Verificando conexão com PostgreSQL..."

$maxRetries = 10
$retryCount = 0
$connected = $false

while (-not $connected -and $retryCount -lt $maxRetries) {
    try {
        $env:PGPASSWORD = "secret"
        $result = psql -h localhost -U laravel_user -d laravel -c "SELECT 1;" 2>&1
        if ($LASTEXITCODE -eq 0) {
            $connected = $true
            Write-Success "PostgreSQL está pronto"
        } else {
            throw
        }
    } catch {
        $retryCount++
        Write-Info "Aguardando PostgreSQL ($retryCount/$maxRetries)..."
        Start-Sleep -Seconds 3
    }
}

if (-not $connected) {
    Write-Info "Não foi possível verificar PostgreSQL automaticamente. Continuando..."
}

# ============================================================================
# ETAPA 8: Executar Migrations
# ============================================================================
Write-Step "Executando migrations do banco de dados..."

try {
    php artisan migrate --force
    Write-Success "Migrations executadas"
} catch {
    Write-Failed "Erro ao executar migrations"
    Write-Info "Tente manualmente: php artisan migrate"
}

# ============================================================================
# ETAPA 9: Criar Usuário Administrador
# ============================================================================
Write-Step "Criando usuário administrador..."

try {
    php artisan db:seed --class=AdminUserSeeder --force
    Write-Success "Usuário admin criado"
    Write-Info "Email: admin@acto.com | Senha: password"
} catch {
    Write-Info "Seeder já executado ou erro. Continuando..."
}

# ============================================================================
# ETAPA 10: Configurar MinIO
# ============================================================================
Write-Step "Configurando MinIO..."

# Verificar se MinIO está pronto
$minioReady = $false
$maxRetries = 10
$retryCount = 0

while (-not $minioReady -and $retryCount -lt $maxRetries) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:9000/minio/health/live" -UseBasicParsing -ErrorAction Stop
        $minioReady = $true
        Write-Success "MinIO está pronto"
    } catch {
        $retryCount++
        Write-Info "Aguardando MinIO ($retryCount/$maxRetries)..."
        Start-Sleep -Seconds 3
    }
}

if (-not $minioReady) {
    Write-Info "MinIO não está respondendo. Você pode configurá-lo manualmente depois."
} else {
    # Baixar mc.exe se não existir
    $mcPath = Get-Command mc.exe -ErrorAction SilentlyContinue
    
    if (-not $mcPath) {
        Write-Info "Baixando MinIO Client..."
        $downloadUrl = "https://dl.min.io/client/mc/release/windows-amd64/mc.exe"
        $mcExePath = "$PSScriptRoot\mc.exe"
        
        try {
            Invoke-WebRequest -Uri $downloadUrl -OutFile $mcExePath
            $mcCommand = $mcExePath
            Write-Success "MinIO Client baixado"
        } catch {
            Write-Info "Erro ao baixar mc.exe. Configure MinIO manualmente."
            $mcCommand = $null
        }
    } else {
        $mcCommand = "mc.exe"
    }
    
    if ($mcCommand) {
        try {
            & $mcCommand alias set local http://localhost:9000 minioadmin minioadmin 2>&1 | Out-Null
            & $mcCommand mb local/acto-maps --ignore-existing 2>&1 | Out-Null
            & $mcCommand anonymous set none local/acto-maps 2>&1 | Out-Null
            Write-Success "Bucket 'acto-maps' criado no MinIO"
        } catch {
            Write-Info "Erro ao configurar bucket. Configure manualmente."
        }
    }
}

# ============================================================================
# ETAPA 11: Criar Storage Link
# ============================================================================
Write-Step "Criando link de storage..."

try {
    php artisan storage:link
    Write-Success "Link de storage criado"
} catch {
    Write-Info "Link já existe ou erro. Continuando..."
}

# ============================================================================
# ETAPA 12: Build dos Assets
# ============================================================================
Write-Step "Compilando assets do frontend..."

try {
    npm run build
    Write-Success "Assets compilados"
} catch {
    Write-Info "Erro ao compilar assets. Execute manualmente: npm run dev"
}

# ============================================================================
# FINALIZAÇÃO
# ============================================================================
Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "     INSTALACAO CONCLUIDA COM SUCESSO!" -ForegroundColor White
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Para iniciar o servidor de desenvolvimento:" -ForegroundColor Cyan
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "URLs importantes:" -ForegroundColor Cyan
Write-Host "  Aplicação:      http://localhost:8000" -ForegroundColor White
Write-Host "  Painel Admin:   http://localhost:8000/painel" -ForegroundColor White
Write-Host "  MinIO Console:  http://localhost:9001" -ForegroundColor White
Write-Host ""
Write-Host "Credenciais padrão:" -ForegroundColor Cyan
Write-Host "  Admin:  admin@acto.com / password" -ForegroundColor White
Write-Host "  MinIO:  minioadmin / minioadmin" -ForegroundColor White
Write-Host ""
Write-Host "Comandos úteis:" -ForegroundColor Cyan
Write-Host "  Iniciar dev:    php artisan serve" -ForegroundColor White
Write-Host "  Assets watch:   npm run dev" -ForegroundColor White
Write-Host "  Ver logs:       podman-compose logs -f" -ForegroundColor White
Write-Host "  Parar tudo:     podman-compose down" -ForegroundColor White
Write-Host ""
Write-Host "Leia a documentação completa em docs/ para mais informações!" -ForegroundColor Yellow
Write-Host ""

