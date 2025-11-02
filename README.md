# ACTO Maps - Sistema de Gerenciamento de Camadas Geoespaciais

Sistema de gerenciamento e visualização de camadas geoespaciais usando Laravel 12, ArcGIS Maps SDK v4, PostgreSQL + PostGIS e MinIO.

## Stack Tecnológico

- Backend: Laravel 12 + PHP 8.2
- Admin Panel: Filament v4 (área administrativa /painel)
- Frontend Público: Blade + Bootstrap 5 + jQuery + ArcGIS Maps SDK v4
- Database: PostgreSQL 16 + PostGIS
- Storage: MinIO (S3-compatible)
- Containers: Podman (apenas PostgreSQL e MinIO)

## Arquitetura

```
/ (PÚBLICO) - Mapa com ArcGIS SDK v4 (sem autenticação)
/painel (ADMIN) - Filament com autenticação + roles + 2FA
```

## Pré-requisitos

- PHP 8.2+ com extensões: pdo_pgsql, pgsql, mbstring, xml, bcmath, gd, zip, opcache
- Composer
- Podman ou Docker
- Git

## Setup Inicial

### 1. Clonar Repositório

```powershell
git clone <repository-url>
cd Teste_ACTO
```

### 2. Instalar Dependências

```powershell
composer install
```

### 3. Configurar Ambiente

```powershell
Copy-Item env.example .env
php artisan key:generate
```

Veja todas as variáveis de ambiente em `docs/Environment-Variables.md`

### 4. Gerar Certificados (Opcional para Produção)

```powershell
.\scripts\generate-certificates.ps1
```

Este script gera:
- Certificado CA e chave privada
- Par de chaves RSA 4096
- Arquivo env-snippet.txt pronto para copiar

### 5. Configurar .env

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_password

# MinIO
AWS_ENDPOINT=http://127.0.0.1:9000
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin123
AWS_BUCKET=layers

# Evolution API (WhatsApp 2FA)
EVOLUTION_API_URL=https://sua-evolution-api.com
EVOLUTION_API_KEY=sua_api_key
EVOLUTION_INSTANCE_NAME=acto_maps
TWOFACTOR_WHATSAPP_ENABLED=true

# GeoIP & Anomaly Detection
GEOIP_API_URL=https://ipapi.co
ANOMALY_DETECTION_ENABLED=true

# RSA 4096 Encryption
RSA_PRIVATE_KEY="base64_encoded_private_key"
RSA_PUBLIC_KEY="base64_encoded_public_key"
RSA_ENCRYPTION_ENABLED=true
```

### 6. Iniciar Containers

```powershell
podman-compose up -d
podman-compose ps
```

Serviços disponíveis:
- PostgreSQL + PostGIS: localhost:5432
- MinIO: localhost:9000 (API) e localhost:9001 (Console)
- Mintlify (Documentação): localhost:3000

### 7. Configurar MinIO

Acessar http://localhost:9001 (minioadmin/minioadmin123) e criar bucket "layers".

### 8. Rodar Migrations

```powershell
php artisan migrate
php artisan db:seed
```

### 8. Iniciar Laravel

```powershell
php artisan serve
```

Aplicação disponível em http://localhost:8000

## Acessos

| URL | Autenticação | Descrição |
|-----|--------------|-----------|
| http://localhost:8000 | Público | Mapa com ArcGIS |
| http://localhost:8000/painel | Login Required | Filament Admin Panel |
| http://localhost:3000 | Público | Mintlify - Documentação |
| http://localhost:9001 | - | MinIO Console |
| localhost:5432 | - | PostgreSQL |

### Roles do Sistema

| Role | Acesso /painel | Permissões | 2FA |
|------|----------------|------------|-----|
| Admin | Sim | CRUD completo + usuários | Obrigatório |
| Operator | Sim | CRUD camadas | Opcional |
| Viewer | Sim | Apenas leitura | Opcional |

### API REST

| Endpoint | Autenticação | Acesso |
|----------|--------------|--------|
| GET /api/layers | Pública | Todos |
| POST /api/layers | Sanctum | Admin, Operator |
| PUT /api/layers/{id} | Sanctum | Admin, Operator |
| DELETE /api/layers/{id} | Sanctum | Admin |

### Criar Usuário Admin

```powershell
php artisan make:filament-user
```

## Comandos Úteis

### Containers

```powershell
podman-compose up -d      # Iniciar
podman-compose down       # Parar
podman-compose ps         # Status
podman-compose logs -f    # Logs
```

### Laravel

```powershell
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan migrate
php artisan test
```

### Database

```powershell
podman-compose exec postgres psql -U laravel_user -d laravel
```

## Licença

LICENÇA DE AVALIAÇÃO TÉCNICA — USO NÃO-COMERCIAL

Copyright (c) 2025 - Kemersson Vinicius Gonçalves Teixeira. Todos os direitos reservados.

Este projeto está licenciado exclusivamente para fins de **avaliação técnica**. É proibido qualquer uso comercial, distribuição pública ou incorporação em produtos, serviços ou projetos sem autorização expressa do autor.

**Principais Restrições:**
- Uso permitido APENAS para avaliação técnica do autor
- Proibido uso comercial, distribuição ou sublicenciamento
- Proibido incorporar código em sistemas ou produtos
- Propriedade intelectual permanece com o autor

Para licenciamento comercial ou autorizações especiais, contate: kemerssonvinicius@gmail.com

Veja o texto completo da licença em: [license.txt](license.txt)

## Documentação

### Visualização Interativa (Mintlify)

Acesse a documentação interativa em: http://localhost:3000

```powershell
# Iniciar Mintlify (já incluído no podman-compose)
podman-compose up -d mintlify
```

### Arquivos Markdown

- [Escopo do Projeto](docs/ESCOPO.md) - Frontend público vs autenticado
- [Product Requirements](docs/PRD.md) - Requisitos do produto
- [Technical Requirements](docs/TRD.md) - Requisitos técnicos
- [Business Requirements](docs/BRD.md) - Requisitos de negócio
- [Test-Driven Development](docs/TDD.md) - Estratégia de testes
- [Infraestrutura](docs/Infradoc.md) - Setup e DevOps
- [Segurança](docs/Seguranca.md) - Políticas de segurança
- [Evolution API](docs/Evolution-API.md) - WhatsApp 2FA
- [Environment Variables](docs/Environment-Variables.md) - Variáveis de ambiente
- [Database Schema](docs/database.dbml) - Estrutura do banco

## Testes (TDD)

### Configurar Ambiente de Testes

```powershell
# 1. Configurar banco de dados de testes
.\scripts\setup-test-db.ps1

