<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.cookie')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/api/classrooms', [ClassroomController::class, 'index']);
    Route::post('/api/classrooms', [ClassroomController::class, 'store']);
    Route::get('/api/classrooms/invite/{token}', [ClassroomController::class, 'showByToken']);
    Route::post('/api/classrooms/invite/{token}/join', [ClassroomController::class, 'join']);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');

// SPA entry for dashboard (Vue Router handles sub-routes client-side).
Route::get('/dashboard/{any?}', function () {
    return view('welcome');
})->where('any', '.*')->name('dashboard');

// Public invite link entry (Vue Router handles preview/join).
Route::get('/class/{token}', function () {
    return view('welcome');
})->name('class.invite');
