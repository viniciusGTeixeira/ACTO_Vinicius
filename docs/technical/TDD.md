# Test-Driven Development Document (TDD)

## 1. Visão Geral

Este documento define a estratégia de testes para o projeto ACTO Maps, seguindo práticas de TDD (Test-Driven Development).

## 2. Filosofia TDD

### 2.1 Ciclo Red-Green-Refactor

1. RED: Escrever teste que falha
2. GREEN: Escrever código mínimo para passar
3. REFACTOR: Melhorar código mantendo testes passando

### 2.2 Princípios

- Testes antes do código
- Testes como documentação viva
- Cobertura mínima de 80%
- Testes rápidos e isolados
- Mocks e stubs quando necessário

## 3. Stack de Testes

### 3.1 Ferramentas

| Ferramenta | Versão | Uso |
|------------|--------|-----|
| Pest PHP | ^2.0 | Framework de testes principal |
| PHPUnit | ^10.0 | Base para Pest |
| Laravel Dusk | ^7.0 | Testes E2E de browser |
| Mockery | ^1.6 | Mocking de dependências |
| Faker | ^1.23 | Geração de dados fake |
| Laravel Factories | Built-in | Model factories |

### 3.2 Instalação

```bash
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
composer require --dev laravel/dusk
composer require --dev mockery/mockery
```

Inicializar Pest:

```bash
php artisan pest:install
```

Inicializar Dusk:

```bash
php artisan dusk:install
```

## 4. Estrutura de Testes

```
tests/
├── Pest.php                     # Configuração Pest
├── TestCase.php                 # Base TestCase
│
├── Unit/                        # Testes unitários
│   ├── Models/
│   │   ├── LayerTest.php
│   │   └── UserTest.php
│   ├── Services/
│   │   ├── LayerServiceTest.php
│   │   └── GeoIPServiceTest.php
│   └── Helpers/
│       └── HaversineTest.php
│
├── Feature/                     # Testes de feature
│   ├── API/
│   │   ├── LayerApiTest.php
│   │   └── AuthenticationTest.php
│   ├── Admin/
│   │   ├── LayerCrudTest.php
│   │   └── UserManagementTest.php
│   └── Security/
│       ├── TwoFactorTest.php
│       └── AnomalyDetectionTest.php
│
├── Integration/                 # Testes de integração
│   ├── PostGIS/
│   │   └── GeometryTest.php
│   ├── MinIO/
│   │   └── StorageTest.php
│   └── EvolutionAPI/
│       └── WhatsAppTest.php
│
└── Browser/                     # Testes E2E (Dusk)
    ├── LoginTest.php
    ├── MapVisualizationTest.php
    └── LayerUploadTest.php
```

## 5. Tipos de Testes

### 5.1 Unit Tests (Testes Unitários)

Testam unidades isoladas de código (métodos, classes).

**Características:**
- Rápidos (< 100ms)
- Isolados (sem dependências externas)
- Sem database, HTTP, filesystem

**Exemplo: Model Layer**

```php
<?php

use App\Models\Layer;

test('layer has correct fillable attributes', function () {
    $layer = new Layer();
    
    expect($layer->getFillable())->toContain('name', 'description', 'type', 'geometry');
});

test('layer type must be valid', function () {
    $layer = Layer::factory()->make(['type' => 'invalid']);
    
    expect($layer->save())->toBeFalse();
});

test('layer name is required', function () {
    $layer = Layer::factory()->make(['name' => null]);
    
    expect($layer->save())->toBeFalse();
});

test('layer name cannot exceed 255 characters', function () {
    $layer = Layer::factory()->make(['name' => str_repeat('a', 256)]);
    
    expect($layer->save())->toBeFalse();
});
```

**Exemplo: Service Layer**

```php
<?php

use App\Services\LayerService;
use App\Repositories\LayerRepository;
use Mockery;

test('layer service creates layer with valid data', function () {
    $mockRepo = Mockery::mock(LayerRepository::class);
    $mockRepo->shouldReceive('create')
        ->once()
        ->with([
            'name' => 'Test Layer',
            'type' => 'polygon',
            'created_by' => 1
        ])
        ->andReturn((object)['id' => 1, 'name' => 'Test Layer']);
    
    $service = new LayerService($mockRepo);
    
    $result = $service->create([
        'name' => 'Test Layer',
        'type' => 'polygon',
        'created_by' => 1
    ]);
    
    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Test Layer');
});
```

