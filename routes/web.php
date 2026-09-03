<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategorySetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\YoutubeJsonTrackerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    if (! $request->user()->categoria_principal) {
        return to_route('category.setup');
    }

    return app(DashboardController::class)->index($request);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/configuracion-inicial', [CategorySetupController::class, 'edit'])->name('category.setup');
    Route::post('/configuracion-inicial', [CategorySetupController::class, 'update'])->name('category.setup.update');
    Route::get('/videos/related-search', [YoutubeJsonTrackerController::class, 'relatedSearch'])->name('videos.related-search')->middleware('throttle:60,1');
    Route::resource('videos', YoutubeJsonTrackerController::class)->except('show')->middleware('throttle:60,1');
    Route::get('/videos/{video}/details', [YoutubeJsonTrackerController::class, 'show'])->name('videos.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
