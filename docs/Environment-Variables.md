# Variáveis de Ambiente

## 1. Instruções

Copie o arquivo `env.example` para `.env`:
```powershell
Copy-Item env.example .env
```

Gere a chave da aplicação:
```powershell
php artisan key:generate
```

## 2. Configurações Críticas

### 2.1 Rate Limiting

Limites de requisições por minuto:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| RATE_LIMIT_PUBLIC_API | 60 | Requisições públicas por minuto por IP |
| RATE_LIMIT_PUBLIC_API_DECAY | 1 | Tempo em minutos para resetar contador |
| RATE_LIMIT_AUTHENTICATED_API | 100 | Requisições autenticadas por minuto por usuário |
| RATE_LIMIT_AUTHENTICATED_API_DECAY | 1 | Tempo em minutos para resetar contador |
| RATE_LIMIT_ADMIN_API | 30 | Requisições admin por minuto |
| RATE_LIMIT_ADMIN_API_DECAY | 1 | Tempo em minutos para resetar contador |
| RATE_LIMIT_LOGIN | 5 | Tentativas de login por minuto por IP |
| RATE_LIMIT_LOGIN_DECAY | 1 | Tempo em minutos para resetar contador |

### 2.2 Session

Configurações de sessão em minutos:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| SESSION_LIFETIME | 120 | Duração máxima da sessão (2 horas) |
| SESSION_IDLE_TIMEOUT | 30 | Timeout por inatividade (30 minutos) |
| SESSION_ABSOLUTE_TIMEOUT | 480 | Timeout absoluto independente de atividade (8 horas) |
| SESSION_DRIVER | database | Driver para armazenar sessões |
| SESSION_ENCRYPT | false | Criptografar dados de sessão |
| SESSION_HTTP_ONLY | true | Prevenir acesso via JavaScript |
| SESSION_SAME_SITE | lax | Política SameSite (lax, strict, none) |
| SESSION_SECURE_COOKIE | false | Apenas HTTPS (true em produção) |

### 2.3 Certificados CA e TLS

Certificados para criptografia ponta a ponta:

```env
CA_CERTIFICATE="-----BEGIN CERTIFICATE-----
MIIDXTCCAkWgAwIBAgIJAKL0UG+mRnqlMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNV
BAYTAkJSMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBX
aWRnaXRzIFB0eSBMdGQwHhcNMjMwMTAxMDAwMDAwWhcNMjQwMTAxMDAwMDAwWjBF
...
-----END CERTIFICATE-----"

CA_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...
-----END PRIVATE KEY-----"
```

Gerar certificados CA:
```powershell
# Gerar chave privada CA
openssl genrsa -out ca-key.pem 4096

# Gerar certificado CA / Caso queira por que estarão no env_example
openssl req -new -x509 -days 365 -key ca-key.pem -out ca-cert.pem -subj "/C=BR/ST=DF/O=ACTO/CN=ACTO Root CA"

# Ver certificado / Caso queira
openssl x509 -in ca-cert.pem -text -noout

# Converter para formato de uma linha para .env
$cert = Get-Content ca-cert.pem -Raw
$cert = $cert -replace "`r`n", "\n"
Write-Host "CA_CERTIFICATE=`"$cert`""
```

### 2.4 RSA 4096 Encryption

Chaves RSA para criptografia de dados:

```env
RSA_ENCRYPTION_ENABLED=true
RSA_KEY_SIZE=4096
RSA_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----
MIIJKAIBAAKCAgEA...
-----END RSA PRIVATE KEY-----"
RSA_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIICCgKCAgEA...
-----END PUBLIC KEY-----"
```

Gerar par de chaves RSA:
```powershell
# Gerar chave privada RSA 4096
openssl genrsa -out rsa-private.pem 4096

# Extrair chave pública
openssl rsa -in rsa-private.pem -pubout -out rsa-public.pem

# Ver chaves
Get-Content rsa-private.pem
Get-Content rsa-public.pem

# Converter para .env
$privateKey = Get-Content rsa-private.pem -Raw
$publicKey = Get-Content rsa-public.pem -Raw
$privateKey = $privateKey -replace "`r`n", "\n"
$publicKey = $publicKey -replace "`r`n", "\n"