**Exemplo: Helper Function**

```php
<?php

use function App\Helpers\haversineDistance;

test('haversine calculates distance correctly', function () {
    // Distância entre Brasília e São Paulo (aprox 872 km)
    $lat1 = -15.7801;
    $lon1 = -47.9292;
    $lat2 = -23.5505;
    $lon2 = -46.6333;
    
    $distance = haversineDistance($lat1, $lon1, $lat2, $lon2);
    
    expect($distance)->toBeGreaterThan(850);
    expect($distance)->toBeLessThan(900);
});

test('haversine returns zero for same location', function () {
    $distance = haversineDistance(-15.7801, -47.9292, -15.7801, -47.9292);
    
    expect($distance)->toBe(0.0);
});
```

### 5.2 Feature Tests (Testes de Feature)

Testam funcionalidades completas com banco de dados.

**Características:**
- Médios (< 500ms)
- Usam database de teste
- HTTP requests
- Migrations e seeders

**Exemplo: API Layers**

```php
<?php

use App\Models\User;
use App\Models\Layer;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

test('authenticated user can list layers', function () {
    $user = User::factory()->create();
    Layer::factory()->count(5)->create();
    
    Sanctum::actingAs($user);
    
    $response = $this->getJson('/api/v1/layers');
    
    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'type', 'created_at']
            ],
            'meta'
        ]);
});

test('unauthenticated user cannot create layer', function () {
    $layerData = [
        'name' => 'Test Layer',
        'type' => 'polygon',
        'geojson' => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[[-47.9, -15.8], [-47.9, -15.7], [-47.8, -15.7]]]
            ]
        ]
    ];
    
    $response = $this->postJson('/api/v1/layers', $layerData);
    
    $response->assertStatus(401);
});

test('admin can create layer with valid geojson', function () {
    $admin = User::factory()->admin()->create();
    
    Sanctum::actingAs($admin);
    
    $layerData = [
        'name' => 'New Layer',
        'type' => 'polygon',
        'geojson' => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [-47.9292, -15.7801],
                        [-47.9200, -15.7801],
                        [-47.9200, -15.7700],
                        [-47.9292, -15.7700],
                        [-47.9292, -15.7801]
                    ]
                ]
            ]
        ]
    ];
    
    $response = $this->postJson('/api/v1/layers', $layerData);
    
    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'New Layer',
                'type' => 'polygon'
            ]
        ]);
    
    $this->assertDatabaseHas('layers', [
        'name' => 'New Layer',
        'type' => 'polygon',
        'created_by' => $admin->id
    ]);
});

test('layer creation validates geojson format', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);
    
    $invalidData = [
        'name' => 'Invalid Layer',
        'type' => 'polygon',
        'geojson' => 'not a json'
    ];
    
    $response = $this->postJson('/api/v1/layers', $invalidData);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['geojson']);
});
```

**Exemplo: Autenticação 2FA**

```php
<?php

use App\Models\User;

test('admin user requires 2fa on login', function () {
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('password')
    ]);
    
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $admin->email,
        'password' => 'password'
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'requires_2fa' => true
        ]);
});

test('non-admin user does not require 2fa if not enabled', function () {
    $user = User::factory()->operator()->create([
        'password' => bcrypt('password'),
        'two_factor_secret' => null
    ]);
    
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);
    
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

test('2fa code validates correctly', function () {
    $admin = User::factory()->admin()->withTwoFactor()->create();
    
    $validCode = $admin->generateTwoFactorCode();
    
    $response = $this->postJson('/api/v1/auth/2fa/confirm', [
        'user_id' => $admin->id,
        'code' => $validCode
    ]);
    
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token']]);
});
```

### 5.3 Integration Tests (Testes de Integração)

Testam integração entre componentes e serviços externos.

