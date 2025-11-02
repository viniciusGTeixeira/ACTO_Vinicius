# ACTO Maps

Sistema de Gestão Geoespacial com visualização de mapas interativos.
 
> **📚 [Documentação Completa](docs/)** - TRD, PRD, BRD, Infraestrutura, Segurança, API, Storage  

## Instalação Rápida

### Requisitos

- PHP 8.2+
- Composer
- Node.js 20+
- Podman Desktop
- PostgreSQL Client (psql)

### Instalação Automatizada (Windows)

Execute um único comando no PowerShell:

```powershell
.\setup.ps1
```

Este script irá:
- Verificar todos os requisitos
- Instalar dependências PHP e Node.js
- Configurar banco de dados PostgreSQL + PostGIS
- Configurar storage MinIO (S3-compatible)
- Executar migrations
- Criar usuário administrador
- Compilar assets

### Iniciar o Servidor

```powershell
php artisan serve
```

Acesse: **http://localhost:8000**

## Credenciais Padrão

### Painel Administrativo
- **URL**: http://localhost:8000/painel
- **Email**: admin@acto.com
- **Senha**: password

### MinIO Console
- **URL**: http://localhost:9001
- **Usuário**: minioadmin
- **Senha**: minioadmin

## Estrutura do Projeto

```
acto-maps/
├── app/
│   ├── Filament/            # Painel administrativo
│   ├── Http/Controllers/    # Controllers da API e Web
│   ├── Models/              # Models Eloquent
│   └── Repositories/        # Repositórios (padrão Repository)
├── database/
│   ├── migrations/          # Migrations do banco
│   ├── seeders/             # Seeders
│   └── init/                # Scripts de inicialização do DB
├── docs/                    # Documentação completa
├── resources/
│   └── views/
│       ├── auth/            # Views de autenticação
│       └── map/             # View do mapa público
├── routes/
│   ├── api.php              # Rotas da API
│   └── web.php              # Rotas web
└── setup.ps1                # Script de instalação automatizada
```

## Funcionalidades

### Principais

- **Painel Administrativo** (Filament 4.0)
  - CRUD completo de camadas geográficas
  - Upload de arquivos GeoJSON
  - Gerenciamento de usuários e permissões
  
- **Mapa Interativo Público**
  - Visualização de todas as camadas cadastradas
  - Toggle de visibilidade por camada
  - Powered by ArcGIS Maps SDK v4
  
- **Autenticação Segura**
  - Laravel Fortify
  - Autenticação de dois fatores (2FA)
  - Hash de senha com Argon2ID

- **Storage S3-Compatible**
  - MinIO para armazenamento de arquivos
  - Backup automático configurável

- **Banco de Dados Geoespacial**
  - PostgreSQL 16 + PostGIS 3.4
  - Suporte completo a geometrias
  - Índices espaciais otimizados

## Uso

### Criar uma Camada

1. Acesse http://localhost:8000/painel
2. Faça login com as credenciais admin
3. Clique em **"Camadas"** no menu
4. Clique em **"Novo Layer"**
5. Preencha o nome e faça upload de um arquivo GeoJSON válido
6. Salve

### Visualizar no Mapa

1. Acesse http://localhost:8000/
2. As camadas aparecerão automaticamente no mapa
3. Use o painel lateral para ativar/desativar camadas

### API REST

Endpoint disponível em `/api/layers`:

```bash
# Listar todas as camadas
curl http://localhost:8000/api/layers

# Obter GeoJSON de uma camada
curl http://localhost:8000/api/layers/{id}/geojson
```

## Comandos Úteis

### Desenvolvimento

```powershell
# Iniciar servidor de desenvolvimento
php artisan serve

# Watch de assets (em outro terminal)
npm run dev

# Ver logs dos containers
podman-compose logs -f

# Executar migrations
php artisan migrate

# Criar novo seeder
php artisan make:seeder NomeSeeder
```

### Produção

```powershell
# Build de assets para produção
npm run build

# Limpar caches
php artisan optimize:clear

# Cache de configuração
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Banco de Dados

```powershell
# Resetar banco de dados
php artisan migrate:fresh --seed

# Backup do banco
podman exec acto-postgres pg_dump -U laravel_user laravel > backup.sql

# Restore do banco
podman exec -i acto-postgres psql -U laravel_user laravel < backup.sql
```

### Containers

```powershell
# Iniciar todos os serviços
podman-compose up -d

# Parar todos os serviços
podman-compose down

# Ver status
podman-compose ps

# Logs específicos
podman-compose logs postgres
podman-compose logs minio
```

## Arquitetura

### Backend

- **Framework**: Laravel 12
- **Admin Panel**: Filament 4.0
- **Database**: PostgreSQL 16 + PostGIS 3.4
- **Storage**: MinIO (S3-compatible)
- **Auth**: Laravel Fortify + 2FA

### Frontend

- **CSS Framework**: Bootstrap 5 + Tailwind CSS
- **JavaScript**: jQuery
- **Maps**: ArcGIS Maps SDK for JavaScript v4.28

### Infraestrutura

- **Containers**: Podman + Podman Compose
- **Web Server**: PHP Built-in (dev) / Nginx (prod)
- **Object Storage**: MinIO

## Documentação Completa

Documentação detalhada disponível em `/docs`:

- **[TRD.md](docs/TRD.md)** - Documento Técnico de Requisitos
- **[Infradoc.md](docs/Infradoc.md)** - Documentação de Infraestrutura
- **[STORAGE.md](docs/STORAGE.md)** - Configuração de Storage

## Troubleshooting

### Erro: Connection refused (PostgreSQL)

```powershell
# Verificar se container está rodando
podman ps | findstr postgres

# Reiniciar container
podman-compose restart postgres
```

### Erro: MinIO bucket não existe

```powershell
# Reconfigurar MinIO
.\setup.ps1
# Ou manualmente via console: http://localhost:9001
```

### Erro: Class not found

```powershell
# Recompilar autoload
composer dump-autoload
```

### Assets não carregam

```powershell
# Limpar cache e recompilar
npm run build
php artisan view:clear
```

## Segurança

- Autenticação de dois fatores (2FA)
- Hash de senha com Argon2ID
- Rate limiting em todas as rotas
- CSRF protection
- XSS protection
- SQL Injection protection (PDO)
- Validação de uploads
- Session security configurada

## Licença

MIT License

Copyright (c) 2025 Kemersson Vinicius Gonçalves Teixeira

## Suporte

Para dúvidas e problemas, consulte a documentação em `/docs` ou abra uma issue.

---

**ACTO Maps** - Sistema de Gestão Geoespacial
