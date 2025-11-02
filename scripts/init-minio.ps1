# ACTO Maps - MinIO Initialization Script (PowerShell)
# 
# @license MIT
# @author Kemersson Vinicius Gonçalves Teixeira
# @date 10/2025

$ErrorActionPreference = "Stop"

Write-Host "Initializing MinIO for ACTO Maps..." -ForegroundColor Green

# Check if MinIO is running
try {
    $response = Invoke-WebRequest -Uri "http://localhost:9000/minio/health/live" -UseBasicParsing -ErrorAction Stop
    Write-Host "MinIO is running!" -ForegroundColor Green
} catch {
    Write-Host "Error: MinIO is not running. Please start it with 'podman-compose up -d minio'" -ForegroundColor Red
    exit 1
}

# Check if mc.exe exists
$mcPath = Get-Command mc.exe -ErrorAction SilentlyContinue
if (-not $mcPath) {
    Write-Host "MinIO Client (mc.exe) not found. Downloading..." -ForegroundColor Yellow
    
    $downloadUrl = "https://dl.min.io/client/mc/release/windows-amd64/mc.exe"
    $mcExePath = "$env:TEMP\mc.exe"
    
    Invoke-WebRequest -Uri $downloadUrl -OutFile $mcExePath
    
    Write-Host "Downloaded mc.exe to $mcExePath" -ForegroundColor Green
    Write-Host "Please move it to a directory in your PATH or use the full path" -ForegroundColor Yellow
    
    $mcCommand = $mcExePath
} else {
    $mcCommand = "mc.exe"
}

# Configure MinIO client
Write-Host "Configuring MinIO client..." -ForegroundColor Cyan
& $mcCommand alias set local http://localhost:9000 minioadmin minioadmin

# Create bucket
Write-Host "Creating bucket 'acto-maps'..." -ForegroundColor Cyan
& $mcCommand mb local/acto-maps --ignore-existing

# Set bucket policy
Write-Host "Setting bucket policy to private..." -ForegroundColor Cyan
& $mcCommand anonymous set none local/acto-maps

# Create directory structure
Write-Host "Creating directory structure..." -ForegroundColor Cyan
& $mcCommand mb local/acto-maps/geojson-uploads --ignore-existing
& $mcCommand mb local/acto-maps/livewire-tmp --ignore-existing
& $mcCommand mb local/acto-maps/backups --ignore-existing
& $mcCommand mb local/acto-maps/exports --ignore-existing

Write-Host ""
Write-Host "MinIO initialized successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "MinIO Console: http://localhost:9001" -ForegroundColor Cyan
Write-Host "Username: minioadmin" -ForegroundColor Cyan
Write-Host "Password: minioadmin" -ForegroundColor Cyan
Write-Host ""
Write-Host "Bucket: acto-maps" -ForegroundColor Cyan
Write-Host "Endpoint: http://localhost:9000" -ForegroundColor Cyan