Write-Host "RSA_PRIVATE_KEY=`"$privateKey`""
Write-Host "RSA_PUBLIC_KEY=`"$publicKey`""
```

## 3. Segurança

### 3.1 Argon2ID Hashing

Configuração para hash de senhas:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| HASH_DRIVER | argon2id | Algoritmo de hash |
| ARGON2_MEMORY | 65536 | Memória em KB (64 MB) |
| ARGON2_THREADS | 1 | Threads paralelas |
| ARGON2_TIME | 4 | Número de iterações |

### 3.2 Anomaly Detection

Detecção de anomalias geográficas:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| ANOMALY_DETECTION_ENABLED | true | Habilitar detecção |
| ANOMALY_MAX_ATTEMPTS | 3 | Tentativas antes de bloquear |
| ANOMALY_TIME_WINDOW | 3600 | Janela de tempo em segundos (1 hora) |
| ANOMALY_MIN_DISTANCE_KM | 500 | Distância mínima para considerar impossível |
| ANOMALY_ACTION | block | Ação: block, alert, log |

### 3.3 GeoIP

Coleta de localização por IP:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| GEOIP_ENABLED | true | Habilitar GeoIP |
| GEOIP_API_URL | https://ipapi.co | URL da API gratuita |
| GEOIP_API_KEY | | Chave da API (opcional) |
| GEOIP_CACHE_DURATION | 86400 | Cache em segundos (24 horas) |

APIs GeoIP gratuitas:
- ipapi.co: https://ipapi.co/{ip}/json/
- ip-api.com: http://ip-api.com/json/{ip}
- ipinfo.io: https://ipinfo.io/{ip}/json

### 3.4 Two-Factor Authentication

Configuração 2FA:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| TWOFACTOR_ENABLED | true | Habilitar 2FA |
| TWOFACTOR_REQUIRED_FOR_ADMIN | true | Obrigatório para admins |
| TWOFACTOR_ISSUER | ACTO Maps | Nome do emissor no app |
| TWOFACTOR_WINDOW | 1 | Janela de validação (30s x window) |
| TWOFACTOR_RECOVERY_CODES | 8 | Quantidade de recovery codes |

### 3.5 WhatsApp via Evolution API

Envio de tokens 2FA via WhatsApp:

| Variável | Exemplo | Descrição |
|----------|---------|-----------|
| TWOFACTOR_WHATSAPP_ENABLED | true | Habilitar WhatsApp 2FA |
| EVOLUTION_API_URL | https://api.evolution-api.com | URL da Evolution API |
| EVOLUTION_API_KEY | abc123xyz | API Key da Evolution |
| EVOLUTION_INSTANCE_NAME | acto-maps | Nome da instância |
| TWOFACTOR_WHATSAPP_TEMPLATE | Seu código: {code} | Template da mensagem |

Template tags disponíveis:
- {code}: Código 2FA
- {app}: Nome da aplicação
- {expires}: Tempo de expiração

## 4. Database

### 4.1 PostgreSQL

Conexão com PostgreSQL:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| DB_CONNECTION | pgsql | Driver do banco |
| DB_HOST | 127.0.0.1 | Host do PostgreSQL |
| DB_PORT | 5432 | Porta |
| DB_DATABASE | laravel | Nome do banco |
| DB_USERNAME | laravel_user | Usuário |
| DB_PASSWORD | secret | Senha |

### 4.2 PostGIS

Configuração PostGIS:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| POSTGIS_VERSION | 3.4 | Versão do PostGIS |
| POSTGIS_SRID | 4326 | Sistema de referência (WGS84) |
| POSTGIS_GEOMETRY_TYPE | geometry | Tipo de coluna |

## 5. Storage

### 5.1 MinIO / S3

Configuração do MinIO:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| FILESYSTEM_DISK | s3 | Driver padrão |
| AWS_ACCESS_KEY_ID | minioadmin | Access Key |
| AWS_SECRET_ACCESS_KEY | minioadmin | Secret Key |
| AWS_DEFAULT_REGION | us-east-1 | Região |
| AWS_BUCKET | acto-maps | Nome do bucket |
| AWS_ENDPOINT | http://127.0.0.1:9000 | Endpoint do MinIO |
| AWS_USE_PATH_STYLE_ENDPOINT | true | Usar path-style |
| AWS_URL | http://127.0.0.1:9000/acto-maps | URL pública |

## 6. Frontend

### 6.1 Filament

Configuração do painel admin:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| FILAMENT_DOMAIN | null | Domínio customizado |
| FILAMENT_PATH | painel | Path do painel (/painel) |
| FILAMENT_TIMEZONE | America/Sao_Paulo | Timezone |
| FILAMENT_LOCALE | pt_BR | Idioma |

### 6.2 ArcGIS

Configuração do mapa:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| ARCGIS_API_KEY | | API Key do ArcGIS |
| ARCGIS_BASEMAP | topo-vector | Mapa base |
| ARCGIS_CENTER_LAT | -15.7801 | Latitude inicial (Brasília) |
| ARCGIS_CENTER_LNG | -47.9292 | Longitude inicial |
| ARCGIS_ZOOM_LEVEL | 4 | Nível de zoom inicial |

Obter API Key: https://developers.arcgis.com/

### 6.3 Layers

Configuração de camadas:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| LAYER_MAX_FILE_SIZE | 10240 | Tamanho máximo em KB (10 MB) |
| LAYER_ALLOWED_TYPES | point,linestring,polygon | Tipos permitidos |
| LAYER_CACHE_TTL | 3600 | Cache em segundos (1 hora) |

## 7. API

Configuração da API REST:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| API_PREFIX | api | Prefixo das rotas |
| API_VERSION | v1 | Versão da API |
| API_PAGINATION_DEFAULT | 15 | Itens por página |
| API_PAGINATION_MAX | 100 | Máximo de itens por página |

## 8. Monitoring

### 8.1 Logs

Configuração de logs:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| LOG_CHANNEL | stack | Canal de log |
| LOG_LEVEL | debug | Nível: debug, info, warning, error |
| AUDIT_ENABLED | true | Habilitar audit log |
| AUDIT_LOG_RETENTION_DAYS | 180 | Retenção de logs (6 meses) |
| AUDIT_LOG_SENSITIVE_DATA | false | Logar dados sensíveis |

### 8.2 Monitoring Services

Integração com serviços de monitoramento:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| MONITORING_ENABLED | false | Habilitar monitoramento |
| SENTRY_LARAVEL_DSN | | DSN do Sentry |
| SENTRY_TRACES_SAMPLE_RATE | 0.2 | Taxa de amostragem (20%) |

## 9. Performance

Otimizações de performance:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| OPCACHE_ENABLE | 1 | Habilitar OPCache |
| OPCACHE_MEMORY | 128 | Memória OPCache em MB |
| RESPONSE_CACHE_ENABLED | false | Cache de respostas HTTP |
| RESPONSE_CACHE_TTL | 3600 | TTL do cache em segundos |

## 10. Backup

Configuração de backup:

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| BACKUP_ENABLED | true | Habilitar backups |
| BACKUP_SCHEDULE | 0 2 * * * | Cron schedule (diário 2h) |
| BACKUP_RETENTION_DAYS | 30 | Retenção em dias |
| BACKUP_ENCRYPT | true | Criptografar backups |
| BACKUP_DESTINATION | local | Destino: local, s3 |

## 11. Produção

Configurações obrigatórias em produção:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

DB_PASSWORD=senha_forte_aleatoria

AWS_ACCESS_KEY_ID=custom_access_key
AWS_SECRET_ACCESS_KEY=senha_forte_aleatoria

RSA_ENCRYPTION_ENABLED=true
TLS_ENABLED=true

SECURITY_HEADERS_ENABLED=true
SECURITY_HSTS_ENABLED=true

BACKUP_ENABLED=true
BACKUP_ENCRYPT=true

MONITORING_ENABLED=true
```

