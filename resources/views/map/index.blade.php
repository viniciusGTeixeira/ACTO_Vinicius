@extends('layouts.app')

@section('title', 'Mapa Público - ACTO Maps')

@push('styles')
<style>
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
    }
    
    #map-container {
        width: 100%;
        height: 100%;
        position: relative;
    }
    
    #viewDiv {
        width: 100%;
        height: 100%;
    }
    
    .map-header {
        position: absolute;
        top: 15px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        background: rgba(255, 255, 255, 0.95);
        padding: 15px 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .map-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }
    
    .layer-toggle {
        position: absolute;
        top: 100px;
        right: 15px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.95);
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-height: 400px;
        overflow-y: auto;
        min-width: 250px;
    }
    
    .layer-toggle h5 {
        margin: 0 0 15px 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #00c853;
        padding-bottom: 8px;
    }
    
    .layer-item {
        margin-bottom: 10px;
        padding: 8px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    .layer-item:hover {
        background-color: #f8f9fa;
    }
    
    .layer-item label {
        cursor: pointer;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .layer-item input[type="checkbox"] {
        cursor: pointer;
    }
    
    .layer-color-indicator {
        width: 20px;
        height: 12px;
        border-radius: 2px;
        display: inline-block;
        border: 1px solid #dee2e6;
    }
    
    .layer-color-polygon {
        background-color: rgba(255, 165, 0, 0.5);
        border-color: rgb(255, 140, 0);
    }
    
    .layer-color-linestring {
        background-color: rgb(0, 123, 255);
        border-color: rgb(0, 123, 255);
    }
    
    .layer-color-point {
        background-color: rgb(0, 200, 83);
        border-color: rgb(0, 200, 83);
        border-radius: 50%;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .loading-overlay.hidden {
        display: none;
    }
    
    .spinner-border.text-primary {
        color: #00c853 !important;
        border-color: currentColor transparent currentColor transparent;
    }
</style>
@endpush

@section('content')
<div id="map-container">
    <div class="map-header">
        <h1>ACTO Maps - Visualização Pública</h1>
    </div>
    
    <div class="layer-toggle">
        <h5>Camadas Disponíveis</h5>
        <div id="layers-list">
            <p class="text-muted">Carregando camadas...</p>
        </div>
    </div>
    
    <div id="viewDiv"></div>
    
    <div class="loading-overlay" id="loading">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3">Carregando mapa...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Map configuration from server
    const mapConfig = {
        apiKey: "{{ $arcgisApiKey }}",
        basemap: "{{ $arcgisBasemap }}",
        center: {
            lng: {{ $centerLng }},
            lat: {{ $centerLat }}
        },
        zoom: {{ $zoomLevel }}
    };
</script>

<script>
    // @ts-nocheck
    /* eslint-disable */
    require([
        "esri/config",
        "esri/Map",
        "esri/views/MapView",
        "esri/layers/GraphicsLayer",
        "esri/Graphic",
        "esri/geometry/Polygon",
        "esri/geometry/Polyline",
        "esri/geometry/Point",
        "esri/symbols/SimpleMarkerSymbol",
        "esri/symbols/SimpleLineSymbol",
        "esri/symbols/SimpleFillSymbol"
    ], function(esriConfig, Map, MapView, GraphicsLayer, Graphic, Polygon, Polyline, Point, SimpleMarkerSymbol, SimpleLineSymbol, SimpleFillSymbol) {
        
        // Configure ArcGIS API Key
        if (mapConfig.apiKey && mapConfig.apiKey !== '') {
            esriConfig.apiKey = mapConfig.apiKey;
            console.log('ArcGIS API Key configured');
        } else {
            console.warn('No ArcGIS API Key - using basic basemap');
        }
        
        // Create map with fallback basemap (osm doesn't require API key)
        const map = new Map({
            basemap: 'osm' // OpenStreetMap - works without API key
        });
        
        console.log('Map created with OSM basemap (no API key required)');
        
        // Create view
        const view = new MapView({
            container: "viewDiv",
            map: map,
            center: [mapConfig.center.lng, mapConfig.center.lat],
            zoom: mapConfig.zoom
        });
        
        // Store layers
        const layersMap = new Map();
        
        // Load layers from API
        $.ajax({
            url: '/api/layers',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    renderLayersList(response.data);
                    loadLayersOnMap(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load layers:', error);
                $('#layers-list').html('<p class="text-danger">Erro ao carregar camadas</p>');
            }
        });
        
        function renderLayersList(layers) {
            if (layers.length === 0) {
                $('#layers-list').html('<p class="text-muted">Nenhuma camada disponível</p>');
                return;
            }
            
            let html = '';
            layers.forEach(layer => {
                // Determine geometry type for color indicator
                let colorClass = 'layer-color-point'; // default
                if (layer.geometry_type) {
                    const geomType = layer.geometry_type.toUpperCase();
                    if (geomType.includes('POLYGON')) {
                        colorClass = 'layer-color-polygon';
                    } else if (geomType.includes('LINE')) {
                        colorClass = 'layer-color-linestring';
                    }
                }
                
                html += `
                    <div class="layer-item">
                        <label class="form-check-label">
                            <input type="checkbox" 
                                   class="form-check-input layer-checkbox" 
                                   data-layer-id="${layer.id}" 
                                   checked>
                            <span class="layer-color-indicator ${colorClass}"></span>
                            <span>${layer.name}</span>
                        </label>
                    </div>
                `;
            });
            
            $('#layers-list').html(html);
            
            // Add event listeners
            $('.layer-checkbox').on('change', function() {
                const layerId = $(this).data('layer-id');
                const geoJsonLayer = layersMap.get(layerId);
                
                if (geoJsonLayer) {
                    geoJsonLayer.visible = $(this).is(':checked');
                }
            });
        }
        
        function loadLayersOnMap(layers) {
            const promises = layers.map(layer => {
                return $.ajax({
                    url: `/api/layers/${layer.id}/geojson`,
                    method: 'GET'
                }).then(geojsonData => {
                    console.log('GeoJSON data received for layer:', layer.name, geojsonData);
                    
                    // Create graphics layer (without title to avoid g.includes error)
                    const graphicsLayer = new GraphicsLayer();
                    
                    // Convert GeoJSON geometry to ArcGIS geometry
                    let esriGeometry;
                    const geomType = geojsonData.geometry.type.toUpperCase();
                    
                    console.log('Converting geometry type:', geomType);
                    
                    if (geomType.includes('POLYGON')) {
                        // Create Polygon from GeoJSON coordinates
                        esriGeometry = new Polygon({
                            rings: geojsonData.geometry.coordinates,
                            spatialReference: { wkid: 4326 }
                        });
                    } else if (geomType.includes('LINE')) {
                        // Create Polyline from GeoJSON coordinates
                        esriGeometry = new Polyline({
                            paths: geojsonData.geometry.coordinates,
                            spatialReference: { wkid: 4326 }
                        });
                    } else if (geomType.includes('POINT')) {
                        // Create Point from GeoJSON coordinates
                        esriGeometry = new Point({
                            longitude: geojsonData.geometry.coordinates[0],
                            latitude: geojsonData.geometry.coordinates[1],
                            spatialReference: { wkid: 4326 }
                        });
                    }
                    
                    console.log('Esri geometry created:', esriGeometry);
                    
                    // Get symbol based on geometry type
                    const symbol = getSymbolForGeometry(geojsonData.geometry.type);
                    
                    // Create graphic with Esri geometry
                    const graphic = new Graphic({
                        geometry: esriGeometry,
                        symbol: symbol,
                        attributes: {
                            id: layer.id,
                            name: layer.name
                        }
                    });
                    
                    console.log('Graphic created successfully');
                    
                    // Add graphic to layer
                    graphicsLayer.add(graphic);
                    
                    // Store layer reference
                    layersMap.set(layer.id, graphicsLayer);
                    
                    // Add to map
                    map.add(graphicsLayer);
                    
                    console.log('Layer added successfully:', layer.name);
                    
                    return graphicsLayer;
                }).catch(error => {
                    console.error(`Failed to load layer ${layer.name}:`, error);
                    return null;
                });
            });
            
            Promise.all(promises).then((loadedLayers) => {
                console.log('All layers loaded:', loadedLayers);
                
                // Auto-zoom to show all layers
                const validLayers = loadedLayers.filter(l => l);
                console.log('Valid layers for zoom:', validLayers.length);
                
                if (validLayers.length > 0) {
                    // Wait for layers to load, then zoom to their extent
                    console.log('Waiting for layers to be ready...');
                    
                    Promise.all(validLayers.map(layer => layer.when())).then(() => {
                        console.log('Layers are ready, calculating extent...');
                        
                        // Use first layer's fullExtent to zoom
                        const firstLayer = validLayers[0];
                        
                        firstLayer.queryExtent().then((result) => {
                            console.log('Layer extent:', result.extent);
                            
                            if (result.extent) {
                                view.goTo(result.extent.expand(1.5)).then(() => {
                                    console.log('Zoom completed successfully');
                                }).catch(err => {
                                    console.error('Error during goTo:', err);
                                });
                            } else {
                                console.warn('No extent available for layer');
                            }
                        }).catch(err => {
                            console.error('Error querying extent:', err);
                        });
                    }).catch(err => {
                        console.error('Error waiting for layers:', err);
                    });
                }
                
                $('#loading').addClass('hidden');
            }).catch(error => {
                console.error('Error loading layers:', error);
                $('#loading').addClass('hidden');
            });
        }
        
        function getSymbolForGeometry(geometryType) {
            let symbol;
            
            const type = geometryType.toUpperCase();
            console.log('Getting symbol for geometry type:', type);
            
            if (type.includes('POINT')) {
                symbol = new SimpleMarkerSymbol({
                    color: [0, 200, 83, 1],
                    size: 10,
                    outline: new SimpleLineSymbol({
                        color: [255, 255, 255],
                        width: 2
                    })
                });
            } else if (type.includes('LINE')) {
                symbol = new SimpleLineSymbol({
                    color: [0, 123, 255, 1],
                    width: 3
                });
            } else if (type.includes('POLYGON')) {
                symbol = new SimpleFillSymbol({
                    color: [255, 165, 0, 0.5],
                    outline: new SimpleLineSymbol({
                        color: [255, 140, 0],
                        width: 2
                    })
                });
            } else {
                // Default
                symbol = new SimpleMarkerSymbol();
            }
            
            console.log('Symbol created:', symbol);
            return symbol;
        }
    });
    /* eslint-enable */
</script>
@endpush

