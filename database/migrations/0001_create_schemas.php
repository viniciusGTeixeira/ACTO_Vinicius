<?php

/**
 * ACTO Maps - Database Schemas
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable PostGIS extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        
        // Create schemas
        DB::statement('CREATE SCHEMA IF NOT EXISTS auth');
        DB::statement('CREATE SCHEMA IF NOT EXISTS geo');
        DB::statement('CREATE SCHEMA IF NOT EXISTS security');
        DB::statement('CREATE SCHEMA IF NOT EXISTS storage');
        
        // Set search path to include all schemas
        DB::statement("ALTER DATABASE laravel SET search_path TO public, auth, geo, security, storage");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS storage CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS security CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS geo CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS auth CASCADE');
    }
};

