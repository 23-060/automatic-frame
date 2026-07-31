<?php

use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/photos', [PhotoController::class, 'store'])->name('photos.store');
Route::get('/share/{uuid}', [PhotoController::class, 'share'])->name('share.show');
Route::post('/share/{uuid}/mail', [PhotoController::class, 'sendEmail'])->name('share.mail');
