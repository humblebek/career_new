<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/privacy-policy', fn () => view('privacy-policy'))->name('privacy-policy');

// Guest-only authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Secret word 2FA verification (no auth middleware — user is not logged in yet)
Route::get('/secret-word-verify', [AuthController::class, 'showSecretWordForm'])->name('secret-word.verify');
Route::post('/secret-word-verify', [AuthController::class, 'verifySecretWord'])->name('secret-word.check')->middleware('throttle:secret-word');

// Logout (auth only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {

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
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
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
