<?php

/**
 * ACTO Maps - Valid GeoJSON File Rule
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ValidGeojsonFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Log::info('[GEOJSON_VALIDATION] Starting GeoJSON file validation', [
            'attribute' => $attribute,
            'file_type' => get_class($value),
        ]);

        if (!$value instanceof UploadedFile) {
            Log::warning('[GEOJSON_VALIDATION] Invalid file type received', [
                'received_type' => get_class($value),
            ]);
            $fail('O arquivo não é válido.');
            return;
        }

        Log::info('[GEOJSON_VALIDATION] File received successfully', [
            'original_name' => $value->getClientOriginalName(),
            'size' => $value->getSize(),
            'mime_type' => $value->getMimeType(),
            'temp_path' => $value->getRealPath(),
        ]);

        $content = file_get_contents($value->getRealPath());
        Log::info('[GEOJSON_VALIDATION] File content read', [
            'content_length' => strlen($content),
        ]);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('[GEOJSON_VALIDATION] Invalid JSON format', [
                'error' => json_last_error_msg(),
            ]);
            $fail('O arquivo não é um JSON válido.');
            return;
        }

        Log::info('[GEOJSON_VALIDATION] JSON parsed successfully');

        if (!isset($data['type'])) {
            Log::error('[GEOJSON_VALIDATION] Missing type field in GeoJSON');
            $fail('O GeoJSON deve ter um campo "type".');
            return;
        }

        $validTypes = [
            'Point', 
            'LineString', 
            'Polygon', 
            'MultiPoint', 
            'MultiLineString', 
            'MultiPolygon', 
            'Feature', 
            'FeatureCollection', 
            'GeometryCollection'
        ];

        if (!in_array($data['type'], $validTypes)) {
            Log::error('[GEOJSON_VALIDATION] Invalid geometry type', [
                'type' => $data['type'],
                'valid_types' => $validTypes,
            ]);
            $fail('O tipo de geometria não é válido.');
            return;
        }

        Log::info('[GEOJSON_VALIDATION] Geometry type validated', [
            'type' => $data['type'],
        ]);

        if ($data['type'] === 'Feature' && !isset($data['geometry'])) {
            Log::error('[GEOJSON_VALIDATION] Feature missing geometry field');
            $fail('Feature GeoJSON deve conter um campo "geometry".');
            return;
        }

        if ($data['type'] === 'FeatureCollection' && !isset($data['features'])) {
            Log::error('[GEOJSON_VALIDATION] FeatureCollection missing features field');
            $fail('FeatureCollection GeoJSON deve conter um campo "features".');
            return;
        }

        Log::info('[GEOJSON_VALIDATION] Validation completed successfully', [
            'type' => $data['type'],
        ]);
    }
}

