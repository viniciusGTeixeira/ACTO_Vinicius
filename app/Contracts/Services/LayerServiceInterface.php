<?php

/**
 * ACTO Maps - Layer Service Interface
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Contracts\Services;

use App\Models\Layer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface LayerServiceInterface
{
    /**
     * Get all layers
     *
     * @return Collection
     */
    public function getAllLayers(): Collection;

    /**
     * Get layer by ID
     *
     * @param int $id
     * @return Layer|null
     */
    public function getLayerById(int $id): ?Layer;

    /**
     * Create layer from GeoJSON file
     *
     * @param string $name
     * @param UploadedFile $file
     * @return Layer
     */
    public function createLayerFromGeojson(string $name, UploadedFile $file): Layer;

    /**
     * Update layer
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateLayer(int $id, array $data): bool;

    /**
     * Delete layer
     *
     * @param int $id
     * @return bool
     */
    public function deleteLayer(int $id): bool;

    /**
     * Get all layers as GeoJSON feature collection
     *
     * @return array
     */
    public function getLayersAsFeatureCollection(): array;

    /**
     * Validate GeoJSON structure
     *
     * @param string $geojson
     * @return bool
     */
    public function validateGeojson(string $geojson): bool;
}

