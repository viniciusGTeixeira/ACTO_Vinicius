<?php

/**
 * ACTO Maps - User Migration
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auth.users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Two-Factor Authentication fields
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            // WhatsApp 2FA fields
            $table->string('phone_number', 20)->nullable();
            $table->boolean('two_factor_whatsapp_enabled')->default(false);
            
            // GeoIP and Anomaly Detection fields
            $table->string('last_login_ip', 45)->nullable();
            $table->decimal('last_login_latitude', 10, 8)->nullable();
            $table->decimal('last_login_longitude', 11, 8)->nullable();
            $table->string('last_login_country', 2)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('email');
            $table->index('last_login_ip');
            $table->index(['last_login_latitude', 'last_login_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth.users');
    }
};

