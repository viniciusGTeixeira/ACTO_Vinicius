<?php

/**
 * ACTO Maps - Security Tables Migration
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
        // Audit Logs
        Schema::create('security.audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('auth.users')->onDelete('set null');
            $table->string('action', 50);
            $table->string('model', 100);
            $table->bigInteger('model_id')->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index(['model', 'model_id']);
            $table->index('created_at');
            $table->index('action');
        });
        
        // Failed Login Attempts
        Schema::create('security.failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            
            $table->index('email');
            $table->index('ip_address');
            $table->index('attempted_at');
        });
        
        // Blacklisted Tokens
        Schema::create('security.blacklisted_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash')->unique();
            $table->foreignId('user_id')->constrained('auth.users')->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->timestamp('blacklisted_at')->useCurrent();
            $table->timestamp('expires_at');
            
            $table->index('token_hash');
            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.blacklisted_tokens');
        Schema::dropIfExists('security.failed_login_attempts');
        Schema::dropIfExists('security.audit_logs');
    }
};

