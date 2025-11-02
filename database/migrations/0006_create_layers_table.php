<?php

/**
 * ACTO Maps - Layers Migration
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('geo.layers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            // geometry field will be added with raw SQL after table creation
            $table->timestamps();
            
            $table->index('name');
        });
        
        // Add geometry column with PostGIS (SRID 4326 - WGS 84)
        DB::statement('ALTER TABLE geo.layers ADD COLUMN geometry geometry(Geometry, 4326)');
        
        // Create spatial index for performance
        DB::statement('CREATE INDEX layers_geometry_idx ON geo.layers USING GIST (geometry)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo.layers');
    }
};

