# Script para gerar certificados CA e chaves RSA para o projeto ACTO Maps
# Requer OpenSSL instalado no Windows

param(
    [string]$OutputDir = "certificates",
    [switch]$Force
)

$ErrorActionPreference = "Stop"

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  ACTO Maps - Gerador de Certificados" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se OpenSSL está instalado
try {
    $opensslVersion = & openssl version
    Write-Host "[OK] OpenSSL encontrado: $opensslVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERRO] OpenSSL não encontrado!" -ForegroundColor Red
    Write-Host "Instale o OpenSSL: https://slproweb.com/products/Win32OpenSSL.html" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Criar diretório de saída
if (-not (Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
    Write-Host "[OK] Diretório criado: $OutputDir" -ForegroundColor Green
} else {
    if (-not $Force) {
        Write-Host "[AVISO] Diretório $OutputDir já existe. Use -Force para sobrescrever." -ForegroundColor Yellow
        $response = Read-Host "Deseja continuar? (s/n)"
        if ($response -ne 's') {
            Write-Host "Operação cancelada." -ForegroundColor Yellow
            exit 0
        }
    }
}

Write-Host ""
Write-Host "Gerando certificados..." -ForegroundColor Cyan
Write-Host ""

# 1. Gerar Certificado CA
Write-Host "[1/4] Gerando chave privada CA (4096 bits)..." -ForegroundColor Yellow
& openssl genrsa -out "$OutputDir/ca-key.pem" 4096 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Chave privada CA gerada: $OutputDir/ca-key.pem" -ForegroundColor Green
} else {
    Write-Host "[ERRO] Falha ao gerar chave privada CA" -ForegroundColor Red
    exit 1
}

Write-Host "[2/4] Gerando certificado CA..." -ForegroundColor Yellow
& openssl req -new -x509 -days 365 -key "$OutputDir/ca-key.pem" -out "$OutputDir/ca-cert.pem" `
    -subj "/C=BR/ST=DF/L=Brasilia/O=ACTO/OU=IT/CN=ACTO Root CA" 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Certificado CA gerado: $OutputDir/ca-cert.pem" -ForegroundColor Green
} else {
    Write-Host "[ERRO] Falha ao gerar certificado CA" -ForegroundColor Red
    exit 1
}

# 2. Gerar par de chaves RSA 4096
Write-Host "[3/4] Gerando chave privada RSA (4096 bits)..." -ForegroundColor Yellow
& openssl genrsa -out "$OutputDir/rsa-private.pem" 4096 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Chave privada RSA gerada: $OutputDir/rsa-private.pem" -ForegroundColor Green
} else {
    Write-Host "[ERRO] Falha ao gerar chave privada RSA" -ForegroundColor Red
    exit 1
}

Write-Host "[4/4] Extraindo chave pública RSA..." -ForegroundColor Yellow
& openssl rsa -in "$OutputDir/rsa-private.pem" -pubout -out "$OutputDir/rsa-public.pem" 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Chave pública RSA gerada: $OutputDir/rsa-public.pem" -ForegroundColor Green
} else {
    Write-Host "[ERRO] Falha ao extrair chave pública RSA" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  Certificados gerados com sucesso!" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Exibir informações dos certificados
Write-Host "Informações do Certificado CA:" -ForegroundColor Cyan
& openssl x509 -in "$OutputDir/ca-cert.pem" -noout -subject -dates

Write-Host ""
Write-Host "Tamanhos das chaves:" -ForegroundColor Cyan
Get-ChildItem "$OutputDir/*.pem" | ForEach-Object {
    $size = [math]::Round($_.Length / 1KB, 2)
    Write-Host "  $($_.Name): ${size} KB" -ForegroundColor White
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  Gerando formato para .env" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Função para converter certificado em formato .env
function ConvertTo-EnvFormat {
    param([string]$FilePath)
    $content = Get-Content $FilePath -Raw
    # Remover espaços em branco do final
    $content = $content.TrimEnd()
    # Substituir quebras de linha por \n
    $content = $content -replace "`r`n", "\n"
    $content = $content -replace "`n", "\n"
    return $content
}

# Criar arquivo .env snippet
$envSnippet = @"
# Certificate Authority
CA_CERTIFICATE="$(ConvertTo-EnvFormat "$OutputDir/ca-cert.pem")"

CA_PRIVATE_KEY="$(ConvertTo-EnvFormat "$OutputDir/ca-key.pem")"

# RSA Encryption
RSA_PRIVATE_KEY="$(ConvertTo-EnvFormat "$OutputDir/rsa-private.pem")"

RSA_PUBLIC_KEY="$(ConvertTo-EnvFormat "$OutputDir/rsa-public.pem")"
"@

$envSnippet | Out-File "$OutputDir/env-snippet.txt" -Encoding UTF8
Write-Host "[OK] Snippet para .env salvo: $OutputDir/env-snippet.txt" -ForegroundColor Green

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  Próximos passos" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Copie o conteúdo de $OutputDir/env-snippet.txt" -ForegroundColor White
Write-Host "2. Cole no seu arquivo .env" -ForegroundColor White
Write-Host "3. Ou use o comando abaixo:" -ForegroundColor White
Write-Host ""
Write-Host "   Get-Content $OutputDir/env-snippet.txt | Add-Content .env" -ForegroundColor Yellow
Write-Host ""
Write-Host "4. Ative a criptografia:" -ForegroundColor White
Write-Host ""
Write-Host "   RSA_ENCRYPTION_ENABLED=true" -ForegroundColor Yellow
Write-Host "   TLS_ENABLED=true" -ForegroundColor Yellow
Write-Host ""
Write-Host "IMPORTANTE: Mantenha as chaves privadas seguras!" -ForegroundColor Red
Write-Host "- ca-key.pem: Chave privada do CA" -ForegroundColor Red
Write-Host "- rsa-private.pem: Chave privada RSA" -ForegroundColor Red
Write-Host ""
Write-Host "Estas chaves NÃO devem ser commitadas no Git!" -ForegroundColor Red
Write-Host ""

