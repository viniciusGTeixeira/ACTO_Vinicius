# Business Requirements Document (BRD)

## 1. Informações do Projeto

| Campo | Valor |
|-------|-------|
| Nome do Projeto | ACTO Maps - Sistema de Gerenciamento de Camadas Geoespaciais |
| Versão | 1.0.0 |
| Data | 2025-11-02 |
| Status | Em Desenvolvimento |
| Stakeholders | Equipe Técnica ACTO |

## 2. Objetivo do Negócio

Desenvolver aplicação web para gerenciar e visualizar dados georreferenciados em mapas interativos, permitindo que administradores cadastrem camadas geográficas e usuários públicos visualizem essas informações.

## 3. Escopo do Projeto

### 3.1 No Escopo

#### Tarefa Original
- Painel administrativo protegido por senha em /painel
- CRUD completo de camadas geográficas
- Upload de arquivos GeoJSON
- Armazenamento indexado de geometrias no PostGIS
- Mapa público interativo na raiz / usando ArcGIS Maps SDK v4
- Exibição de todas as camadas cadastradas no mapa

#### Melhorias Adicionais
- Sistema de roles (Admin, Operator, Viewer)
- Autenticação com 2FA obrigatório para Admins
- API REST com autenticação e permissões
- Dashboard com widgets estatísticos
- Notificações via WhatsApp (Evolution API)
- Detecção de anomalias geográficas
- Criptografia avançada (RSA 4096, Argon2ID)

### 3.2 Fora do Escopo

- Análise espacial avançada (buffer, intersection, union)
- Edição de geometrias diretamente no mapa
- Versionamento de camadas
- Aplicativo mobile
- Integração com sistemas externos (ERP, CRM)
- Importação em lote de múltiplos arquivos
- Exportação de relatórios em PDF

## 4. Requisitos de Negócio

### 4.1 Funcionalidades Principais

| ID | Requisito | Prioridade | Complexidade |
|----|-----------|------------|--------------|
| BR-001 | Painel administrativo com autenticação | Alta | Média |
| BR-002 | CRUD de camadas geográficas | Alta | Média |
| BR-003 | Upload e validação de arquivos GeoJSON | Alta | Alta |
| BR-004 | Armazenamento de geometrias com PostGIS | Alta | Alta |
| BR-005 | Mapa público interativo | Alta | Alta |
| BR-006 | Renderização de camadas no mapa | Alta | Média |
| BR-007 | Sistema de roles e permissões | Média | Média |
| BR-008 | Autenticação de dois fatores (2FA) | Média | Alta |
| BR-009 | API REST com autenticação | Baixa | Média |
| BR-010 | Dashboard com estatísticas | Baixa | Baixa |

### 4.2 Regras de Negócio

| ID | Regra | Descrição |
|----|-------|-----------|
| RN-001 | Validação de GeoJSON | Arquivo deve ser GeoJSON válido conforme RFC 7946 |
| RN-002 | Limite de tamanho | Arquivo GeoJSON máximo de 10 MB |
| RN-003 | Camada requer nome | Nome obrigatório com máximo de 255 caracteres |
| RN-004 | Geometria indexada | Geometrias devem ter índice GIST para performance |
| RN-005 | 2FA obrigatório para Admin | Role Admin deve ter 2FA ativo |
| RN-006 | Soft delete de camadas | Exclusão lógica para manter histórico |
| RN-007 | Audit log | Todas as ações críticas devem ser registradas |
| RN-008 | Rate limiting | API pública limitada a 60 req/min por IP |
| RN-009 | Validação de tipos | Geometria deve ser point, linestring ou polygon |
| RN-010 | Permissões granulares | Operator não pode gerenciar usuários |

## 5. Personas e Casos de Uso

### 5.1 Persona: Administrador

Características:
- Responsável técnico pelo sistema
- Conhecimento avançado em GIS
- Necessita controle total

Necessidades:
- Gerenciar usuários e permissões
- Cadastrar e editar camadas
- Visualizar logs de auditoria
- Configurar sistema

Casos de Uso:
- UC-001: Login com email, senha e 2FA
- UC-002: Criar novo usuário com role específica
- UC-003: Fazer upload de arquivo GeoJSON
- UC-004: Editar informações de camada existente
- UC-005: Excluir camada
- UC-006: Visualizar dashboard com estatísticas