**Exemplo: PostGIS Integration**

```php
<?php

use App\Models\Layer;
use Illuminate\Support\Facades\DB;

test('postgis extension is installed', function () {
    $result = DB::select("SELECT PostGIS_Version()");
    
    expect($result)->not->toBeEmpty();
    expect($result[0]->postgis_version)->toContain('3.4');
});

test('geometry column accepts valid polygon', function () {
    $layer = Layer::factory()->create([
        'type' => 'polygon',
        'geometry' => DB::raw("ST_GeomFromGeoJSON('{\"type\":\"Polygon\",\"coordinates\":[[[-47.9292,-15.7801],[-47.9200,-15.7801],[-47.9200,-15.7700],[-47.9292,-15.7700],[-47.9292,-15.7801]]]}')")
    ]);
    
    expect($layer->exists)->toBeTrue();
    
    $geomType = DB::selectOne("SELECT ST_GeometryType(geometry) as type FROM layers WHERE id = ?", [$layer->id]);
    
    expect($geomType->type)->toBe('ST_Polygon');
});

test('gist index exists on geometry column', function () {
    $indexes = DB::select("
        SELECT indexname, indexdef 
        FROM pg_indexes 
        WHERE tablename = 'layers' 
        AND indexdef LIKE '%USING gist%'
    ");
    
    expect($indexes)->not->toBeEmpty();
});

test('spatial query finds layers within bounding box', function () {
    Layer::factory()->create([
        'name' => 'Inside',
        'geometry' => DB::raw("ST_GeomFromText('POINT(-47.9292 -15.7801)', 4326)")
    ]);
    
    Layer::factory()->create([
        'name' => 'Outside',
        'geometry' => DB::raw("ST_GeomFromText('POINT(-46.0 -14.0)', 4326)")
    ]);
    
    $results = DB::select("
        SELECT name FROM layers
        WHERE ST_Intersects(
            geometry,
            ST_MakeEnvelope(-48.0, -16.0, -47.8, -15.5, 4326)
        )
    ");
    
    expect($results)->toHaveCount(1);
    expect($results[0]->name)->toBe('Inside');
});
```

**Exemplo: MinIO Integration**

```php
<?php

use Illuminate\Support\Facades\Storage;

test('minio connection is configured', function () {
    expect(config('filesystems.disks.s3'))->not->toBeEmpty();
    expect(config('filesystems.disks.s3.endpoint'))->toContain('127.0.0.1:9000');
});

test('can store file in minio', function () {
    Storage::fake('s3');
    
    $content = json_encode(['type' => 'FeatureCollection', 'features' => []]);
    
    Storage::disk('s3')->put('test/layer.geojson', $content);
    
    expect(Storage::disk('s3')->exists('test/layer.geojson'))->toBeTrue();
    expect(Storage::disk('s3')->get('test/layer.geojson'))->toBe($content);
});

test('can delete file from minio', function () {
    Storage::fake('s3');
    
    Storage::disk('s3')->put('test/layer.geojson', 'content');
    Storage::disk('s3')->delete('test/layer.geojson');
    
    expect(Storage::disk('s3')->exists('test/layer.geojson'))->toBeFalse();
});
```

**Exemplo: Evolution API Integration**

```php
<?php

use App\Services\EvolutionApiService;
use Illuminate\Support\Facades\Http;

test('evolution api sends whatsapp message', function () {
    Http::fake([
        'https://api.evolution-api.com/*' => Http::response([
            'status' => 'success',
            'messageId' => 'msg_123'
        ], 200)
    ]);
    
    $service = new EvolutionApiService();
    
    $result = $service->sendMessage('5561999999999', 'Test message');
    
    expect($result)->toBeTrue();
    
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.evolution-api.com/messages/send' &&
               $request['phone'] === '5561999999999' &&
               $request['message'] === 'Test message';
    });
});

test('evolution api handles failure gracefully', function () {
    Http::fake([
        'https://api.evolution-api.com/*' => Http::response(['error' => 'Failed'], 500)
    ]);
    
    $service = new EvolutionApiService();
    
    expect(fn() => $service->sendMessage('invalid', 'Test'))
        ->toThrow(\Exception::class);
});
```

