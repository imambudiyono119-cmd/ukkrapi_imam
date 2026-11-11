<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingReportController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index'])->name('home');
Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar.public');
Route::get('/api/events', [CalendarController::class, 'api'])->name('api.events');
Route::get('/api/holidays', [CalendarController::class, 'holidays'])->name('api.holidays');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.submit');
    Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token?}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/events', [EventController::class, 'store'])
        ->middleware('role:Waka/Admin,Guru,Organisasi,Guru/Organisasi')
        ->name('events.store');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.updateStatus');
    Route::get('/complaints/{complaint}/escalate', [ComplaintController::class, 'escalate'])->name('complaints.escalate');
});

Route::middleware(['auth', 'role:Waka/Admin'])->group(function () {
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::patch('/events/{event}/decision', [EventController::class, 'decide'])->name('events.decision');
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
    Route::post('/master-data', [MasterDataController::class, 'store'])->name('master-data.store');
    Route::patch('/master-data/{user}/email', [MasterDataController::class, 'updateEmail'])->name('master-data.email');
    Route::patch('/master-data/{user}/role', [MasterDataController::class, 'updateRole'])->name('master-data.role');
    Route::patch('/master-data/{user}/password', [MasterDataController::class, 'updatePassword'])->name('master-data.password');
    Route::get('/laporan/peminjaman/harian', [BookingReportController::class, 'daily'])->name('reports.daily');
    Route::get('/laporan/peminjaman/bulanan', [BookingReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/admin', fn () => redirect()->route('dashboard'));
});

Route::middleware(['auth', 'role:PIC Ruang'])->group(function () {
    Route::get('/pic', fn () => redirect()->route('dashboard'));
});

Route::middleware(['auth', 'role:Guru,Organisasi,Guru/Organisasi'])->group(function () {
    Route::get('/guru', fn () => redirect()->route('dashboard'));
});
