<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AdminAuthenticationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdvancedDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FooterGridOneController;
use App\Http\Controllers\Admin\FooterGridThreeController;
use App\Http\Controllers\Admin\FooterGridTwoController;
use App\Http\Controllers\Admin\FooterInfoController;
use App\Http\Controllers\Admin\HomeSectionSettingController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\NewsAggregatorController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RealtimeFeedController;
use App\Http\Controllers\Admin\RolePermisionController;
use App\Http\Controllers\Admin\RoleUserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SocialCountController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Models\FooterGridOne;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'admin', 'as' => 'admin.'], function(){

    Route::get('login', [AdminAuthenticationController::class, 'login'])->name('login');
    Route::post('login', [AdminAuthenticationController::class, 'handleLogin'])->name('handle-login');
    Route::post('logout', [AdminAuthenticationController::class, 'logout'])->name('logout');

    /** Reset passeord */
    Route::get('forgot-password', [AdminAuthenticationController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [AdminAuthenticationController::class, 'sendResetLink'])->name('forgot-password.send');

    Route::get('reset-password/{token}', [AdminAuthenticationController::class, 'resetPassword'])->name('reset-password');
    Route::post('reset-password', [AdminAuthenticationController::class, 'handleResetPassword'])->name('reset-password.send');


});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function(){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /** Advanced Dashboard Routes */
    Route::get('advanced-dashboard', [AdvancedDashboardController::class, 'index'])->name('dashboard.advanced');
    Route::get('dashboard/realtime', [AdvancedDashboardController::class, 'realtime'])->name('dashboard.realtime');
    Route::get('dashboard/chart-data', [AdvancedDashboardController::class, 'chartData'])->name('dashboard.chart-data');
    Route::get('dashboard/demographics', [AdvancedDashboardController::class, 'demographics'])->name('dashboard.demographics');
    Route::get('dashboard/content', [AdvancedDashboardController::class, 'content'])->name('dashboard.content');
    Route::get('dashboard/sources', [AdvancedDashboardController::class, 'sources'])->name('dashboard.sources');
    Route::get('dashboard/trending', [AdvancedDashboardController::class, 'trending'])->name('dashboard.trending');
    Route::get('dashboard/export', [AdvancedDashboardController::class, 'export'])->name('dashboard.export');

    /** News Aggregator Routes */
    Route::get('aggregator', [NewsAggregatorController::class, 'index'])->name('aggregator.index');
    Route::get('aggregator/create', [NewsAggregatorController::class, 'create'])->name('aggregator.create');
    Route::post('aggregator', [NewsAggregatorController::class, 'store'])->name('aggregator.store');
    Route::get('aggregator/{id}/edit', [NewsAggregatorController::class, 'edit'])->name('aggregator.edit');
    Route::put('aggregator/{id}', [NewsAggregatorController::class, 'update'])->name('aggregator.update');
    Route::delete('aggregator/{id}', [NewsAggregatorController::class, 'destroy'])->name('aggregator.destroy');
    Route::get('aggregator/{id}/fetch', [NewsAggregatorController::class, 'fetchSource'])->name('aggregator.fetch');
    Route::get('aggregator/fetch-all', [NewsAggregatorController::class, 'fetchAll'])->name('aggregator.fetch-all');
    Route::get('aggregator/initialize', [NewsAggregatorController::class, 'initializeDefaults'])->name('aggregator.initialize');
    Route::post('aggregator/{id}/toggle', [NewsAggregatorController::class, 'toggleStatus'])->name('aggregator.toggle');
    Route::get('aggregator/news', [NewsAggregatorController::class, 'aggregatedNews'])->name('aggregator.news');
    Route::post('aggregator/news/status', [NewsAggregatorController::class, 'updateNewsStatus'])->name('aggregator.news.status');

    /** Real-Time Feed Control Routes */
    Route::get('realtime-feed', [RealtimeFeedController::class, 'index'])->name('realtime-feed.index');
    Route::post('realtime-feed/start', [RealtimeFeedController::class, 'start'])->name('realtime-feed.start');
    Route::post('realtime-feed/stop', [RealtimeFeedController::class, 'stop'])->name('realtime-feed.stop');
    Route::get('realtime-feed/status', [RealtimeFeedController::class, 'status'])->name('realtime-feed.status');
    Route::post('realtime-feed/cycle', [RealtimeFeedController::class, 'cycle'])->name('realtime-feed.cycle');
    Route::get('realtime-feed/stream', [RealtimeFeedController::class, 'stream'])->name('realtime-feed.stream');
    Route::get('realtime-feed/latest', [RealtimeFeedController::class, 'latestNews'])->name('realtime-feed.latest');
    Route::post('realtime-feed/fetch/{source}', [RealtimeFeedController::class, 'fetchSource'])->name('realtime-feed.fetch-source');
    Route::post('realtime-feed/toggle/{source}', [RealtimeFeedController::class, 'toggleSource'])->name('realtime-feed.toggle-source');
    Route::post('realtime-feed/reset', [RealtimeFeedController::class, 'reset'])->name('realtime-feed.reset');

    /**Profile Routes */
    Route::put('profile-password-update/{id}', [ ProfileController::class, 'passwordUpdate'])->name('profile-password.update');
    Route::resource('profile', ProfileController::class);

    /** Language Route */
    Route::resource('language', LanguageController::class);

    /** Category Route */
    Route::resource('category', CategoryController::class);

    /** News Route */
    Route::get('fetch-news-category', [NewsController::class, 'fetchCategory'])->name('fetch-news-category');
    Route::get('toggle-news-status', [NewsController::class, 'toggleNewsStatus'])->name('toggle-news-status');
    Route::get('news-copy/{id}', [NewsController::class, 'copyNews'])->name('news-copy');
    Route::get('pending-news', [NewsController::class, 'pendingNews'])->name('pending.news');
    Route::put('approve-news', [NewsController::class, 'approveNews'])->name('approve.news');

    Route::resource('news', NewsController::class);

    /** Home Section Setting Route */
    Route::get('home-section-setting', [HomeSectionSettingController::class, 'index'])->name('home-section-setting.index');
    Route::put('home-section-setting', [HomeSectionSettingController::class, 'update'])->name('home-section-setting.update');

    /** Social Count Route */
    Route::resource('social-count', SocialCountController::class);

    /** Ad Route */
    Route::resource('ad', AdController::class);

    /** Subscriber Route */
    Route::resource('subscribers', SubscriberController::class);

    /** Social links Route */
    Route::resource('social-link', SocialLinkController::class);

    /** Footer Info Route */
    Route::resource('footer-info', FooterInfoController::class);

    /** Footer Grid One Route */
    Route::post('footer-grid-one-title', [FooterGridOneController::class, 'handleTitle'])->name('footer-grid-one-title');
    Route::resource('footer-grid-one', FooterGridOneController::class);

    /** Footer Grid Two Route */
    Route::post('footer-grid-two-title', [FooterGridTwoController::class, 'handleTitle'])->name('footer-grid-two-title');
    Route::resource('footer-grid-two', FooterGridTwoController::class);

    /** Footer Grid Two Route */
    Route::post('footer-grid-three-title', [FooterGridThreeController::class, 'handleTitle'])->name('footer-grid-three-title');
    Route::resource('footer-grid-three', FooterGridThreeController::class);

    /** About page Route */
    Route::get('about', [AboutController::class, 'index'])->name('about.index');
    Route::put('about', [AboutController::class, 'update'])->name('about.update');

    /** Contact page Route */
    Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
    Route::put('contact', [ContactController::class, 'update'])->name('contact.update');

    /** Contact Message Route */
    Route::get('contact-message', [ContactMessageController::class, 'index'])->name('contact-message.index');
    Route::post('contact-send-replay', [ContactMessageController::class, 'sendReplay'])->name('contact.send-replay');

    /** Settings Routes */
    Route::get('setting', [SettingController::class, 'index'])->name('setting.index');
    /** Settings Routes */
    Route::put('general-setting', [SettingController::class, 'updateGeneralSetting'])->name('general-setting.update');
    Route::put('seo-setting', [SettingController::class, 'updateSeoSetting'])->name('seo-setting.update');
    Route::put('appearance-setting', [SettingController::class, 'updateAppearanceSetting'])->name('appearance-setting.update');
    Route::put('microsoft-api-setting', [SettingController::class, 'updateMicrosoftApiSetting'])->name('microsoft-api-setting.update');

    /** Role and Permissions Routes */
    Route::get('role', [RolePermisionController::class, 'index'])->name('role.index');
    Route::get('role/create', [RolePermisionController::class, 'create'])->name('role.create');
    Route::post('role/create', [RolePermisionController::class, 'store'])->name('role.store');
    Route::get('role/{id}/edit', [RolePermisionController::class, 'edit'])->name('role.edit');
    Route::put('role/{id}/edit', [RolePermisionController::class, 'update'])->name('role.update');
    Route::delete('role/{id}/destory', [RolePermisionController::class, 'destory'])->name('role.destory');

    /** Admin User Routes */
    Route::resource('role-users', RoleUserController::class);

    /** Localization Routes */
    Route::get('admin-localization', [LocalizationController::class, 'adminIndex'])->name('admin-localization.index');
    Route::get('frontend-localization', [LocalizationController::class, 'frontnedIndex'])->name('frontend-localization.index');

    Route::post('extract-localize-string', [LocalizationController::class, 'extractLocalizationStrings'])->name('extract-localize-string');

    Route::post('update-lang-string', [LocalizationController::class, 'updateLangString'])->name('update-lang-string');


    Route::post('translate-string', [LocalizationController::class, 'translateString'])->name('translate-string');

});


