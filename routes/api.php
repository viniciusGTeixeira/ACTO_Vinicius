<?php

/**
 * ACTO Maps - API Routes
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

use App\Http\Controllers\Api\LayerController;
use Illuminate\Support\Facades\Route;

// Public API routes (with rate limiting)
Route::middleware(['throttle:public_api'])->group(function () {
    // Get all layers
    Route::get('/layers', [LayerController::class, 'index'])->name('api.layers.index');
    
    // Get single layer
    Route::get('/layers/{id}', [LayerController::class, 'show'])->name('api.layers.show');
    
    // Get all layers as GeoJSON FeatureCollection
    Route::get('/layers/geojson/all', [LayerController::class, 'geojson'])->name('api.layers.geojson');
    
    // Get single layer as GeoJSON Feature
    Route::get('/layers/{id}/geojson', [LayerController::class, 'geojsonSingle'])->name('api.layers.geojson.single');
});

// Authenticated API routes (require Sanctum token)
Route::middleware(['auth:sanctum', 'throttle:authenticated_api'])->group(function () {
    // Future authenticated endpoints
});

