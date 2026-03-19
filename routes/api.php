<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Symfony\Component\HttpKernel\Profiler\Profile;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//Rutas para Auth
Route::post('/register',[AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Grupo de rutas para Task
Route::middleware(['auth:sanctum', 'verified'])->group(function(){
    //ListAll, Create, Read, Update, Delete
    Route::get('/tasks',[TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function(){
     //Ruta para el perfil del usuario
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/avatar', [ProfileController::class, 'saveAvatar']);
    Route::post('/sendEmailVerificationNotification', [ProfileController::class, 'sendEmailVerificationNotification']);
});

//Ruta para verficacion de email
Route::get('/email/verify/{id}/{hash}', [ProfileController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');


