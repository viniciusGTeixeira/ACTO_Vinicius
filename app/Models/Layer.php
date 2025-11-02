<?php

/**
 * ACTO Maps - Layer Model
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Layer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geo.layers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'geometry',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'geometry_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Set geometry from GeoJSON
     *
     * @param string $geojson
     * @return void
     */
    public function setGeometryFromGeojson(string $geojson): void
    {
        $this->geometry = DB::raw("ST_GeomFromGeoJSON('" . addslashes($geojson) . "')");
    }

    /**
     * Get geometry as GeoJSON
     *
     * @return string|null
     */
    public function getGeometryAsGeojson(): ?string
    {
        if (!$this->geometry) {
            return null;
        }

        $result = DB::selectOne(
            "SELECT ST_AsGeoJSON(geometry) as geojson FROM geo.layers WHERE id = ?",
            [$this->id]
        );

        return $result->geojson ?? null;
    }

    /**
     * Scope to filter by geometry type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGeometryType($query, string $type)
    {
        return $query->whereRaw("GeometryType(geometry) = ?", [strtoupper($type)]);
    }

    /**
     * Get the geometry type
     *
     * @return string|null
     */
    public function getGeometryTypeAttribute(): ?string
    {
        if (!$this->id) {
            return null;
        }

        $result = DB::selectOne(
            "SELECT GeometryType(geometry) as type FROM geo.layers WHERE id = ?",
            [$this->id]
        );

        return $result->type ?? null;
    }
}

