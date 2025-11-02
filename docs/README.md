# Documentação ACTO Maps

## Visualização

### Mintlify (Recomendado)

Documentação interativa com busca, navegação e syntax highlighting.

```powershell
# Iniciar (incluído no podman-compose up)
podman-compose up -d mintlify

# Acessar
# http://localhost:3000
```

### Markdown (GitHub)

Arquivos `.md` podem ser visualizados diretamente no GitHub ou em qualquer editor Markdown.

## Estrutura

```
docs/
├── mint.json                    # Configuração Mintlify
├── README.md                    # Este arquivo
│
├── ESCOPO.md                    # Escopo do projeto
├── BRD.md                       # Business Requirements
├── PRD.md                       # Product Requirements
│
├── technical/                   # Documentação técnica
│   ├── TRD.md                  # Technical Requirements
│   ├── Infradoc.md             # Infraestrutura
│   ├── Seguranca.md            # Segurança
│   ├── Environment-Variables.md # Variáveis de ambiente
│   └── Evolution-API.md        # WhatsApp 2FA
│
├── api/                         # API Reference
│   ├── overview.mdx            # Visão geral da API
│   ├── authentication.mdx      # Autenticação
│   └── layers.mdx              # Endpoints de layers
│
├── database.mdx                 # Schema do banco
└── database.dbml                # Diagrama DBML

```

## Navegação Mintlify

### Abas Principais

1. **Início**: Escopo e requisitos de negócio
2. **Documentação Técnica**: TRD, infraestrutura, segurança
3. **API Reference**: Endpoints e autenticação
4. **Banco de Dados**: Schema e queries

### Busca

Use `Ctrl+K` (ou `Cmd+K` no Mac) para abrir a busca rápida.

### Dark Mode

Alterne entre light/dark mode no canto superior direito.

## Edição

### Arquivos Markdown

Edite os arquivos `.md` diretamente:

```powershell
# Mintlify detecta mudanças automaticamente (hot reload)
code docs/technical/TRD.md
```

### Configuração Mintlify

Edite `mint.json` para:
- Adicionar/remover páginas
- Alterar navegação
- Customizar cores e logo
- Configurar integrations

Exemplo:

```json
{
  "navigation": [
    {
      "group": "Novo Grupo",
      "pages": [
        "nova-pagina"
      ]
    }
  ]
}
```

## Comandos

### Iniciar Mintlify

```powershell
podman-compose up -d mintlify
```

### Ver Logs

```powershell
podman logs -f acto-mintlify
```

### Reiniciar

```powershell
podman-compose restart mintlify
```

### Parar

```powershell
podman-compose stop mintlify
```

## Troubleshooting

### Porta 3000 já em uso

```powershell
# Ver processo usando a porta
netstat -ano | Select-String ":3000"

# Mudar porta no podman-compose.yml
ports:
  - "3001:3000"  # Usar porta 3001 no host
```

### Mintlify não carrega

```powershell
# Ver logs
podman logs acto-mintlify

# Reconstruir container
podman-compose down mintlify
podman-compose up -d mintlify
```

### Mudanças não aparecem

Mintlify tem hot reload, mas às vezes precisa de restart:

```powershell
podman-compose restart mintlify
```

## Referências

- Mintlify Documentation: https://mintlify.com/docs
- MDX: https://mdxjs.com/
- Mermaid Diagrams: https://mermaid.js.org/

