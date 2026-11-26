<?php

use App\Http\Controllers\PublicPages;
use App\Support\PublicUrl;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPages::class, 'home'])->name('home');
Route::get('/o-kancelarii', [PublicPages::class, 'about'])->name('about');
Route::get('/specjalizacje', [PublicPages::class, 'services'])->name('services.index');
Route::get('/specjalizacje/{slug}', [PublicPages::class, 'service'])->name('services.show');
Route::get('/zespol', [PublicPages::class, 'team'])->name('team');
Route::get('/kariera', [PublicPages::class, 'career'])->name('career');
Route::get('/aktualnosci', [PublicPages::class, 'legacyArchive'])->name('articles.legacy');
Route::get('/artykuly', [PublicPages::class, 'articles'])->name('articles.index');
Route::get('/kontakt', [PublicPages::class, 'contact'])->name('contact');
Route::get('/dokumenty', [PublicPages::class, 'documents'])->name('documents.index');
Route::get('/dokumenty/{slug}', [PublicPages::class, 'document'])->name('documents.show');

Route::get('/{slug}', [PublicPages::class, 'article'])
    ->where('slug', '(?!(?:'.implode('|', PublicUrl::RESERVED_SLUGS).')(?:/|$))[a-z0-9-]+')
    ->name('articles.show');

Route::get('/{path}', [PublicPages::class, 'notFound'])->where('path', '.*')->fallback();
