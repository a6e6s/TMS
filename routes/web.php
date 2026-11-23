<?php

use App\Http\Controllers\SetAdminLanguageController;
use App\Http\Controllers\TicketScreenshotController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::post('/admin/language', SetAdminLanguageController::class)
    ->name('admin.language');

Route::get('/ticket-screenshots/{ticket}/{screenshot}', TicketScreenshotController::class)
    ->middleware('signed')
    ->name('tickets.screenshot');