### 5.2 Persona: Operador

Características:
- Usuário técnico operacional
- Conhecimento intermediário em GIS
- Foco em cadastro de dados

Necessidades:
- Cadastrar novas camadas
- Editar camadas existentes
- Visualizar mapa

Casos de Uso:
- UC-007: Login com email e senha
- UC-008: Fazer upload de múltiplas camadas
- UC-009: Atualizar metadados de camada
- UC-010: Desativar camada temporariamente

### 5.3 Persona: Visualizador

Características:
- Usuário interno não técnico
- Necessita apenas consulta
- Sem permissão para edição

Necessidades:
- Visualizar lista de camadas
- Acessar informações das camadas
- Baixar dados públicos

Casos de Uso:
- UC-011: Login com email e senha
- UC-012: Listar todas as camadas ativas
- UC-013: Visualizar detalhes de camada específica
- UC-014: Exportar dados em formato GeoJSON

### 5.4 Persona: Usuário Público

Características:
- Usuário externo sem cadastro
- Acesso apenas ao mapa público
- Interesse em visualizar informações geográficas

Necessidades:
- Visualizar mapa interativo
- Navegar pelo mapa (zoom, pan)
- Ativar/desativar camadas
- Ver informações das camadas

Casos de Uso:
- UC-015: Acessar mapa público sem autenticação
- UC-016: Fazer zoom in/out no mapa
- UC-017: Clicar em feature para ver atributos
- UC-018: Toggle de visibilidade de camadas

## 6. Métricas de Sucesso

### 6.1 KPIs Técnicos

| Métrica | Meta | Medição |
|---------|------|---------|
| Tempo de resposta API | < 500ms | Prometheus/New Relic |
| Uptime do sistema | > 99.5% | Monitoramento 24/7 |
| Taxa de erro | < 1% | Logs centralizados |
| Tempo de renderização do mapa | < 2s | Google Lighthouse |
| Cobertura de testes | > 80% | PHPUnit/Pest |

### 6.2 KPIs de Negócio

| Métrica | Meta | Medição |
|---------|------|---------|
| Camadas cadastradas | 100+ no primeiro mês | Dashboard |
| Usuários ativos | 10+ usuários admin/operator | Analytics |
| Visualizações do mapa público | 500+ visitas/mês | Google Analytics |
| Taxa de falha em upload | < 5% | Logs de erros |
| Tempo médio de cadastro | < 3 minutos | Análise de fluxo |

## 7. Impacto nos Stakeholders

| Stakeholder | Impacto | Nível | Ações Necessárias |
|-------------|---------|-------|-------------------|
| Equipe Técnica | Ferramenta para gerenciar dados GIS | Alto | Treinamento em funcionalidades |
| Administradores | Controle total do sistema | Alto | Documentação completa + suporte |
| Operadores | Cadastro eficiente de camadas | Médio | Manual de operação + FAQ |
| Usuários Públicos | Acesso fácil a informações geográficas | Médio | Interface intuitiva |
| Gestão | Visibilidade de métricas | Baixo | Dashboard executivo |

## 8. Riscos e Mitigações

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|---------------|---------|-----------|
| R-001 | Performance lenta com muitas camadas | Média | Alto | Implementar cache, índices e paginação |
| R-002 | Upload de arquivo malicioso | Baixa | Alto | Validação rigorosa, antivírus scan |
| R-003 | Falha na integração com PostGIS | Baixa | Alto | Testes automatizados, backup de dados |
| R-004 | Problemas com ArcGIS SDK | Média | Médio | Documentação oficial, fallback para Leaflet |
| R-005 | Brecha de segurança | Baixa | Alto | Auditorias, testes de penetração |
| R-006 | Indisponibilidade de Evolution API | Média | Baixo | Fallback para email, queue com retry |
| R-007 | Crescimento excessivo do banco | Média | Médio | Políticas de retenção, arquivamento |
| R-008 | Falha no 2FA | Baixa | Médio | Recovery codes, suporte via admin |

## 9. Dependências Externas

