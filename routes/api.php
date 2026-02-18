<?php

use App\Http\Controllers\Api\NewsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| News API Routes (Public)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Breaking news (real-time)
    Route::get('breaking-news', [NewsApiController::class, 'breakingNews']);
    
    // Trending
    Route::get('trending/topics', [NewsApiController::class, 'trendingTopics']);
    Route::get('trending/news', [NewsApiController::class, 'trendingNews']);
    
    // News feed
    Route::get('news/latest', [NewsApiController::class, 'latestNews']);
    Route::get('news/sources', [NewsApiController::class, 'sources']);
    Route::get('news/search', [NewsApiController::class, 'search']);
    
    // Analytics tracking
    Route::post('track', [NewsApiController::class, 'trackEvent']);
});

/*
|--------------------------------------------------------------------------
| Admin API Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin')->group(function () {
    // Real-time stats for dashboard
    Route::get('realtime-stats', [NewsApiController::class, 'realtimeStats']);
});

