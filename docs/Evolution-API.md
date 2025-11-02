# Integração com Evolution API v2 - WhatsApp

## Visão Geral

A Evolution API v2 é utilizada para enviar notificações via WhatsApp, especialmente códigos 2FA para Admins.

Documentação Oficial: https://doc.evolution-api.com/v2/pt/integrations/cloudapi

## Casos de Uso

### 1. Envio de Código 2FA

Quando um Admin faz login:
1. Sistema gera código TOTP (6 dígitos)
2. Exibe código no log com destaque visual
3. Envia código via WhatsApp para número cadastrado

### 2. Formato do Log

```
=====================================
    CÓDIGO 2FA GERADO
=====================================
    
    Usuário: admin@example.com
    Código:  123456
    Válido:  30 segundos
    
=====================================
```

## Configuração

### 1. Variáveis de Ambiente (.env)

```env
# Evolution API v2
EVOLUTION_API_URL=https://sua-evolution-api.com
EVOLUTION_API_KEY=sua_api_key_aqui
EVOLUTION_INSTANCE_NAME=acto_maps

# WhatsApp Business (se usar Cloud API)
WHATSAPP_BUSINESS_TOKEN=seu_token_permanente
WHATSAPP_NUMBER_ID=seu_number_id
WHATSAPP_BUSINESS_ID=seu_business_id

# 2FA WhatsApp Settings
TWOFACTOR_WHATSAPP_ENABLED=true
TWOFACTOR_WHATSAPP_TEMPLATE="ACTO Maps - Código 2FA\n\nSeu código de autenticação:\n{{code}}\n\nVálido por 30 segundos.\n\nNão compartilhe este código!"
```

### 2. Estrutura de Configuração

```php
// config/evolution.php
<?php

return [
    'api_url' => env('EVOLUTION_API_URL'),
    'api_key' => env('EVOLUTION_API_KEY'),
    'instance_name' => env('EVOLUTION_INSTANCE_NAME', 'default'),
    
    'whatsapp' => [
        'token' => env('WHATSAPP_BUSINESS_TOKEN'),
        'number_id' => env('WHATSAPP_NUMBER_ID'),
        'business_id' => env('WHATSAPP_BUSINESS_ID'),
    ],
    
    'two_factor' => [
        'enabled' => env('TWOFACTOR_WHATSAPP_ENABLED', false),
        'template' => env('TWOFACTOR_WHATSAPP_TEMPLATE'),
    ],
];
```

## Instalação

### 1. Instalar Dependência HTTP

```powershell
composer require guzzlehttp/guzzle
```

### 2. Criar Service Provider

```powershell
php artisan make:provider EvolutionServiceProvider
```

## Implementação

### Service de Comunicação

```php
// app/Services/EvolutionApiService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instanceName;
    
    public function __construct()
    {
        $this->apiUrl = config('evolution.api_url');
        $this->apiKey = config('evolution.api_key');
        $this->instanceName = config('evolution.instance_name');
    }
    
    /**
     * Envia mensagem de texto via WhatsApp
     */
    public function sendTextMessage(string $phoneNumber, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/message/sendText/{$this->instanceName}", [
                'number' => $phoneNumber,
                'text' => $message,
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully');
                return ['success' => true, 'data' => $response->json()];
            }
            
            Log::error('Failed to send WhatsApp message', [
                'status' => $response->status(),
            ]);
            
            return ['success' => false, 'error' => $response->body()];
            
        } catch (\Exception $e) {
            Log::error('Exception sending WhatsApp message', [
                'error' => $e->getMessage(),
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Envia código 2FA via WhatsApp com formatação especial no log
     */
    public function send2FACode(string $phoneNumber, string $code, string $userEmail): array
    {
        $this->logTwoFactorCode($code, $userEmail);
        
        $message = str_replace('{{code}}', $code, config('evolution.two_factor.template'));
        
        return $this->sendTextMessage($phoneNumber, $message);
    }
    
    /**
     * Exibe código 2FA no log com estrutura retangular destacada
     */
    protected function logTwoFactorCode(string $code, string $userEmail): void
    {
        $separator = str_repeat('=', 50);
        $title = '    CÓDIGO 2FA GERADO';
        $userLine = "    Usuário: {$userEmail}";
        $codeLine = "    Código:  {$code}";
        $validLine = "    Válido:  30 segundos";
        $timestamp = "    Gerado:  " . now()->format('d/m/Y H:i:s');
        
        $logMessage = "\n" . $separator . "\n"
                    . $title . "\n"
                    . $separator . "\n"
                    . "\n"
                    . $userLine . "\n"
                    . $codeLine . "\n"
                    . $validLine . "\n"
                    . $timestamp . "\n"
                    . "\n"
                    . $separator . "\n";
        
        Log::info($logMessage);
    }
    
    /**
     * Verifica se a instância está conectada
     */
    public function checkConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->get("{$this->apiUrl}/instance/connectionState/{$this->instanceName}");
            
            return $response->successful() && 
                   $response->json('state') === 'open';
                   
        } catch (\Exception $e) {
            Log::error('Error checking Evolution API connection');
            return false;
        }
    }
}
```

