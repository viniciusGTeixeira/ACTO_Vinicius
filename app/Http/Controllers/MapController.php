<?php

/**
 * ACTO Maps - Map Controller
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Http\Controllers;

use Illuminate\View\View;

class MapController extends Controller
{
    /**
     * Display the public map
     *
     * @return View
     */
    public function index(): View
    {
        return view('map.index', [
            'arcgisApiKey' => config('services.arcgis.api_key'),
            'arcgisBasemap' => config('services.arcgis.basemap', 'topo-vector'),
            'centerLat' => config('services.arcgis.center_lat', -15.7801),
            'centerLng' => config('services.arcgis.center_lng', -47.9292),
            'zoomLevel' => config('services.arcgis.zoom_level', 4),
        ]);
    }
}

