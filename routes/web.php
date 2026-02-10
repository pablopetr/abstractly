<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\DigestController;

Route::get('/', fn() => redirect()->route('disciplines.index'));

Route::get('/disciplines', [DisciplineController::class, 'index'])->name('disciplines.index');
Route::post('/disciplines', [DisciplineController::class, 'update'])->name('disciplines.update');

Route::get('/disciplines/{slug}', [DisciplineController::class, 'show'])->name('disciplines.show');
Route::post('/disciplines/{slug}/sources', [DisciplineController::class, 'updateSources'])->name('disciplines.sources.update');

Route::get('/disciplines/{slug}/sources/{key}/preview', [SourceController::class, 'preview'])->name('sources.preview');

Route::post('/digest/generate', [DigestController::class, 'generate'])->name('digest.generate');
Route::get('/digest', [DigestController::class, 'show'])->name('digest.show');