## Pré-requisitos na Evolution API

### Criar Instância

Se estiver usando WhatsApp Business Cloud API:

```bash
curl -X POST https://sua-evolution-api.com/instance/create \
-H "apikey: sua_api_key" \
-H "Content-Type: application/json" \
-d '{
    "instanceName": "acto_maps",
    "token": "SEU_TOKEN_PERMANENTE_BM",
    "number": "SEU_NUMBER_ID",
    "businessId": "SEU_BUSINESS_ID",
    "qrcode": false,
    "integration": "WHATSAPP-BUSINESS"
}'
```

### Configurar Webhook (Opcional)

No Facebook Developers:
- URL: https://sua-evolution-api.com/webhook/meta
- Token: valor de WA_BUSINESS_TOKEN_WEBHOOK

## Exemplo de Uso

### Command de Teste

```powershell
php artisan make:command TestWhatsApp2FA
```

```php
// app/Console/Commands/TestWhatsApp2FA.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EvolutionApiService;

class TestWhatsApp2FA extends Command
{
    protected $signature = 'test:whatsapp-2fa {phone} {--code=123456}';
    protected $description = 'Test WhatsApp 2FA integration';
    
    public function handle(EvolutionApiService $evolutionApi)
    {
        $phone = $this->argument('phone');
        $code = $this->option('code');
        
        $this->info('Sending 2FA code via WhatsApp...');
        
        $result = $evolutionApi->send2FACode($phone, $code, 'test@example.com');
        
        if ($result['success']) {
            $this->info('Message sent successfully!');
        } else {
            $this->error('Failed: ' . ($result['error'] ?? 'Unknown'));
        }
    }
}
```

### Executar Teste

```powershell
php artisan test:whatsapp-2fa 5511999999999
```

## Monitoramento

### Verificar Conexão

```php
$evolutionApi = app(EvolutionApiService::class);

if ($evolutionApi->checkConnection()) {
    echo "Evolution API conectada!";
} else {
    echo "Evolution API desconectada!";
}
```

### Logs

Todos os envios são registrados em storage/logs/laravel.log

## Segurança

### Boas Práticas

1. Nunca commitar .env com tokens reais
2. Mascarar números de telefone nos logs
3. Rate limiting para envio de códigos (máximo 3 por minuto)
4. Validar número de telefone antes de enviar
5. Logs devem ser auditados regularmente

## Testes

### Unit Test

```php
// tests/Unit/EvolutionApiServiceTest.php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EvolutionApiService;
use Illuminate\Support\Facades\Http;

class EvolutionApiServiceTest extends TestCase
{
    public function test_can_send_text_message()
    {
        Http::fake([
            '*/message/sendText/*' => Http::response(['success' => true], 200),
        ]);
        
        $service = new EvolutionApiService();
        $result = $service->sendTextMessage('5511999999999', 'Test message');
        
        $this->assertTrue($result['success']);
    }
}
```

## Referências

- Evolution API v2: https://doc.evolution-api.com/v2/pt
- WhatsApp Cloud API: https://doc.evolution-api.com/v2/pt/integrations/cloudapi
- Laravel Fortify: https://laravel.com/docs/fortify
- Guzzle HTTP: http://docs.guzzlephp.org/