| Dependência | Tipo | Criticidade | Plano B |
|-------------|------|-------------|---------|
| ArcGIS Maps SDK v4 | JavaScript Library | Alta | Leaflet ou MapLibre GL |
| Evolution API v2 | WhatsApp Gateway | Média | Email ou SMS via Twilio |
| PostgreSQL + PostGIS | Database | Alta | Não há alternativa viável |
| MinIO | Object Storage | Média | AWS S3 ou local filesystem |
| Filament v4 | Admin Panel | Alta | Desenvolvimento manual |
| Laravel 12 | Framework | Alta | Não há alternativa viável |

## 10. Cronograma (Estimado)

| Fase | Descrição | Duração | Entregáveis |
|------|-----------|---------|-------------|
| Fase 1 | Setup e infraestrutura | 3 dias | Ambiente configurado, banco criado |
| Fase 2 | Autenticação e roles | 5 dias | Login, 2FA, permissões |
| Fase 3 | CRUD de camadas | 7 dias | Upload GeoJSON, validação, CRUD |
| Fase 4 | Mapa público | 5 dias | Integração ArcGIS, renderização |
| Fase 5 | API REST | 3 dias | Endpoints autenticados |
| Fase 6 | Dashboard e widgets | 4 dias | Estatísticas, gráficos |
| Fase 7 | Segurança avançada | 5 dias | GeoIP, RSA, Argon2ID |
| Fase 8 | Testes e ajustes | 5 dias | Testes automatizados, fixes |
| Fase 9 | Documentação | 3 dias | Docs técnicos e usuário |
| Fase 10 | Deploy | 2 dias | Produção, monitoramento |

Total Estimado: 42 dias úteis (aproximadamente 8-9 semanas)

## 11. Requisitos de Infraestrutura

### 11.1 Desenvolvimento

| Recurso | Especificação |
|---------|---------------|
| CPU | 4 cores |
| RAM | 8 GB |
| Disco | 50 GB SSD |
| SO | Windows 10/11 |
| Containers | Podman (PostgreSQL, MinIO) |
| PHP | 8.2+ com extensões |

### 11.2 Produção

| Recurso | Especificação |
|---------|---------------|
| Aplicação | 2 vCPU, 4 GB RAM |
| Database | 4 vCPU, 8 GB RAM, 100 GB SSD |
| Storage | MinIO cluster ou S3 |
| Bandwidth | 100 Mbps |
| Backup | Diário com retenção de 30 dias |

## 12. Conformidade e Regulamentação

| Requisito | Status | Observações |
|-----------|--------|-------------|
| LGPD | Aplicável | Dados pessoais de usuários |
| Backups | Obrigatório | Retenção mínima de 30 dias |
| Logs de Auditoria | Obrigatório | Retenção de 6 meses |
| Criptografia de Dados | Obrigatório | Em trânsito (HTTPS) e em repouso |
| Controle de Acesso | Obrigatório | Role-based access control |

## 13. Critérios de Aceitação

### 13.1 Funcionalidades Mínimas (MVP)

- [ ] Painel admin acessível em /painel
- [ ] Login com email e senha
- [ ] CRUD completo de camadas
- [ ] Upload de arquivo GeoJSON com validação
- [ ] Geometrias armazenadas no PostGIS com índice GIST
- [ ] Mapa público em / renderizando camadas
- [ ] Interação no mapa (zoom, pan)
- [ ] Documentação de instalação

### 13.2 Melhorias Implementadas

- [ ] Sistema de roles (Admin, Operator, Viewer)
- [ ] 2FA obrigatório para Admins
- [ ] API REST com autenticação
- [ ] Dashboard com widgets
- [ ] Notificações via WhatsApp
- [ ] Detecção de anomalias geográficas
- [ ] Criptografia RSA 4096

## 14. Aprovações

| Papel | Nome | Assinatura | Data |
|-------|------|------------|------|
| Product Owner | - | - | - |
| Tech Lead | - | - | - |
| Stakeholder | - | - | - |

## 15. Histórico de Revisões

| Versão | Data | Autor | Alterações |
|--------|------|-------|------------|
| 1.0.0 | 2025-11-02 | Equipe Técnica | Versão inicial |
