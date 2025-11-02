<?php

/**
 * ACTO Maps - Layer Repository Interface
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Contracts\Repositories;

use App\Models\Layer;
use Illuminate\Database\Eloquent\Collection;

interface LayerRepositoryInterface
{
    /**
     * Get all layers
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Find layer by ID
     *
     * @param int $id
     * @return Layer|null
     */
    public function findById(int $id): ?Layer;

    /**
     * Create new layer
     *
     * @param array $data
     * @return Layer
     */
    public function create(array $data): Layer;

    /**
     * Update layer
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete layer
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get layers by geometry type
     *
     * @param string $type
     * @return Collection
     */
    public function getByGeometryType(string $type): Collection;

    /**
     * Get layers as GeoJSON
     *
     * @return array
     */
    public function getAllAsGeojson(): array;
}

