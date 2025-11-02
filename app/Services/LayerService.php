<?php

/**
 * ACTO Maps - Layer Service
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Services;

use App\Contracts\Repositories\LayerRepositoryInterface;
use App\Contracts\Services\LayerServiceInterface;
use App\Models\Layer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LayerService implements LayerServiceInterface
{
    /**
     * Layer repository instance
     *
     * @var LayerRepositoryInterface
     */
    protected $layerRepository;

    /**
     * Constructor
     *
     * @param LayerRepositoryInterface $layerRepository
     */
    public function __construct(LayerRepositoryInterface $layerRepository)
    {
        $this->layerRepository = $layerRepository;
    }

    /**
     * Get all layers
     *
     * @return Collection
     */
    public function getAllLayers(): Collection
    {
        return $this->layerRepository->getAll();
    }

    /**
     * Get layer by ID
     *
     * @param int $id
     * @return Layer|null
     */
    public function getLayerById(int $id): ?Layer
    {
        return $this->layerRepository->findById($id);
    }

    /**
     * Create layer from GeoJSON file
     *
     * @param string $name
     * @param UploadedFile $file
     * @return Layer
     * @throws InvalidArgumentException
     */
    public function createLayerFromGeojson(string $name, UploadedFile $file): Layer
    {
        Log::info('[LAYER_SERVICE] Starting createLayerFromGeojson', [
            'layer_name' => $name,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'temp_path' => $file->getRealPath(),
        ]);

        // Store file permanently in storage (S3/MinIO bucket)
        $storagePath = null;
        $diskName = config('filesystems.default', 'local');
        
        Log::info('[LAYER_SERVICE] Attempting to store file permanently in storage', [
            'disk' => $diskName,
            'directory' => 'geojson-uploads',
        ]);
        
        try {
            $storagePath = Storage::disk($diskName)->putFile(
                'geojson-uploads',
                $file,
                'private'
            );
            
            Log::info('[LAYER_SERVICE] File stored successfully in bucket', [
                'storage_path' => $storagePath,
                'disk' => $diskName,
            ]);

            // Verify file exists in storage
            if (Storage::disk($diskName)->exists($storagePath)) {
                Log::info('[LAYER_SERVICE] File existence confirmed in bucket', [
                    'path' => $storagePath,
                    'size' => Storage::disk($diskName)->size($storagePath),
                ]);
            } else {
                Log::error('[LAYER_SERVICE] File not found in bucket after upload', [
                    'expected_path' => $storagePath,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[LAYER_SERVICE] Failed to store file in bucket', [
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'disk' => $diskName,
            ]);
            // Continue processing even if storage fails, as we still have the temp file
        }

        Log::info('[LAYER_SERVICE] Reading file content from temporary path');
        $geojsonContent = file_get_contents($file->getRealPath());
        
        Log::info('[LAYER_SERVICE] File content read successfully', [
            'content_length' => strlen($geojsonContent),
        ]);

        Log::info('[LAYER_SERVICE] Validating GeoJSON structure');
        if (!$this->validateGeojson($geojsonContent)) {
            Log::error('[LAYER_SERVICE] GeoJSON validation failed');
            throw new InvalidArgumentException('Invalid GeoJSON format');
        }

        Log::info('[LAYER_SERVICE] GeoJSON validation passed, decoding JSON');
        $geojson = json_decode($geojsonContent, true);
        
        Log::info('[LAYER_SERVICE] JSON decoded successfully', [
            'geojson_type' => $geojson['type'] ?? 'unknown',
        ]);

        // Extract geometry from GeoJSON
        Log::info('[LAYER_SERVICE] Extracting geometry from GeoJSON');
        $geometry = $this->extractGeometry($geojson);
        
        if (!$geometry) {
            Log::error('[LAYER_SERVICE] No valid geometry found in GeoJSON', [
                'geojson_type' => $geojson['type'] ?? 'unknown',
            ]);
            throw new InvalidArgumentException('No valid geometry found in GeoJSON');
        }

        Log::info('[LAYER_SERVICE] Geometry extracted successfully', [
            'geometry_type' => $geometry['type'] ?? 'unknown',
        ]);

        // Create layer with PostGIS
        Log::info('[LAYER_SERVICE] Inserting layer into database with PostGIS');
        $layer = new Layer();
        $layer->name = $name;
        
        try {
            // Use ST_GeomFromGeoJSON to insert geometry
            DB::statement(
                "INSERT INTO geo.layers (name, geometry, created_at, updated_at) 
                 VALUES (?, ST_GeomFromGeoJSON(?), NOW(), NOW())",
                [$name, json_encode($geometry)]
            );
            
            Log::info('[LAYER_SERVICE] Layer inserted into database successfully');
        } catch (\Exception $e) {
            Log::error('[LAYER_SERVICE] Failed to insert layer into database', [
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
        
        // Retrieve the created layer
        Log::info('[LAYER_SERVICE] Retrieving created layer from database');
        $createdLayer = Layer::orderBy('id', 'desc')->first();

        if ($createdLayer) {
            Log::info('[LAYER_SERVICE] Layer creation process completed successfully', [
                'layer_id' => $createdLayer->id,
                'layer_name' => $createdLayer->name,
                'storage_path' => $storagePath,
            ]);
        } else {
            Log::error('[LAYER_SERVICE] Failed to retrieve created layer from database');
        }

        return $createdLayer;
    }

    /**
     * Update layer
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateLayer(int $id, array $data): bool
    {
        return $this->layerRepository->update($id, $data);
    }

    /**
     * Delete layer
     *
     * @param int $id
     * @return bool
     */
    public function deleteLayer(int $id): bool
    {
        return $this->layerRepository->delete($id);
    }

    /**
     * Get all layers as GeoJSON feature collection
     *
     * @return array
     */
    public function getLayersAsFeatureCollection(): array
    {
        $layers = $this->layerRepository->getAllAsGeojson();

        $features = array_map(function ($layer) {
            return [
                'type' => 'Feature',
                'id' => $layer['id'],
                'properties' => [
                    'name' => $layer['name'],
                    'created_at' => $layer['created_at'],
                    'updated_at' => $layer['updated_at'],
                ],
                'geometry' => $layer['geometry'],
            ];
        }, $layers);

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * Validate GeoJSON structure
     *
     * @param string $geojson
     * @return bool
     */
    public function validateGeojson(string $geojson): bool
    {
        Log::info('[LAYER_SERVICE] Validating GeoJSON structure');

        $data = json_decode($geojson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('[LAYER_SERVICE] Invalid JSON format', [
                'error' => json_last_error_msg(),
            ]);
            return false;
        }

        Log::info('[LAYER_SERVICE] JSON parsed successfully in validation');

        if (!isset($data['type'])) {
            Log::error('[LAYER_SERVICE] GeoJSON missing type field');
            return false;
        }

        $validTypes = ['Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon', 'Feature', 'FeatureCollection', 'GeometryCollection'];
        
        if (!in_array($data['type'], $validTypes)) {
            Log::error('[LAYER_SERVICE] Invalid GeoJSON type', [
                'type' => $data['type'],
                'valid_types' => $validTypes,
            ]);
            return false;
        }

        Log::info('[LAYER_SERVICE] GeoJSON structure validation passed', [
            'type' => $data['type'],
        ]);

        return true;
    }

    /**
     * Extract geometry from GeoJSON
     *
     * @param array $geojson
     * @return array|null
     */
    protected function extractGeometry(array $geojson): ?array
    {
        Log::info('[LAYER_SERVICE] Extracting geometry', [
            'geojson_type' => $geojson['type'] ?? 'unknown',
        ]);

        if (isset($geojson['type'])) {
            switch ($geojson['type']) {
                case 'Feature':
                    $geometry = $geojson['geometry'] ?? null;
                    Log::info('[LAYER_SERVICE] Extracted geometry from Feature', [
                        'has_geometry' => !is_null($geometry),
                        'geometry_type' => $geometry['type'] ?? 'unknown',
                    ]);
                    return $geometry;
                
                case 'FeatureCollection':
                    $geometry = $geojson['features'][0]['geometry'] ?? null;
                    Log::info('[LAYER_SERVICE] Extracted geometry from FeatureCollection', [
                        'has_geometry' => !is_null($geometry),
                        'geometry_type' => $geometry['type'] ?? 'unknown',
                        'features_count' => count($geojson['features'] ?? []),
                    ]);
                    return $geometry;
                
                case 'Point':
                case 'LineString':
                case 'Polygon':
                case 'MultiPoint':
                case 'MultiLineString':
                case 'MultiPolygon':
                    Log::info('[LAYER_SERVICE] Direct geometry type detected', [
                        'geometry_type' => $geojson['type'],
                    ]);
                    return $geojson;
                
                default:
                    Log::warning('[LAYER_SERVICE] Unknown geometry type', [
                        'type' => $geojson['type'],
                    ]);
                    return null;
            }
        }

        Log::warning('[LAYER_SERVICE] GeoJSON missing type field in extraction');
        return null;
    }
}

