<?php

use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/photos', [PhotoController::class, 'store'])->name('photos.store');
Route::post('/photos', [PhotoController::class, 'store']);
Route::get('/share/{uuid}', [PhotoController::class, 'share'])->name('share.show');
Route::post('/share/{uuid}/mail', [PhotoController::class, 'sendEmail'])->name('share.mail');

if (env('VERCEL')) {
    Route::get('/storage/{path}', function ($path) {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            abort(404);
        }
        return response()->file($fullPath);
    })->where('path', '.*');
}
