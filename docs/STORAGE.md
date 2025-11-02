# Configuração de Storage - ACTO Maps

## MinIO (S3-Compatible Storage)

O ACTO Maps usa MinIO como storage S3-compatible para armazenar arquivos GeoJSON e outros dados.

## Configuração Inicial

### 1. Iniciar o MinIO

```bash
podman-compose up -d minio
```

### 2. Inicializar o Bucket

**Linux/macOS:**
```bash
chmod +x scripts/init-minio.sh
./scripts/init-minio.sh
```

**Windows PowerShell:**
```powershell
.\scripts\init-minio.ps1
```

### 3. Configurar o .env

Certifique-se de que estas variáveis estão no seu `.env`:

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=acto-maps
AWS_ENDPOINT=http://127.0.0.1:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_URL=http://127.0.0.1:9000/acto-maps
```

## Acesso ao MinIO

### Console Web
- **URL**: http://localhost:9001
- **Usuário**: minioadmin
- **Senha**: minioadmin

### API Endpoint
- **URL**: http://localhost:9000

## Estrutura de Diretórios

### MinIO (S3)

O bucket `acto-maps` contém:

```
acto-maps/
├── geojson-uploads/    # Arquivos GeoJSON processados e armazenados
├── backups/            # Backups do sistema
└── exports/            # Exportações de dados
```

### Storage Local

O diretório `storage/app/private` contém:

```
storage/app/private/
└── livewire-tmp/       # Uploads temporários do Livewire (limpeza automática)
```

**Nota:** O Livewire usa storage local para uploads temporários por questões de performance e compatibilidade. Os arquivos são movidos para MinIO após processamento.

## Uso no Código

### Upload de Arquivo

```php
use Illuminate\Support\Facades\Storage;

// Upload
$path = Storage::disk('s3')->put('geojson-uploads', $file);

// Download
$content = Storage::disk('s3')->get($path);

// URL
$url = Storage::disk('s3')->url($path);

// Delete
Storage::disk('s3')->delete($path);
```

### Verificar Conexão

```php
use Illuminate\Support\Facades\Storage;

if (Storage::disk('s3')->exists('test.txt')) {
    echo "Conectado ao MinIO!";
}
```

## Troubleshooting

### Erro: Connection refused

1. Verifique se o MinIO está rodando:
```bash
podman ps | grep minio
```

2. Verifique os logs:
```bash
podman logs acto-minio
```

### Erro: Bucket não existe

Execute o script de inicialização:
```bash
./scripts/init-minio.sh
```

### Erro: Access Denied

Verifique as credenciais no `.env`:
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`

## Produção

Para produção, recomendamos:

1. **Mudar as credenciais padrão**:
```env
AWS_ACCESS_KEY_ID=seu-access-key-seguro
AWS_SECRET_ACCESS_KEY=sua-secret-key-segura
```

2. **Habilitar TLS**:
```env
AWS_ENDPOINT=https://minio.seudominio.com
AWS_URL=https://minio.seudominio.com/acto-maps
```

3. **Configurar backup automático** do volume `minio_data`

4. **Configurar políticas de acesso** específicas por diretório

## Backup e Restore

### Backup do Bucket

```bash
mc mirror local/acto-maps ./backup-$(date +%Y%m%d)
```

### Restore do Bucket

```bash
mc mirror ./backup-20250101 local/acto-maps
```

## Monitoramento

Verifique o health do MinIO:
```bash
curl http://localhost:9000/minio/health/live
```

Verifique o uso de espaço:
```bash
mc admin info local
```

