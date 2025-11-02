#!/bin/sh

# ACTO Maps - MinIO Bucket Initialization Script
# 
# @license MIT
# @author Kemersson Vinicius Gonçalves Teixeira
# @date 10/2025

# Wait for MinIO to be ready
echo "Waiting for MinIO to be ready..."
until curl -s http://minio:9000/minio/health/live > /dev/null 2>&1; do
    echo "MinIO is not ready yet. Retrying in 5 seconds..."
    sleep 5
done

echo "MinIO is ready. Creating bucket..."

# Install mc (MinIO Client) if not present
if ! command -v mc &> /dev/null; then
    wget https://dl.min.io/client/mc/release/linux-amd64/mc
    chmod +x mc
    mv mc /usr/local/bin/
fi

# Configure MinIO client
mc alias set minio http://minio:9000 minioadmin minioadmin

# Create bucket if it doesn't exist
mc mb minio/acto-maps --ignore-existing

# Set bucket policy to private
mc anonymous set none minio/acto-maps

echo "MinIO bucket 'acto-maps' created successfully!"

