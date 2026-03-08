<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::welcome')->name('home');

Route::livewire('/tasks/{task}', 'pages::tasks.show')
    ->middleware('signed')
    ->name('tasks.show');
