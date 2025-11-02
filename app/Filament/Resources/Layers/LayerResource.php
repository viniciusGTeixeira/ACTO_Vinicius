<?php

/**
 * ACTO Maps - Layer Resource
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Filament\Resources\Layers;

use App\Filament\Resources\Layers\Pages\CreateLayer;
use App\Filament\Resources\Layers\Pages\EditLayer;
use App\Filament\Resources\Layers\Pages\ListLayers;
use App\Filament\Resources\Layers\Schemas\LayerForm;
use App\Filament\Resources\Layers\Tables\LayersTable;
use App\Models\Layer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LayerResource extends Resource
{
    protected static ?string $model = Layer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Camadas';

    protected static ?string $modelLabel = 'Camada';

    protected static ?string $pluralModelLabel = 'Camadas';

    protected static string|\UnitEnum|null $navigationGroup = 'Geoespacial';

    protected static ?int $navigationSort = 1;

    /**
     * Determine if the user can access this resource
     *
     * @return bool
     */
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->can('view_layer');
    }

    /**
     * Determine if the user can create records
     *
     * @return bool
     */
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->can('create_layer');
    }

    /**
     * Determine if the user can edit a record
     *
     * @param mixed $record
     * @return bool
     */
    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->can('edit_layer');
    }

    /**
     * Determine if the user can delete a record
     *
     * @param mixed $record
     * @return bool
     */
    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->can('delete_layer');
    }

    /**
     * Configure form schema
     *
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return LayerForm::configure($schema);
    }

    /**
     * Configure table
     *
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return LayersTable::configure($table);
    }

    /**
     * Get relations
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get pages
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => ListLayers::route('/'),
            'create' => CreateLayer::route('/create'),
            'edit' => EditLayer::route('/{record}/edit'),
        ];
    }
}
