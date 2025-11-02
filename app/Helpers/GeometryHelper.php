<?php

/**
 * ACTO Maps - Geometry Helper
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Helpers;

class GeometryHelper
{
    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in kilometers
     */
    public static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validate coordinates
     *
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    public static function validateCoordinates(float $lat, float $lng): bool
    {
        return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
    }

    /**
     * Format coordinates for display
     *
     * @param float $lat
     * @param float $lng
     * @param int $precision
     * @return string
     */
    public static function formatCoordinates(float $lat, float $lng, int $precision = 6): string
    {
        return sprintf('%.' . $precision . 'f, %.' . $precision . 'f', $lat, $lng);
    }
}

