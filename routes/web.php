<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'registerCreate'])->name('admin.register');
    Route::post('/register', [AuthController::class, 'registerStore'])->name('admin.register.submit');
    Route::prefix('admin')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('admin.login.submit'); 
    });
});


Route::middleware('auth')->group(function () {

    Route::get('/dashboard',[DashboardController::class,'index'])->name('admin.dashboard');
    Route::get('/importer',[DashboardController::class,'importerIndex'])->name('admin.importer.index');
    Route::post('logout', [AuthController::class, 'destroy'])->name('admin.logout');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile-info-uodate', [ProfileController::class, 'update'])->name('profile.info.update');
    Route::post('/profile-password-update', [ProfileController::class, 'updatePass'])->name('profile.password.update');


    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->name('users.index');
        Route::post('/users/update/{id}', 'update')->name('users.update');
        Route::get('/users/status/{id}', 'status')->name('users.status');
        Route::delete('/users/delete/{id}', 'destroy')->name('users.delete');

    });
});


