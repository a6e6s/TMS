<?php

use App\Http\Controllers\SetAdminLanguageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::post('/admin/language', SetAdminLanguageController::class)
    ->name('admin.language');
