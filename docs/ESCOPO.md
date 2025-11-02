# Clarificação de Escopo - Tarefa vs Melhorias

## Escopo do Projeto

### Tarefa Original (Requisitos Mínimos)

**Parte 1: Painel Administrativo (/painel)**
- CRUD de camadas
- Upload de GeoJSON
- Autenticação simples com senha
- Tabela: id, name, geometry (PostGIS indexado)

**Parte 2: Mapa Público (/)**
- PÚBLICO (sem autenticação)
- Exibir todas as camadas
- ArcGIS Maps SDK v4

### Melhorias Adicionais (Solicitadas pelo Cliente)

1. Roles Complexas: Admin, Operator, Viewer
2. 2FA Obrigatório: TOTP (Google Authenticator)
3. API REST com Permissões: Endpoints RESTful + Sanctum
4. Dashboard Bentogrid: Layout assimétrico moderno
5. WhatsApp Integration: Envio de códigos 2FA via Evolution API
6. Detecção de Anomalias: GeoIP + Haversine
7. Criptografia RSA 4096: E2E para dados sensíveis

### NÃO Foi Solicitado

- Frontend /map autenticado (o mapa é PÚBLICO na raiz /)
- Área de visualização separada para usuários logados

## Arquitetura

```
/ (PÚBLICO) → Mapa com todas as camadas
/painel (AUTENTICADO) → CRUD de camadas (Filament)
```

## Rotas do Sistema

| Rota | Acesso | Descrição |
|------|--------|-----------|
| / | Público | Mapa com ArcGIS |
| /painel | Admin/Operator/Viewer | Filament admin |
| /painel/login | Público | Login |
| GET /api/layers | Público | Lista camadas (GeoJSON) |
| POST /api/layers | Admin/Operator | Criar camada |
| PUT /api/layers/{id} | Admin/Operator | Editar camada |
| DELETE /api/layers/{id} | Admin | Deletar camada |

## Personas e Acessos

### 1. Visitante Público
- Acessa / sem login
- Visualiza mapa com todas as camadas
- Pode interagir (zoom, pan, toggle)
- API pública: GET /api/layers

### 2. Admin (com 2FA)
- Acessa /painel com login + 2FA
- Dashboard bentogrid
- CRUD completo de camadas
- Gerenciar usuários e roles
- API: todas as operações

### 3. Operator
- Acessa /painel com login
- Dashboard bentogrid
- CRUD de camadas
- NÃO gerencia usuários
- API: GET, POST, PUT de camadas

### 4. Viewer
- Acessa /painel com login
- Dashboard bentogrid (só visualização)
- Apenas leitura de camadas
- NÃO pode criar/editar/deletar
- API: apenas GET

## Tabela de Dados

### Tabela layers (Conforme Tarefa)

```sql
CREATE TABLE layers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    geometry GEOMETRY NOT NULL
);

CREATE INDEX idx_layers_geometry ON layers USING GIST (geometry);
```

### Campos Adicionais (Melhorias)

```sql
ALTER TABLE layers ADD COLUMN description TEXT;
ALTER TABLE layers ADD COLUMN type VARCHAR(50);
ALTER TABLE layers ADD COLUMN is_active BOOLEAN DEFAULT true;
ALTER TABLE layers ADD COLUMN created_by BIGINT REFERENCES users(id);
ALTER TABLE layers ADD COLUMN created_at TIMESTAMP DEFAULT NOW();
ALTER TABLE layers ADD COLUMN updated_at TIMESTAMP DEFAULT NOW();
ALTER TABLE layers ADD COLUMN deleted_at TIMESTAMP;
```

## Checklist de Implementação

### Tarefa Original (Mínimo)
- [ ] Mapa público em / com ArcGIS SDK v4
- [ ] Carrega todas as camadas via API
- [ ] Painel admin em /painel com autenticação
- [ ] CRUD de camadas via Filament
- [ ] Upload de GeoJSON funcional
- [ ] PostgreSQL + PostGIS com índice GIST

### Melhorias Adicionais
- [ ] Roles: Admin, Operator, Viewer
- [ ] 2FA obrigatório para Admin (TOTP)
- [ ] Dashboard com layout bentogrid
- [ ] API REST com Sanctum
- [ ] Permissões granulares
- [ ] Rate limiting
- [ ] WhatsApp Integration (Evolution API)
- [ ] Detecção anomalias geográficas
- [ ] Criptografia RSA 4096 E2E

## Priorização

### P0 (Crítico - Tarefa Original)
1. Mapa público funcionando
2. CRUD de camadas
3. Upload GeoJSON
4. PostGIS indexado

### P1 (Alta - Melhorias Solicitadas)
1. Roles (Admin/Operator/Viewer)
2. 2FA para Admin
3. API REST com auth
4. Dashboard bentogrid

### P2 (Média - Nice to Have)
1. Widgets avançados
2. Gráficos de estatísticas
3. Logs de auditoria
4. Testes E2E

## Resumo

| Aspecto | Implementação |
|---------|---------------|
| Mapa / | Público (SEM auth) |
| Painel /painel | Autenticado (COM roles) |
| API GET /api/layers | Público |
| API POST/PUT/DELETE | Autenticado + Permissões |
| Roles | Admin, Operator, Viewer |
| 2FA | Obrigatório para Admin |
| Dashboard | Bentogrid |
| Frontend Separado | NÃO (mapa é público) |
