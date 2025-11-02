# Product Requirements Document (PRD)

## 1. Visão Geral do Produto

Sistema de gerenciamento e visualização de camadas geoespaciais conforme especificação do teste técnico ACTO.

Descrição da Tarefa Original:
> Criar uma aplicação web com foco em gestão de dados georreferenciados e exibição em mapa.

### Duas Partes Principais

1. Painel Administrativo (/painel): Área protegida com senha para CRUD de camadas geográficas
2. Mapa na Página Inicial (/): Visualização pública de todas as camadas cadastradas usando ArcGIS Maps SDK v4

## 2. Objetivos do Produto

- Fornecer painel administrativo simples para gerenciar camadas de mapas via upload de GeoJSON
- Exibir mapa interativo com todas as camadas cadastradas
- Armazenar geometrias com PostGIS de forma indexada
- Interface responsiva e funcional

## 3. Requisitos Funcionais

### 3.1 Parte 1: Painel Administrativo (/painel)

#### Segurança
- FR-001: Painel deve ser protegido com autenticação (usuário e senha)
- FR-002: Apenas usuários autenticados podem acessar /painel

#### CRUD de Camadas
- FR-003: Admin deve poder criar novas camadas via upload de arquivo GeoJSON
- FR-004: Admin deve poder visualizar lista de todas as camadas cadastradas
- FR-005: Admin deve poder editar informações das camadas existentes
- FR-006: Admin deve poder excluir camadas

#### Estrutura de Dados (Tabela layers)
- FR-007: Tabela deve ter campo id (incremental, PK)
- FR-008: Tabela deve ter campo name (string, máximo 100 caracteres)
- FR-009: Tabela deve ter campo geometry (PostGIS geometry)
- FR-010: Campo geometry deve ser indexado (GIST index)
- FR-011: Campo geometry deve aceitar upload de arquivo GeoJSON válido

#### Upload de GeoJSON
- FR-012: Sistema deve aceitar upload de arquivos .geojson
- FR-013: Sistema deve validar estrutura do GeoJSON antes de salvar
- FR-014: Sistema deve extrair geometria do GeoJSON e armazenar no campo geometry
- FR-015: Sistema deve converter coordenadas para SRID 4326 (WGS84) se necessário

### 3.2 Parte 2: Mapa na Página Inicial (/)

#### Visualização do Mapa
- FR-016: Página inicial (/) deve exibir mapa interativo usando ArcGIS Maps SDK v4
- FR-017: Mapa deve ser público (não requer autenticação)
- FR-018: Mapa deve mostrar todas as camadas cadastradas no painel
- FR-019: Camadas devem ser carregadas dinamicamente do banco de dados via API

#### Interatividade
- FR-020: Usuário deve poder fazer zoom in/out no mapa
- FR-021: Usuário deve poder navegar (pan) pelo mapa
- FR-022: Camadas devem ser renderizadas com cores distintas
- FR-023: Toggle para ativar/desativar camadas individualmente

### 3.3 Melhorias: Segurança Avançada (Além da Tarefa)

#### Detecção de Anomalias Geográficas
- FR-024: Sistema deve coletar IP e localização geográfica de cada tentativa de login
- FR-025: Sistema deve usar API gratuita de GeoIP para obter lat/long
- FR-026: Sistema deve calcular distância entre tentativas usando fórmula de Haversine
- FR-027: Se 3+ tentativas em locais geograficamente impossíveis, executar proteções
- FR-028: Logs devem exibir anomalias com formato destacado

#### Criptografia RSA 4096 E2E
- FR-029: Dados sensíveis devem ser criptografados com RSA 4096
- FR-030: Certificado CA único deve estar no .env
- FR-031: Servidor deve descriptografar dados com chave privada
- FR-032: Logs devem mostrar operações de criptografia/descriptografia

#### Tabelas Intermediárias Criptografadas
- FR-033: Dados sensíveis (CPF, telefone, endereço) devem estar em tabelas separadas
- FR-034: Dados devem ser criptografados com AES-256-GCM
- FR-035: Accessors/Mutators devem gerenciar criptografia transparentemente

### 3.4 Melhorias: Autenticação e Roles

#### Roles
- FR-036: Sistema deve implementar 3 roles: Admin, Operator, Viewer
- FR-037: Admin: Acesso total ao painel (CRUD de camadas e usuários)
- FR-038: Operator: Acesso ao painel para CRUD de camadas (sem gerenciar usuários)
- FR-039: Viewer: Apenas leitura no painel (visualizar lista de camadas)

