<?php

/**
 * ACTO Maps - Layer Form Schema
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Filament\Resources\Layers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Camada')
                    ->description('Configure o nome e faça upload do arquivo GeoJSON')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome da Camada')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ex: Municípios do Brasil')
                            ->helperText('Máximo de 100 caracteres'),
                        
                        FileUpload::make('geojson_file')
                            ->label('Arquivo GeoJSON')
                            ->acceptedFileTypes(['application/json', 'application/geo+json', '.geojson'])
                            ->maxSize(10240)
                            ->helperText('Faça upload de um arquivo GeoJSON válido (máximo 10MB)')
                            ->disk(config('filesystems.default'))
                            ->directory('geojson-uploads')
                            ->visibility('private')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->hidden(fn (string $context): bool => $context === 'edit')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                
                Section::make('Informações Técnicas')
                    ->description('Dados geoespaciais da camada')
                    ->schema([
                        TextInput::make('geometry_type')
                            ->label('Tipo de Geometria')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Será preenchido após o upload')
                            ->helperText('Point, LineString, Polygon, etc.')
                            ->visible(fn (string $context): bool => $context === 'edit'),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->visible(fn (string $context): bool => $context === 'edit'),
            ]);
    }
}
