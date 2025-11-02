# Paleta de Cores ACTO Maps

## Cor Principal

### Verde ACTO (`#00c853`)

Esta é a cor principal do sistema, usada em todos os componentes primários, botões principais, links e elementos de destaque.

**Hex:** `#00c853`  
**RGB:** `rgb(0, 200, 83)`  
**CSS Variable:** `var(--color-primary)`

## Gradientes

### Gradiente Primário

Usado em elementos visuais de destaque, como fundos de header, botões especiais e efeitos visuais.

```css
background: linear-gradient(135deg, #00c853 0%, #009688 100%);
```

**CSS Variable:** `var(--gradient-primary)`

## Tons da Cor Principal

A paleta inclui variações da cor principal para diferentes níveis de contraste e estados:

| Tom | Hex | Uso Recomendado |
|-----|-----|-----------------|
| 50 | `#e8f5e9` | Fundos muito claros, hover states |
| 100 | `#c8e6c9` | Fundos claros, badges |
| 200 | `#a5d6a7` | Estados hover |
| 300 | `#81c784` | Elementos secundários |
| 400 | `#66bb6a` | Estados ativos |
| 500 | `#00c853` | **Cor principal** |
| 600 | `#00b248` | Hover em botões primários |
| 700 | `#009c3d` | Estados pressed |
| 800 | `#008632` | Elementos de alto contraste |
| 900 | `#006b1f` | Textos em fundos claros |
| 950 | `#004d16` | Sombras e contornos escuros |

## Cores Neutras

| Variável | Hex | Uso |
|----------|-----|-----|
| `--color-text-primary` | `#1a1a1a` | Texto principal |
| `--color-text-secondary` | `#6b7280` | Texto secundário, descrições |
| `--color-border` | `#e5e7eb` | Bordas de inputs e containers |
| `--color-background` | `#ffffff` | Fundo principal |
| `--color-background-alt` | `#f9fafb` | Fundos alternativos |

## Cores de Status

| Variável | Hex | Uso |
|----------|-----|-----|
| `--color-success` | `#10b981` | Mensagens de sucesso |
| `--color-error` | `#ef4444` | Erros e alertas |
| `--color-warning` | `#f59e0b` | Avisos |
| `--color-info` | `#3b82f6` | Informações |

## Uso no Código

### CSS

```css
/* Usando variáveis CSS */
.button {
    background-color: var(--color-primary);
    color: white;
}

.button:hover {
    background-color: var(--color-primary-600);
}

/* Usando gradiente */
.header {
    background: var(--gradient-primary);
}
```

### JavaScript (ArcGIS Maps)

Para símbolos do mapa:

```javascript
// Verde ACTO em RGB: [0, 200, 83]
const symbol = new SimpleMarkerSymbol({
    color: [0, 200, 83, 0.8],  // RGB + Alpha
    size: 8,
    outline: {
        color: [255, 255, 255],
        width: 1
    }
});
```

### Blade Templates

```blade
<div style="background: var(--gradient-primary);">
    Conteúdo
</div>

<button class="btn btn-primary">
    Ação Principal
</button>
```

### Filament (Painel Administrativo)

A cor principal está configurada em `app/Providers/Filament/AdminPanelProvider.php`:

```php
->colors([
    'primary' => [
        50 => '#e8f5e9',
        100 => '#c8e6c9',
        // ... todos os tons
        500 => '#00c853',  // Cor principal
        // ...
    ],
])
```

## Acessibilidade

A cor principal `#00c853` possui:
- **Contraste com branco:** 3.4:1 (AA para textos grandes)
- **Contraste com preto:** 6.2:1 (AA para textos normais)

Para textos sobre fundo verde, use:
- Branco (`#ffffff`) para textos normais
- Tom 900 (`#006b1f`) para textos em fundos muito claros

## Aplicações

### Sistema de Autenticação
- Gradiente no painel visual esquerdo
- Botões de ação principal
- Links e elementos interativos
- Checkboxes e radio buttons

### Mapa Público
- Símbolos de geometrias (pontos, linhas, polígonos)
- Bordas de painéis e listas
- Spinner de carregamento
- Elementos de navegação

### Painel Administrativo (Filament)
- Cor primária de todos os componentes
- Botões de ação
- Estados hover e focus
- Indicadores de seleção

## Tema Claro

O sistema utiliza **tema claro** como padrão. A paleta de cores foi otimizada para:
- Máxima legibilidade em fundos brancos
- Contraste adequado em todas as variações
- Consistência visual em todos os módulos

## Diretrizes de Uso

1. **Use sempre a cor principal** (`#00c853`) para ações primárias
2. **Use o gradiente** para elementos visuais de destaque (não para texto)
3. **Use os tons claros (50-200)** para estados hover e fundos
4. **Use os tons escuros (700-950)** para bordas e sombras
5. **Mantenha consistência** usando as variáveis CSS definidas

## Referências

- **Filament:** [Configuração de Cores](https://filamentphp.com/docs/4.x/panels/themes#customizing-the-color-palette)
- **Tailwind CSS:** [Customização de Cores](https://tailwindcss.com/docs/customizing-colors)
- **CSS Variables:** [MDN Web Docs](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)