#### 2FA (Two-Factor Authentication)
- FR-040: Sistema deve suportar 2FA usando TOTP
- FR-041: 2FA deve ser obrigatório para role Admin
- FR-042: 2FA deve ser opcional para Operator e Viewer
- FR-043: Sistema deve ser compatível com Google Authenticator / Authy
- FR-044: Token 2FA deve ser exibido no log com destaque em estrutura retangular
- FR-045: Token 2FA deve ser enviado via WhatsApp usando Evolution API v2

### 3.5 Melhorias: Dashboard Bentogrid

#### Layout Bentogrid
- FR-046: Dashboard deve usar layout bentogrid (grade assimétrica moderna)
- FR-047: Widgets devem ter tamanhos variados (ex: 1x1, 2x1, 1x2, 2x2)
- FR-048: Layout deve ser responsivo

#### Widgets
- FR-049: Widget: Total de camadas cadastradas
- FR-050: Widget: Camadas adicionadas recentemente
- FR-051: Widget: Gráfico de distribuição de tipos de geometria
- FR-052: Widget: Atividade recente (logs de ações)

### 3.6 Melhorias: API REST com Permissões

#### Endpoints
- FR-053: GET /api/layers - Listar camadas (público)
- FR-054: GET /api/layers/{id} - Detalhe de camada
- FR-055: POST /api/layers - Criar camada (apenas Admin/Operator)
- FR-056: PUT /api/layers/{id} - Atualizar camada (apenas Admin/Operator)
- FR-057: DELETE /api/layers/{id} - Deletar camada (apenas Admin)

#### Segurança da API
- FR-058: API deve usar Laravel Sanctum para autenticação
- FR-059: Rate limiting: 60 req/min para público, 100 req/min para autenticados
- FR-060: Respostas em formato JSON (GeoJSON para geometrias)

## 4. Requisitos Não Funcionais

### 4.1 Performance
- NFR-001: Tempo de resposta da API < 500ms para até 100 camadas
- NFR-002: Mapa deve renderizar em menos de 2 segundos

### 4.2 Segurança
- NFR-003: Senhas devem usar hash Argon2ID
- NFR-004: Rotas do painel /painel devem exigir autenticação
- NFR-005: API REST (exceto GET /api/layers) deve exigir autenticação
- NFR-006: Implementar rate limiting em endpoints públicos
- NFR-007: 2FA obrigatório para Admins usando TOTP

### 4.3 Escalabilidade
- NFR-008: Sistema deve suportar até 1000 camadas simultâneas
- NFR-009: Banco de dados deve ter índices otimizados para queries geoespaciais

### 4.4 Manutenibilidade
- NFR-010: Código deve seguir PSR-12 e princípios SOLID
- NFR-011: Cobertura de testes > 80%

## 5. Personas

### 5.1 Usuário Público (Tarefa Original)
- Acessa / sem autenticação
- Visualiza mapa com todas as camadas
- Pode interagir com o mapa (zoom, pan, toggle)

### 5.2 Administrador (Melhoria)
- Acessa /painel com autenticação + 2FA
- CRUD completo de camadas e usuários
- Visualiza dashboard com estatísticas
- Gerencia roles e permissões

### 5.3 Operador (Melhoria)
- Acessa /painel com autenticação
- CRUD de camadas (sem gerenciar usuários)
- Visualiza dashboard
- 2FA opcional

### 5.4 Visualizador (Melhoria)
- Acessa /painel com autenticação
- Apenas leitura (lista de camadas)
- Sem permissão para criar/editar/deletar
- 2FA opcional

## 6. Critérios de Sucesso

### 6.1 Requisitos Mínimos (Tarefa Original)
- Mapa público em / exibindo camadas com ArcGIS SDK v4
- Painel admin em /painel com CRUD de camadas
- Upload e processamento de GeoJSON
- PostgreSQL + PostGIS com geometrias indexadas
- Documentação de setup

### 6.2 Melhorias Implementadas
- Roles (Admin/Operator/Viewer) com controle granular
- 2FA obrigatório para Admins
- Dashboard com layout bentogrid
- API REST com autenticação e permissões
- Filament v4 para painel moderno
- Testes automatizados

## 7. Fora de Escopo

### 7.1 Não Está na Tarefa Original
- Análise espacial avançada (buffer, intersect, union)
- Importação em lote de múltiplos arquivos
- Exportação de mapas em PDF
- Aplicativo mobile
- Versionamento de camadas
- Edição de geometrias no mapa
- Histórico de alterações detalhado

### 7.2 Deliberadamente NÃO Implementado
- Autenticação para visualização do mapa público (tarefa pede público em /)
- Área separada /map com autenticação (mapa é público na raiz)
