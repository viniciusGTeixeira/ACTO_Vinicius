<?php

/**
 * ACTO Maps - Layers Table
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Filament\Resources\Layers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('name')
                    ->label('Nome da Camada')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nome copiado!')
                    ->copyMessageDuration(1500),
                
                TextColumn::make('geometry_type')
                    ->label('Tipo de Geometria')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'POINT', 'MULTIPOINT' => 'success',
                        'LINESTRING', 'MULTILINESTRING' => 'info',
                        'POLYGON', 'MULTIPOLYGON' => 'warning',
                        default => 'gray',
                    })
                    ->default('N/A'),
                
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar'),
                EditAction::make()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir selecionados'),
                ]),
            ])
            ->emptyStateHeading('Nenhuma camada cadastrada')
            ->emptyStateDescription('Comece criando uma nova camada geoespacial')
            ->emptyStateActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Nova Camada'),
            ]);
    }
}
