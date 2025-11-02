# Scripts Utilitários

## Sumário

1. [generate-certificates.ps1](#generate-certificatesps1) - Gerar certificados CA e chaves RSA
2. [test.ps1](#testps1) - Executar testes
3. [setup-test-db.ps1](#setup-test-dbps1) - Configurar banco de testes
4. [coverage-report.ps1](#coverage-reportps1) - Gerar relatório de coverage

## generate-certificates.ps1

Script PowerShell para gerar certificados CA e chaves RSA 4096 para o projeto ACTO Maps.

### Requisitos

- Windows 10/11
- OpenSSL instalado
  - Download: https://slproweb.com/products/Win32OpenSSL.html
  - Ou via Chocolatey: `choco install openssl`

### Uso

Executar no diretório raiz do projeto:

```powershell
.\scripts\generate-certificates.ps1
```

Opções:

```powershell
# Especificar diretório de saída
.\scripts\generate-certificates.ps1 -OutputDir "meus-certificados"

# Forçar sobrescrita sem confirmação
.\scripts\generate-certificates.ps1 -Force
```

### O que é gerado

1. Certificado CA (Certificate Authority):
   - `ca-cert.pem`: Certificado público do CA
   - `ca-key.pem`: Chave privada do CA

2. Par de chaves RSA 4096:
   - `rsa-public.pem`: Chave pública RSA
   - `rsa-private.pem`: Chave privada RSA

3. Snippet para .env:
   - `env-snippet.txt`: Arquivo com variáveis formatadas para copiar

### Estrutura de diretórios

```
certificates/
├── ca-cert.pem          # Certificado CA público
├── ca-key.pem           # Chave privada CA (SENSÍVEL)
├── rsa-public.pem       # Chave pública RSA
├── rsa-private.pem      # Chave privada RSA (SENSÍVEL)
└── env-snippet.txt      # Snippet para .env (SENSÍVEL)
```

### Adicionar ao .env

Opção 1 - Copiar manualmente:
```powershell
Get-Content certificates/env-snippet.txt
# Copiar e colar no .env
```

Opção 2 - Adicionar automaticamente:
```powershell
Get-Content certificates/env-snippet.txt | Add-Content .env
```

### Segurança

IMPORTANTE: Os arquivos gerados contêm chaves privadas sensíveis!

- Mantenha o diretório `certificates/` seguro
- NÃO commite estes arquivos no Git
- Faça backup criptografado das chaves
- Em produção, use vault ou secrets manager

O arquivo `.gitignore` já está configurado para ignorar:
- `/certificates`
- `*.pem`
- `*.key`
- `env-snippet.txt`

### Validar certificados

Verificar certificado CA:
```powershell
openssl x509 -in certificates/ca-cert.pem -text -noout
```

Verificar par de chaves RSA:
```powershell
# Verificar chave privada
openssl rsa -in certificates/rsa-private.pem -check

# Verificar se chave pública corresponde à privada
$private = openssl rsa -in certificates/rsa-private.pem -pubout
$public = Get-Content certificates/rsa-public.pem -Raw
if ($private -eq $public) {
    Write-Host "Chaves RSA são um par válido" -ForegroundColor Green
}
```

### Troubleshooting

Erro: OpenSSL não encontrado
```
Solução: Instale o OpenSSL e adicione ao PATH
```

Erro: Permissão negada
```
Solução: Execute PowerShell como Administrador
```

Erro: Diretório já existe
```
Solução: Use -Force ou delete o diretório manualmente
```

### Renovar certificados

Certificados CA são válidos por 365 dias. Para renovar:

```powershell
# Remover certificados antigos
Remove-Item certificates -Recurse -Force

# Gerar novos
.\scripts\generate-certificates.ps1

# Atualizar .env com novos certificados
```

### Em produção

Recomendações para ambientes de produção:

1. Use um CA confiável (Let's Encrypt, DigiCert, etc)
2. Armazene chaves em vault (Azure Key Vault, AWS Secrets Manager)
3. Configure rotação automática de certificados
4. Use HSM (Hardware Security Module) para chaves críticas
5. Implemente monitoramento de expiração

### Referências

- OpenSSL Documentation: https://docs.openssl.org/
- Certificate Management Best Practices: https://www.ssl.com/guide/
- RSA Encryption: https://en.wikipedia.org/wiki/RSA_(cryptosystem)

## test.ps1

Script para executar testes com diferentes configurações.

### Uso Básico

```powershell
# Todos os testes
.\scripts\test.ps1

# Testes unitários
.\scripts\test.ps1 -Type unit

# Testes de feature
.\scripts\test.ps1 -Type feature

# Testes de integração
.\scripts\test.ps1 -Type integration
```

### Opções

```powershell
# Com coverage
.\scripts\test.ps1 -Coverage

# Execução paralela
.\scripts\test.ps1 -Parallel

# Filtrar por nome
.\scripts\test.ps1 -Filter "layer"

# Profiling (identificar testes lentos)
.\scripts\test.ps1 -Profile

# Watch mode (rerun ao modificar arquivos)
.\scripts\test.ps1 -Watch

# Combinações
.\scripts\test.ps1 -Type unit -Coverage -Parallel
```

### Parâmetros

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| Type | string | all | Tipo de teste (all, unit, feature, integration) |
| Filter | string | "" | Filtrar testes por nome |
| Coverage | switch | false | Gerar relatório de coverage |
| Parallel | switch | false | Executar testes em paralelo |
| Profile | switch | false | Identificar testes lentos |
| Watch | switch | false | Watch mode |

## setup-test-db.ps1

Script para configurar o banco de dados de testes.

### O que faz

1. Verifica se PostgreSQL está rodando
2. Cria banco de dados `laravel_test`
3. Instala extensões PostGIS
4. Cria arquivo `.env.testing`
5. Gera APP_KEY para testing

### Uso

```powershell
.\scripts\setup-test-db.ps1
```

### Pré-requisitos

- PostgreSQL rodando em container Podman
- Container nome: `acto-postgres`

### Troubleshooting

Erro: PostgreSQL não está rodando
```
Solução: podman-compose up -d postgres
```

Erro: Permissão negada
```
Solução: Execute PowerShell como Administrador
```

## coverage-report.ps1

Script para gerar relatório HTML de code coverage.

### Uso

```powershell
# Gerar relatório
.\scripts\coverage-report.ps1

# Gerar e abrir no navegador
.\scripts\coverage-report.ps1 -Open
```

### Saída

O relatório é gerado em `coverage-report/index.html`

### Requisitos

Para coverage detalhado, instale Xdebug:

```powershell
# Via PECL
pecl install xdebug

# Ou baixe DLL para Windows
# https://xdebug.org/download
```

Configurar no php.ini:

```ini
[xdebug]
zend_extension=xdebug
xdebug.mode=coverage
```

### Métricas

O relatório mostra:
- Linhas cobertas / total
- Funções cobertas / total
- Classes cobertas / total
- Coverage por arquivo
- Coverage por diretório

## Exemplos de Uso

### Fluxo TDD

```powershell
# 1. Configurar banco de testes (primeira vez)
.\scripts\setup-test-db.ps1

# 2. Executar testes unitários rapidamente
.\scripts\test.ps1 -Type unit

# 3. Watch mode durante desenvolvimento
.\scripts\test.ps1 -Type unit -Watch

# 4. Antes de commit - todos os testes com coverage
.\scripts\test.ps1 -Coverage -Parallel

# 5. Gerar relatório HTML detalhado
.\scripts\coverage-report.ps1 -Open
```

### CI/CD Local

```powershell
# Simular pipeline CI/CD
.\scripts\setup-test-db.ps1
php artisan migrate --env=testing
.\scripts\test.ps1 -Coverage -Parallel
.\scripts\coverage-report.ps1
```

### Debug de Testes

```powershell
# Executar teste específico
.\scripts\test.ps1 -Filter "layer creation"

# Identificar testes lentos
.\scripts\test.ps1 -Profile

# Executar teste isolado
php artisan test tests/Feature/LayerTest.php
```

## Notas

- Scripts otimizados para Windows + PowerShell
- Todos os scripts assumem execução no diretório raiz do projeto
- Use `Get-Help .\scripts\test.ps1 -Detailed` para ajuda detalhada
- Logs coloridos para melhor visualização

