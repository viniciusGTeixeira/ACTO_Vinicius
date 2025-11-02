<?php

/**
 * ACTO Maps - Edit Layer Page
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Filament\Resources\Layers\Pages;

use App\Filament\Resources\Layers\LayerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLayer extends EditRecord
{
    protected static string $resource = LayerResource::class;

    /**
     * Get header actions
     *
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir')
                ->successNotificationTitle('Camada excluída com sucesso'),
        ];
    }

    /**
     * Mutate form data before filling
     *
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Add geometry type to form data
        if ($this->record) {
            $data['geometry_type'] = $this->record->geometry_type ?? 'N/A';
        }

        return $data;
    }

    /**
     * Get success notification title
     *
     * @return string|null
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Camada atualizada com sucesso!';
    }
}
