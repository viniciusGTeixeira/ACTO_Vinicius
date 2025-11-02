<?php

/**
 * ACTO Maps - Layer API Controller
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Http\Controllers\Api;

use App\Contracts\Services\LayerServiceInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LayerController extends Controller
{
    /**
     * Layer service instance
     *
     * @var LayerServiceInterface
     */
    protected $layerService;

    /**
     * Constructor
     *
     * @param LayerServiceInterface $layerService
     */
    public function __construct(LayerServiceInterface $layerService)
    {
        $this->layerService = $layerService;
    }

    /**
     * Get all layers
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $layers = $this->layerService->getAllLayers();

            return ResponseHelper::success(
                $layers,
                'Layers retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Failed to retrieve layers: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get layer by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $layer = $this->layerService->getLayerById($id);

            if (!$layer) {
                return ResponseHelper::notFound('Layer not found');
            }

            return ResponseHelper::success(
                $layer,
                'Layer retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Failed to retrieve layer: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get all layers as GeoJSON FeatureCollection
     *
     * @return JsonResponse
     */
    public function geojson(): JsonResponse
    {
        try {
            $featureCollection = $this->layerService->getLayersAsFeatureCollection();

            return response()->json($featureCollection);
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Failed to retrieve GeoJSON: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get single layer as GeoJSON Feature
     *
     * @param int $id
     * @return JsonResponse
     */
    public function geojsonSingle(int $id): JsonResponse
    {
        try {
            $layer = $this->layerService->getLayerById($id);

            if (!$layer) {
                return ResponseHelper::notFound('Layer not found');
            }

            $geojson = $layer->getGeometryAsGeojson();

            if (!$geojson) {
                return ResponseHelper::error('No geometry available for this layer');
            }

            $feature = [
                'type' => 'Feature',
                'id' => $layer->id,
                'properties' => [
                    'name' => $layer->name,
                    'created_at' => $layer->created_at,
                    'updated_at' => $layer->updated_at,
                ],
                'geometry' => json_decode($geojson),
            ];

            return response()->json($feature);
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Failed to retrieve GeoJSON: ' . $e->getMessage(),
                500
            );
        }
    }
}

