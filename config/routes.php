<?php

use App\Controllers\HomeController;
use App\Controllers\AuthenticationController;
use Core\Router\Route;

// Authentication
Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/login', [AuthenticationController::class, 'login'])->name('login-screen');
