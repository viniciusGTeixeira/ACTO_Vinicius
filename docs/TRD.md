# Technical Requirements Document (TRD)

## 1. Arquitetura

### 1.1 Padrão Arquitetural
- BFF (Backend for Frontend): API específica para necessidades do frontend
- SOLID Principles: Garantir código manutenível e extensível
- KISS (Keep It Simple): Evitar over-engineering

### 1.2 Estrutura de Camadas

```mermaid
%%{init: {'theme':'forest'}}%%
flowchart TD
    subgraph frontend["Frontend Layer"]
        A1[Admin Panel<br/>Filament v4<br/>Gerenciar Camadas]
        A2[Frontend Público<br/>Blade + Bootstrap<br/>Visualizar Mapas + ArcGIS]
    end
    
    B[BFF Layer<br/>Laravel Controllers + Middleware]
    C[Service Layer<br/>Business Logic + Validation]
    D[Repository Layer<br/>Data Access + ORM Eloquent]
    E[Database<br/>PostgreSQL + PostGIS]
    
    A1 -->|LIVEWIRE/AJAX| B
    A2 -->|HTTP/AJAX| B
    B --> C
    C --> D
    D --> E
```

## 2. Stack Tecnológico

### 2.1 Backend
- PHP: 8.2+
- Framework: Laravel 12
- ORM: Eloquent
- Autenticação: Laravel Fortify + Laravel Permissions + 2FA (TOTP)
- Admin Panel: Filament v4
- Notificações: Evolution API v2 (WhatsApp)

### 2.2 Frontend

#### Admin Panel (Área Administrativa)
- Framework: Filament v4
- Características:
  - Interface moderna e responsiva (Tailwind CSS)
  - CRUD automático para camadas
  - Formulários com validação
  - Tabelas com filtros, busca e ordenação
  - Dashboard com widgets
  - Sistema de notificações
  - Dark mode nativo
  - Multi-idioma

#### Frontend Público (Visualização de Mapas)
- Template Engine: Blade
- CSS Framework: Bootstrap 5
- JavaScript: jQuery + Vanilla JS
- Maps: ArcGIS Maps SDK for JavaScript v4
- Características:
  - Interface pública para visualização de mapas
  - Interação com camadas
  - Design responsivo e moderno

### 2.3 Banco de Dados
- RDBMS: PostgreSQL 16+ com extensão PostGIS
- Características:
  - ACID compliant
  - Suporte a tipos geométricos (POINT, LINESTRING, POLYGON)
  - Índices espaciais (GIST)

### 2.4 Storage
- Object Storage: MinIO (S3-compatible)
- Armazenamento de arquivos de camadas e metadados

### 2.5 Containerização
- Engine: Podman
- Orchestration: Podman Compose

## 3. Estrutura de Diretórios

```
Teste_ACTO/
├── app/
│   ├── Contracts/                     # INTERFACES (SOLID)
│   │   ├── Repositories/
│   │   │   └── LayerRepositoryInterface.php
│   │   └── Services/
│   │       └── LayerServiceInterface.php
│   ├── Filament/                      # ADMIN PANEL (Filament v4)
│   │   ├── Resources/
│   │   │   └── Layers/
│   │   │       ├── LayerResource.php  # CRUD de camadas
│   │   │       ├── Schemas/           # Form schemas
│   │   │       │   └── LayerForm.php
│   │   │       ├── Tables/            # Table configurations
│   │   │       │   └── LayersTable.php
│   │   │       └── Pages/             # Resource pages
│   │   │           ├── CreateLayer.php
│   │   │           ├── EditLayer.php
│   │   │           └── ListLayers.php
│   │   ├── Pages/
│   │   │   └── Dashboard.php          # Dashboard (futuro)
│   │   └── Widgets/
│   │       └── StatsOverview.php      # Widgets (futuro)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── LayerController.php    # API REST
│   │   │   └── MapController.php          # Frontend público
│   │   ├── Requests/                  # FORM REQUESTS
│   │   │   ├── StoreLayerRequest.php
│   │   │   └── UpdateLayerRequest.php
│   │   └── Rules/                     # CUSTOM VALIDATION RULES
│   │       └── ValidGeojsonFile.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Layer.php
│   ├── Repositories/                  # DATA ACCESS LAYER
│   │   └── LayerRepository.php
│   ├── Services/                      # BUSINESS LOGIC LAYER
│   │   └── LayerService.php
│   ├── Helpers/                       # HELPER FUNCTIONS
│   │   ├── ResponseHelper.php
│   │   └── GeometryHelper.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── RepositoryServiceProvider.php  # DI BINDINGS
│       └── Filament/
│           └── AdminPanelProvider.php
├── database/
│   ├── migrations/
│   └── seeders/
├── docs/
├── public/
│   ├── js/
│   │   └── map.js                     # Frontend público
│   └── css/
│       └── app.css
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php          # Layout base
│       ├── map/
│       │   └── index.blade.php        # Visualização de mapas
│       └── welcome.blade.php          # Homepage
├── routes/
│   ├── web.php                        # Rotas públicas
│   └── api.php                        # Rotas API
├── tests/
│   ├── Unit/
│   └── Feature/
├── podman-compose.yml
├── .env.example
└── README.md
```

## 4. Banco de Dados

### 4.1 Tabelas Principais

