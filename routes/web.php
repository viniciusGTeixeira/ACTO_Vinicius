<?php

/**
 * ACTO Maps - Web Routes
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

// Public map route
Route::get('/', [MapController::class, 'index'])->name('map.index');
