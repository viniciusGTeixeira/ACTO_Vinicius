<?php

/**
 * ACTO Maps - Layer Repository
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Repositories;

use App\Contracts\Repositories\LayerRepositoryInterface;
use App\Models\Layer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LayerRepository implements LayerRepositoryInterface
{
    /**
     * Get all layers
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Layer::orderBy('created_at', 'desc')->get();
    }

    /**
     * Find layer by ID
     *
     * @param int $id
     * @return Layer|null
     */
    public function findById(int $id): ?Layer
    {
        return Layer::find($id);
    }

    /**
     * Create new layer
     *
     * @param array $data
     * @return Layer
     */
    public function create(array $data): Layer
    {
        return Layer::create($data);
    }

    /**
     * Update layer
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $layer = $this->findById($id);
        
        if (!$layer) {
            return false;
        }

        return $layer->update($data);
    }

    /**
     * Delete layer
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $layer = $this->findById($id);
        
        if (!$layer) {
            return false;
        }

        return $layer->delete();
    }

    /**
     * Get layers by geometry type
     *
     * @param string $type
     * @return Collection
     */
    public function getByGeometryType(string $type): Collection
    {
        return Layer::byGeometryType($type)->get();
    }

    /**
     * Get layers as GeoJSON
     *
     * @return array
     */
    public function getAllAsGeojson(): array
    {
        $layers = DB::select("
            SELECT 
                id,
                name,
                ST_AsGeoJSON(geometry) as geometry,
                created_at,
                updated_at
            FROM geo.layers
            ORDER BY created_at DESC
        ");

        return array_map(function ($layer) {
            return [
                'id' => $layer->id,
                'name' => $layer->name,
                'geometry' => json_decode($layer->geometry),
                'created_at' => $layer->created_at,
                'updated_at' => $layer->updated_at,
            ];
        }, $layers);
    }
}