#### auth.users
```sql
- id (bigint, PK, auto-increment)
- name (varchar(255), NOT NULL)
- email (varchar(255), unique, NOT NULL)
- password (varchar(255), NOT NULL) -- Argon2ID hash
- two_factor_secret (text, nullable)
- two_factor_recovery_codes (text, nullable)
- two_factor_confirmed_at (timestamp, nullable)
- phone_number (varchar(20), nullable)
- two_factor_whatsapp_enabled (boolean, default: false)
- last_login_ip (varchar(45), nullable, indexed)
- last_login_latitude (decimal(10,8), nullable)
- last_login_longitude (decimal(11,8), nullable)
- last_login_country (varchar(2), nullable)
- last_login_at (timestamp, nullable)
- failed_login_attempts (integer, default: 0)
- locked_until (timestamp, nullable)
- timestamps
- Indexes: (email), (last_login_ip), (last_login_latitude, last_login_longitude)
```

**Schema**: `auth` (organização por schemas imperativos)  
**2FA**: Suporta TOTP e WhatsApp via Evolution API  
**GeoIP**: Campos para detecção de anomalias geográficas

#### auth.roles & auth.permissions
- Gerenciadas pelo Laravel Permission (spatie/laravel-permission)
- Tabelas: `auth.roles`, `auth.permissions`, `auth.model_has_roles`, `auth.model_has_permissions`, `auth.role_has_permissions`

#### geo.layers
```sql
- id (bigint, PK, auto-increment)
- name (varchar(100), NOT NULL, indexed)
- geometry (geometry(Geometry, 4326), NOT NULL) -- PostGIS geometry with SRID 4326
- created_at (timestamp)
- updated_at (timestamp)
- Indexes: (name), GIST(geometry)
```

**Schema**: `geo` (organização por schemas imperativos)  
**Geometria**: Suporta todos os tipos do GeoJSON (Point, LineString, Polygon, Multi*)

## 5. APIs

### 5.1 Endpoints Implementados

#### GET /api/layers
**Descrição**: Retorna todas as camadas  
**Autenticação**: Não requerida  
**Rate Limit**: 60 req/min por IP

**Response**:
```json
{
  "success": true,
  "message": "Layers retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Camada Exemplo",
      "created_at": "2025-11-02T12:00:00.000000Z",
      "updated_at": "2025-11-02T12:00:00.000000Z"
    }
  ]
}
```

#### GET /api/layers/{id}
**Descrição**: Retorna detalhes de uma camada específica  
**Autenticação**: Não requerida  
**Rate Limit**: 60 req/min por IP

**Response**:
```json
{
  "success": true,
  "message": "Layer retrieved successfully",
  "data": {
    "id": 1,
    "name": "Camada Exemplo",
    "created_at": "2025-11-02T12:00:00.000000Z",
    "updated_at": "2025-11-02T12:00:00.000000Z"
  }
}
```

#### GET /api/layers/geojson/all
**Descrição**: Retorna todas as camadas como GeoJSON FeatureCollection  
**Autenticação**: Não requerida  
**Rate Limit**: 60 req/min por IP

**Response**:
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "id": 1,
      "properties": {
        "name": "Camada Exemplo",
        "created_at": "2025-11-02T12:00:00.000000Z",
        "updated_at": "2025-11-02T12:00:00.000000Z"
      },
      "geometry": {
        "type": "Polygon",
        "coordinates": [[...]]
      }
    }
  ]
}
```

#### GET /api/layers/{id}/geojson
**Descrição**: Retorna uma camada específica como GeoJSON Feature  
**Autenticação**: Não requerida  
**Rate Limit**: 60 req/min por IP

**Response**:
```json
{
  "type": "Feature",
  "id": 1,
  "properties": {
    "name": "Camada Exemplo",
    "created_at": "2025-11-02T12:00:00.000000Z",
    "updated_at": "2025-11-02T12:00:00.000000Z"
  },
  "geometry": {
    "type": "Polygon",
    "coordinates": [[...]]
  }
}
```

#### POST /api/layers (Admin only)
Descrição: Cria nova camada

#### PUT /api/layers/{id} (Admin only)
Descrição: Atualiza camada

#### DELETE /api/layers/{id} (Admin only)
Descrição: Remove camada

## 6. Segurança

### 6.1 Autenticação
- Laravel Sanctum para API tokens
- Session-based auth para web
- 2FA usando TOTP (Google Authenticator compatible)

### 6.2 Autorização
- Middleware de roles (admin, operator, viewer)
- Policies para controle granular de acesso

### 6.3 Criptografia
- Senhas: Argon2ID
- Secrets 2FA: encrypted
- Comunicação: HTTPS only (produção)

## 7. Performance

### 7.1 Otimizações
- Query Builder com índices apropriados
- Eager Loading para relacionamentos
- Cache de camadas frequentemente acessadas (Redis/File)
- Paginação de resultados

### 7.2 Métricas
- Response time < 500ms
- Database queries < 10 por request
- Memory usage < 128MB por request

## 8. Testes

### 8.1 Estratégia
- Unit Tests: Models, Services, Repositories (Pest PHP)
- Feature Tests: Controllers, API endpoints
- E2E Tests: User flows (Laravel Dusk/Playwright)
- Load Tests: JMeter para APIs

### 8.2 Cobertura Mínima
- Services: 90%
- Controllers: 80%
- Models: 70%

## 9. Deployment

### 9.1 Ambientes
- Local: Podman Compose
- Staging: Container registry + Podman
- Production: Kubernetes ou Podman em VMs

### 9.2 CI/CD
- GitHub Actions ou GitLab CI
- Pipeline: lint → test → build → deploy

## 10. Dependências Principais

```json
{
  "laravel/framework": "^12.0",
  "laravel/fortify": "^1.0",
  "spatie/laravel-permission": "^6.0",
  "filament/filament": "^4.0",
  "pestphp/pest": "^2.0"
}
```
