<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Posts
    Route::livewire('posts', 'pages::posts.index')
        ->middleware('can:create posts')
        ->name('posts.index');
    Route::livewire('posts/create', 'pages::posts.create')
        ->middleware('can:create posts')
        ->name('posts.create');
    Route::livewire('posts/edit', 'pages::posts.edit')
        ->name('posts.edit');
    Route::livewire('posts/show/{id}', 'pages::posts.show')->name('posts.show');

    // Users
    Route::livewire('users', 'pages::users.index')->name('users.index');
});

require __DIR__.'/settings.php';
