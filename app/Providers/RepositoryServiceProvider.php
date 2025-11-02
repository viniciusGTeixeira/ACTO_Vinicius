<?php

/**
 * ACTO Maps - Repository Service Provider
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Providers;

use App\Contracts\Repositories\LayerRepositoryInterface;
use App\Contracts\Services\LayerServiceInterface;
use App\Repositories\LayerRepository;
use App\Services\LayerService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(LayerRepositoryInterface::class, LayerRepository::class);
        
        // Service bindings
        $this->app->bind(LayerServiceInterface::class, LayerService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

