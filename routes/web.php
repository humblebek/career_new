<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Student dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Test routes
    Route::prefix('test')->name('test.')->group(function () {
        Route::post('/start/{careerTest}', [TestController::class, 'start'])->name('start');
        Route::get('/take/{testAttempt}', [TestController::class, 'take'])->name('take');
        Route::post('/submit/{testAttempt}', [TestController::class, 'submitAnswer'])->name('submit');
        Route::get('/result/{testAttempt}', [ResultController::class, 'show'])->name('result');
        Route::get('/history', [ResultController::class, 'history'])->name('history');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        // Career tests management
        Route::get('/tests', [AdminController::class, 'tests'])->name('tests');
        Route::get('/tests/create', [AdminController::class, 'createTest'])->name('tests.create');
        Route::post('/tests', [AdminController::class, 'storeTest'])->name('tests.store');
        Route::get('/tests/{careerTest}/edit', [AdminController::class, 'editTest'])->name('tests.edit');
        Route::put('/tests/{careerTest}', [AdminController::class, 'updateTest'])->name('tests.update');
        Route::delete('/tests/{careerTest}', [AdminController::class, 'destroyTest'])->name('tests.destroy');
        // Questions management
        Route::get('/tests/{careerTest}/questions', [AdminController::class, 'questions'])->name('tests.questions');
        Route::get('/tests/{careerTest}/questions/create', [AdminController::class, 'createQuestion'])->name('questions.create');
        Route::post('/tests/{careerTest}/questions', [AdminController::class, 'storeQuestion'])->name('questions.store');
        Route::get('/questions/{question}/edit', [AdminController::class, 'editQuestion'])->name('questions.edit');
        Route::put('/questions/{question}', [AdminController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{question}', [AdminController::class, 'destroyQuestion'])->name('questions.destroy');
    });
});