# 2. Executar migrations
php artisan migrate --env=testing
```

### Executar Testes

```powershell
# Todos os testes
php artisan test

# Ou use o script
.\scripts\test.ps1

# Testes específicos
.\scripts\test.ps1 -Type unit
.\scripts\test.ps1 -Type feature -Coverage
.\scripts\test.ps1 -Filter "layer"

# Gerar relatório de coverage
.\scripts\coverage-report.ps1 -Open
```

### Estrutura de Testes

- `tests/Unit/` - Testes unitários (rápidos, isolados)
- `tests/Feature/` - Testes de feature (com database)
- `tests/Integration/` - Testes de integração (PostGIS, MinIO, APIs)
- `tests/Browser/` - Testes E2E (Laravel Dusk)

### Métricas

- Coverage mínimo: 80%
- Tempo de execução: < 2 minutos
- Ferramentas: Pest PHP, PHPUnit, Mockery

Documentação completa: [TDD.md](docs/TDD.md)

## Segurança

### Autenticação
- Senhas: Argon2ID hash
- 2FA: TOTP (Google Authenticator)
- API: Laravel Sanctum
- Rate Limiting ativo
- CORS configurado

### 2FA via WhatsApp
- Integração com Evolution API v2
- Códigos enviados via WhatsApp para Admins
- Log destacado com estrutura retangular

```
==================================================
    CÓDIGO 2FA GERADO
==================================================
    
    Usuário: admin@example.com
    Código:  123456
    Válido:  30 segundos
    
==================================================
```

### Segurança Avançada

**Detecção de Anomalias Geográficas**
- GeoIP API para coleta de localização por IP
- Cálculo de distância com fórmula de Haversine
- Proteção: Se 3+ tentativas em locais impossíveis:
  - Forçar 2FA obrigatório
  - Encerrar todas as sessões
  - Blacklist de tokens
  - Notificação via WhatsApp

**Criptografia RSA 4096 E2E**
- RSA 4096 bits para dados sensíveis
- Certificado CA único no .env
- Logs detalhados de operações

**Tabelas Intermediárias Criptografadas**
- Dados sensíveis em tabelas separadas
- Criptografia AES-256-GCM
- Proteção contra SQL injection

**Argon2Id**
- Hash com 64MB RAM, 4 iterações
- Proteção contra GPU/ASIC attacks

## Testes

```powershell
php artisan test
./vendor/bin/pest
```

## Troubleshooting

**Extensões PHP faltando**
```powershell
php -m
```
Editar php.ini e habilitar extensões necessárias.

**Erro de conexão PostgreSQL**
```powershell
podman-compose ps
podman-compose logs postgres
```

**Permissões Windows**
```powershell
icacls storage /grant Users:F /t
icacls bootstrap\cache /grant Users:F /t
```

**Porta 8000 em uso**
```powershell
php artisan serve --port=8001
```

## Deploy Produção

```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

Configurar .env:
- APP_ENV=production
- APP_DEBUG=false
- HTTPS habilitado

## Licença

[Definir licença]
