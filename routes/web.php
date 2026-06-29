<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassroomChatController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassroomPostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.cookie')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/api/classrooms', [ClassroomController::class, 'index']);
    Route::post('/api/classrooms', [ClassroomController::class, 'store']);
    Route::get('/api/notifications', [ClassroomController::class, 'notifications']);
    Route::post('/api/notifications/clear', [ClassroomController::class, 'clearNotifications']);
    Route::post('/api/notifications/read', [ClassroomController::class, 'readNotification']);
    Route::get('/api/users', [ProfileController::class, 'search']);
    Route::get('/api/classrooms/invite/{token}', [ClassroomController::class, 'showByToken']);
    Route::post('/api/classrooms/invite/{token}/join', [ClassroomController::class, 'join']);
    Route::get('/api/classrooms/{token}', [ClassroomController::class, 'show']);
    Route::put('/api/classrooms/{token}', [ClassroomController::class, 'update']);
    Route::post('/api/classrooms/{token}/archive', [ClassroomController::class, 'archive']);
    Route::post('/api/classrooms/{token}/members/{userId}/approve', [ClassroomController::class, 'approveMember']);
    Route::post('/api/classrooms/{token}/members/{userId}/co-teacher', [ClassroomController::class, 'assignCoTeacher']);
    Route::post('/api/classrooms/{token}/members/{userId}/award', [ClassroomController::class, 'giveAward']);
    Route::delete('/api/classrooms/{token}/members/{userId}', [ClassroomController::class, 'removeMember']);
    Route::post('/api/classrooms/{token}/leave', [ClassroomController::class, 'leave']);
    Route::get('/api/users/{id}', [ProfileController::class, 'show']);
    Route::put('/api/profile', [ProfileController::class, 'update']);
    Route::post('/api/users/{id}/friend', [ProfileController::class, 'addFriend']);
    Route::post('/api/users/{id}/friend/accept', [ProfileController::class, 'accept']);
    Route::post('/api/users/{id}/friend/decline', [ProfileController::class, 'decline']);
    Route::post('/api/users/{id}/friend/cancel', [ProfileController::class, 'cancel']);
    Route::post('/api/users/{id}/friend/unfriend', [ProfileController::class, 'unfriend']);

    Route::get('/api/classrooms/{token}/posts', [ClassroomPostController::class, 'index']);
    Route::post('/api/classrooms/{token}/posts', [ClassroomPostController::class, 'store']);
    Route::post('/api/classrooms/{token}/posts/{postId}/like', [ClassroomPostController::class, 'like']);
    Route::post('/api/classrooms/{token}/posts/{postId}/comments', [ClassroomPostController::class, 'comment']);
    Route::post('/api/classrooms/{token}/posts/{postId}/comments/{commentId}/like', [ClassroomPostController::class, 'likeComment']);
    Route::post('/api/classrooms/{token}/posts/{postId}/toggle-comments', [ClassroomPostController::class, 'toggleComments']);
    Route::post('/api/classrooms/{token}/posts/{postId}/hide', [ClassroomPostController::class, 'hide']);
    Route::post('/api/classrooms/{token}/posts/{postId}/pin', [ClassroomPostController::class, 'pin']);

    Route::get('/api/classrooms/{token}/messages', [ClassroomChatController::class, 'index']);
    Route::post('/api/classrooms/{token}/messages', [ClassroomChatController::class, 'store']);

    Route::get('/api/conversations', [ChatController::class, 'conversations']);
    Route::get('/api/conversations/with/{userId}', [ChatController::class, 'open']);
    Route::post('/api/conversations/{id}/messages', [ChatController::class, 'send']);
    Route::post('/api/conversations/{id}/nickname', [ChatController::class, 'nickname']);
    Route::delete('/api/conversations/{id}', [ChatController::class, 'destroy']);
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
