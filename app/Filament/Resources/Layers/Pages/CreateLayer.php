<?php

/**
 * ACTO Maps - Create Layer Page
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Filament\Resources\Layers\Pages;

use App\Contracts\Services\LayerServiceInterface;
use App\Filament\Resources\Layers\LayerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateLayer extends CreateRecord
{
    protected static string $resource = LayerResource::class;

    /**
     * Mutate form data before creating the record
     *
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('[LAYER_CREATE] Starting layer creation process', [
            'layer_name' => $data['name'] ?? 'unknown',
            'has_geojson_file' => isset($data['geojson_file']),
        ]);

        // Remove geojson_file from data to avoid database insertion
        // It's kept in $this->data for processing in handleRecordCreation
        if (isset($data['geojson_file'])) {
            unset($data['geojson_file']);
        }
        
        return $data;
    }

    /**
     * Handle record creation with GeoJSON processing
     *
     * @param array $data
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        Log::info('[LAYER_CREATE] Handling record creation', [
            'layer_name' => $data['name'],
            'user_id' => Auth::check() ? Auth::id() : 'unknown',
        ]);

        $layerService = app(LayerServiceInterface::class);
        
        // Get the temporary file from Livewire
        $temporaryFile = $this->data['geojson_file'] ?? null;
        
        Log::info('[LAYER_CREATE] Raw file data', [
            'geojson_file_type' => gettype($temporaryFile),
            'geojson_file_value' => is_array($temporaryFile) ? 'array[' . count($temporaryFile) . ']' : (is_object($temporaryFile) ? get_class($temporaryFile) : $temporaryFile),
            'is_array' => is_array($temporaryFile),
            'array_keys' => is_array($temporaryFile) ? array_keys($temporaryFile) : null,
        ]);
        
        if (!$temporaryFile) {
            Log::error('[LAYER_CREATE] Missing GeoJSON file', [
                'has_file' => !is_null($temporaryFile),
                'data_keys' => array_keys($this->data),
            ]);
            throw new \InvalidArgumentException('GeoJSON file is required');
        }

        if (is_array($temporaryFile)) {
            Log::info('[LAYER_CREATE] File is array, extracting element', [
                'array_count' => count($temporaryFile),
                'array_contents' => array_map(function($item) {
                    return is_object($item) ? get_class($item) : gettype($item) . ': ' . $item;
                }, $temporaryFile),
            ]);
            
            // Get first value from associative array (UUID-keyed)
            $temporaryFile = reset($temporaryFile);
            
            if (!$temporaryFile) {
                Log::error('[LAYER_CREATE] Array is empty or first element is null');
                throw new \InvalidArgumentException('GeoJSON file array is empty');
            }
        }

        Log::info('[LAYER_CREATE] Temporary file info', [
            'type' => is_object($temporaryFile) ? get_class($temporaryFile) : gettype($temporaryFile),
            'is_temporary_file' => $temporaryFile instanceof TemporaryUploadedFile,
            'is_string' => is_string($temporaryFile),
            'value' => is_string($temporaryFile) ? $temporaryFile : 'not-a-string',
        ]);

        // Handle TemporaryUploadedFile
        if ($temporaryFile instanceof TemporaryUploadedFile) {
            $tempPath = $temporaryFile->getRealPath();
            $originalName = $temporaryFile->getClientOriginalName();
            $mimeType = $temporaryFile->getMimeType();
            
            $geojsonFile = new UploadedFile(
                $tempPath,
                $originalName,
                $mimeType,
                null,
                true
            );

            Log::info('[LAYER_CREATE] GeoJSON file from TemporaryUploadedFile', [
                'original_name' => $geojsonFile->getClientOriginalName(),
                'size' => $geojsonFile->getSize(),
                'mime_type' => $geojsonFile->getMimeType(),
                'temp_path' => $tempPath,
            ]);
        }
        // Handle string path (file already saved by Filament)
        elseif (is_string($temporaryFile)) {
            // File was already saved by Filament to the final destination
            $disk = config('filesystems.default');
            $storage = Storage::disk($disk);
            
            if (!$storage->exists($temporaryFile)) {
                Log::error('[LAYER_CREATE] File path does not exist', [
                    'path' => $temporaryFile,
                    'disk' => $disk,
                ]);
                throw new \InvalidArgumentException('Uploaded file not found in storage: ' . $temporaryFile);
            }
            
            // Download to local temp to create UploadedFile
            $tempPath = tempnam(sys_get_temp_dir(), 'geojson_');
            file_put_contents($tempPath, $storage->get($temporaryFile));
            
            $originalName = basename($temporaryFile);
            $mimeType = 'application/geo+json';
            
            $geojsonFile = new UploadedFile(
                $tempPath,
                $originalName,
                $mimeType,
                null,
                true
            );

            Log::info('[LAYER_CREATE] GeoJSON file from storage path', [
                'original_name' => $geojsonFile->getClientOriginalName(),
                'size' => $geojsonFile->getSize(),
                'mime_type' => $geojsonFile->getMimeType(),
                'storage_path' => $temporaryFile,
                'temp_path' => $tempPath,
            ]);
            
            // Delete from storage after loading
            $storage->delete($temporaryFile);
        }
        else {
            throw new \InvalidArgumentException('Invalid file type received: ' . gettype($temporaryFile));
        }

        try {
            $layer = $layerService->createLayerFromGeojson(
                $data['name'],
                $geojsonFile
            );

            Log::info('[LAYER_CREATE] Layer created successfully', [
                'layer_id' => $layer->id,
                'layer_name' => $layer->name,
            ]);

            // Clean up temporary files
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            
            if ($temporaryFile instanceof TemporaryUploadedFile) {
                $temporaryFile->delete();
            }

            return $layer;
        } catch (\Exception $e) {
            Log::error('[LAYER_CREATE] Error creating layer', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'layer_name' => $data['name'],
            ]);

            // Clean up temporary files on error
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            
            if ($temporaryFile instanceof TemporaryUploadedFile) {
                $temporaryFile->delete();
            }

            \Filament\Notifications\Notification::make()
                ->title('Erro ao criar camada')
                ->body($e->getMessage())
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    /**
     * Get redirect URL after creation
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Get success notification title
     *
     * @return string|null
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Camada criada com sucesso!';
    }
}