## 12. Validação

Verificar configurações:
```powershell
php artisan config:show
php artisan about
```

Testar conexões:
```powershell
# Banco de dados
php artisan tinker
>>> DB::connection()->getPdo();

# MinIO
php artisan tinker
>>> Storage::disk('s3')->exists('test.txt');

# Cache
php artisan cache:clear
php artisan config:cache
```

## 13. Troubleshooting

Problemas comuns:

### Certificado CA inválido
```
Error: Unable to verify certificate
```
Solução: Verificar formato do certificado no .env (usar \n para quebras de linha)

### Rate limit muito restritivo
```
429 Too Many Requests
```
Solução: Ajustar RATE_LIMIT_* conforme necessidade

### Session expira muito rápido
```
Session expired, please login again
```
Solução: Aumentar SESSION_LIFETIME e SESSION_IDLE_TIMEOUT

### RSA encryption falha
```
RSA decryption failed
```
Solução: Verificar se chaves privada e pública são um par válido

## 14. Referências

- Laravel Configuration: https://laravel.com/docs/12.x/configuration
- Filament Configuration: https://filamentphp.com/docs/4.x/panel-configuration
- PostgreSQL: https://www.postgresql.org/docs/
- MinIO: https://min.io/docs/
- OpenSSL: https://docs.openssl.org/

