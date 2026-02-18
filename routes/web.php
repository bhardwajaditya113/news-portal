<?php

use App\Http\Controllers\Frontend\EnhancedHomeController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\Frontend\LiveFeedController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index']);

/** Enhanced Home Page (Optional - can replace main if needed) */
Route::get('/enhanced', [EnhancedHomeController::class, 'index'])->name('home.enhanced');
Route::get('/trending', [EnhancedHomeController::class, 'trending'])->name('trending');
Route::get('/search', [EnhancedHomeController::class, 'search'])->name('search');
Route::get('/topic/{topic}', [EnhancedHomeController::class, 'byTopic'])->name('news.topic');
Route::get('/source/{sourceSlug}', [EnhancedHomeController::class, 'bySource'])->name('news.source');
Route::post('/preferences', [EnhancedHomeController::class, 'savePreferences'])->name('preferences.save');

/** API Routes for Real-time Features */
Route::get('/api/breaking-news', [EnhancedHomeController::class, 'apiBreakingNews'])->name('api.breaking-news');

/** Live Feed Routes */
Route::get('/live-feed/stream', [LiveFeedController::class, 'stream'])->name('live-feed.stream');
Route::get('/live-feed/latest', [LiveFeedController::class, 'latest'])->name('live-feed.latest');
Route::get('/live-feed/breaking', [LiveFeedController::class, 'breaking'])->name('live-feed.breaking');
Route::get('/live-feed/ticker', [LiveFeedController::class, 'ticker'])->name('live-feed.ticker');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('language', LanguageController::class)->name('language');

/** News Details Routes */
Route::get('news-details/{slug}', [HomeController::class, 'ShowNews'])->name('news-details');

/** News Details Routes */
Route::get('news', [HomeController::class, 'news'])->name('news');

/** News Comment Routes */
Route::post('news-comment', [HomeController::class, 'handleComment'])->name('news-comment');
Route::post('news-comment-replay', [HomeController::class, 'handleReplay'])->name('news-comment-replay');

Route::delete('news-comment-destroy', [HomeController::class, 'commentDestory'])->name('news-comment-destroy');

/** Newsletter Routes */
Route::post('subscribe-newsletter', [HomeController::class, 'SubscribeNewsLetter'])->name('subscribe-newsletter');

/** About Page Route */
Route::get('about', [HomeController::class, 'about'])->name('about');

/** Contact Page Route */
Route::get('contact', [HomeController::class, 'contact'])->name('contact');
/** Contact Page Route */
Route::post('contact', [HomeController::class, 'handleContactFrom'])->name('contact.submit');

