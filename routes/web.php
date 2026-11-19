<?php

use App\Http\Controllers\DocController;
use App\Http\Controllers\PhotoController;
use App\Models\Doc;
use App\Models\Photo;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        $photosCount = Photo::query()->count();
        $docsCount = Doc::query()->count();
    } catch (Throwable) {
        $photosCount = 0;
        $docsCount = 0;
    }

    return view('home', [
        'photosCount' => $photosCount,
        'docsCount' => $docsCount,
    ]);
})->name('home');

Route::resource('photos', PhotoController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('docs', DocController::class)->only(['index', 'store', 'update', 'destroy']);