### 5.4 Browser Tests (Testes E2E)

Testam fluxos completos no navegador com Laravel Dusk.

**Exemplo: Login Flow**

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password')
    ]);
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/painel/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/painel')
            ->assertSee('Dashboard');
    });
});

test('admin user sees 2fa prompt', function () {
    $admin = User::factory()->admin()->withTwoFactor()->create();
    
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->visit('/painel/login')
            ->type('email', $admin->email)
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/painel/2fa')
            ->assertSee('Two-Factor Authentication');
    });
});
```

**Exemplo: Layer Upload**

```php
<?php

test('admin can upload geojson layer', function () {
    $admin = User::factory()->admin()->create();
    
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/painel/layers/create')
            ->type('name', 'Test Layer')
            ->select('type', 'polygon')
            ->attach('file', __DIR__ . '/fixtures/test-layer.geojson')
            ->press('Create')
            ->assertPathIs('/painel/layers')
            ->assertSee('Layer created successfully')
            ->assertSee('Test Layer');
    });
});
```

**Exemplo: Map Visualization**

```php
<?php

test('public map displays layers', function () {
    Layer::factory()->count(3)->create(['is_active' => true]);
    
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('ACTO Maps')
            ->waitFor('#map', 10)
            ->assertScript('return document.getElementById("map") !== null')
            ->assertScript('return window.mapView !== undefined');
    });
});
```

## 6. Configuração de Ambiente

### 6.1 Database de Teste

Configurar `.env.testing`:

```env
APP_ENV=testing
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_test
DB_USERNAME=laravel_user
DB_PASSWORD=secret

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

### 6.2 phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
        <exclude>
            <directory>app/Console</directory>
            <file>app/Helpers/helpers.php</file>
        </exclude>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="pgsql"/>
        <env name="DB_DATABASE" value="laravel_test"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>
```

### 6.3 Pest.php

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
)->in('Feature', 'Integration');

uses(Tests\TestCase::class)->in('Unit');

function actingAsAdmin()
{
    return test()->actingAs(
        \App\Models\User::factory()->admin()->create()
    );
}

function actingAsOperator()
{
    return test()->actingAs(
        \App\Models\User::factory()->operator()->create()
    );
}

function actingAsViewer()
{
    return test()->actingAs(
        \App\Models\User::factory()->viewer()->create()
    );
}
```

## 7. Executar Testes

### 7.1 Comandos

```bash
# Todos os testes
php artisan test

# Testes específicos
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test tests/Unit/Models/LayerTest.php

# Com coverage
php artisan test --coverage --min=80

# Parallel
php artisan test --parallel

# Dusk
php artisan dusk

# Watch mode (rerun on file change)
php artisan test --watch
```

### 7.2 Filtros

```bash
# Por nome
php artisan test --filter=layer

# Por group
php artisan test --group=api

# Excluir group
php artisan test --exclude-group=slow
```

## 8. Coverage

### 8.1 Meta

Cobertura mínima: 80%

| Camada | Meta | Prioridade |
|--------|------|------------|
| Models | 90% | Alta |
| Services | 90% | Alta |
| Repositories | 85% | Alta |
| Controllers | 80% | Média |
| Middlewares | 85% | Alta |
| Helpers | 95% | Média |

### 8.2 Gerar Relatório

```bash
# HTML report
php artisan test --coverage --coverage-html coverage-report

# Text report
php artisan test --coverage --coverage-text

# XML (para CI/CD)
php artisan test --coverage --coverage-clover coverage.xml
```

## 9. CI/CD Pipeline

### 9.1 GitHub Actions

`.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgis/postgis:16-3.4
        env:
          POSTGRES_DB: laravel_test
          POSTGRES_USER: laravel_user
          POSTGRES_PASSWORD: secret
        ports:
          - 5432:5432
        options: --health-cmd pg_isready --health-interval 10s
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: pdo_pgsql, pgsql, mbstring, xml, bcmath
          coverage: xdebug
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Copy .env
        run: cp .env.testing .env
      
      - name: Generate Key
        run: php artisan key:generate
      
      - name: Run Migrations
        run: php artisan migrate --force
      
      - name: Run Tests
        run: php artisan test --coverage --min=80
      
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

## 10. Best Practices

### 10.1 Nomenclatura

```php
// Good
test('user can create layer with valid data')
test('layer name is required')
test('admin cannot delete layer created by another admin')

// Bad
test('test1')
test('layer test')
test('it works')
```

### 10.2 Arrange-Act-Assert (AAA)

```php
test('layer service creates layer', function () {
    // Arrange
    $admin = User::factory()->admin()->create();
    $layerData = ['name' => 'Test', 'type' => 'polygon'];
    
    // Act
    $layer = $layerService->create($layerData, $admin);
    
    // Assert
    expect($layer)->toBeInstanceOf(Layer::class);
    expect($layer->name)->toBe('Test');
});
```

### 10.3 Isolamento

```php
// Good - isolado
test('layer calculates area', function () {
    $layer = Layer::factory()->make([
        'geometry' => 'POLYGON(...)'
    ]);
    
    expect($layer->calculateArea())->toBeGreaterThan(0);
});

// Bad - dependente de database
test('layer calculates area', function () {
    $layer = Layer::find(1);
    expect($layer->calculateArea())->toBeGreaterThan(0);
});
```

### 10.4 Factories

```php
// database/factories/LayerFactory.php
public function definition(): array
{
    return [
        'name' => $this->faker->words(3, true),
        'description' => $this->faker->sentence(),
        'type' => $this->faker->randomElement(['point', 'linestring', 'polygon']),
        'is_active' => true,
        'created_by' => User::factory(),
    ];
}

public function polygon(): static
{
    return $this->state(fn (array $attributes) => [
        'type' => 'polygon',
        'geometry' => DB::raw("ST_GeomFromGeoJSON('{\"type\":\"Polygon\",\"coordinates\":...}')")
    ]);
}

public function inactive(): static
{
    return $this->state(fn (array $attributes) => [
        'is_active' => false,
    ]);
}
```

### 10.5 Mocking

```php
// Good - mock apenas o necessário
test('service calls repository once', function () {
    $mock = Mockery::mock(LayerRepository::class);
    $mock->shouldReceive('find')->once()->with(1)->andReturn(new Layer());
    
    $service = new LayerService($mock);
    $result = $service->getById(1);
    
    expect($result)->toBeInstanceOf(Layer::class);
});

// Bad - mock excessivo
test('too much mocking', function () {
    $mock1 = Mockery::mock(Dep1::class);
    $mock2 = Mockery::mock(Dep2::class);
    $mock3 = Mockery::mock(Dep3::class);
    // ... código de teste complexo
});
```

## 11. Métricas

### 11.1 KPIs de Testes

| Métrica | Meta | Medição |
|---------|------|---------|
| Coverage | > 80% | PHPUnit coverage |
| Tempo de execução | < 2 min | CI/CD pipeline |
| Taxa de falha | < 5% | Histórico de builds |
| Testes por feature | > 5 | Contagem manual |

### 11.2 Dashboard

Monitorar com:
- CodeCov
- SonarQube
- PHPMetrics

## 12. Troubleshooting

### 12.1 Testes lentos

```bash
# Identificar testes lentos
php artisan test --profile

# Usar parallel execution
php artisan test --parallel --processes=4
```

### 12.2 Falhas intermitentes

```bash
# Repetir teste 10x
php artisan test --repeat=10 tests/Feature/LayerTest.php
```

### 12.3 Debug

```php
test('debug example', function () {
    $layer = Layer::factory()->create();
    
    dump($layer->toArray());  // Debug output
    dd($layer->geometry);     // Dump and die
    
    ray($layer);              // Ray app
});
```

## 13. Referências

- Pest PHP: https://pestphp.com/
- Laravel Testing: https://laravel.com/docs/12.x/testing
- PHPUnit: https://phpunit.de/
- Laravel Dusk: https://laravel.com/docs/12.x/dusk
- Mockery: http://docs.mockery.io/

