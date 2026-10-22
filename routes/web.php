<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDebtorController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DebtorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
        Route::get('/duznici', [AdminDebtorController::class, 'index'])->name('debtors.index');
        Route::get('/duznici/{debtor}/izmena', [AdminDebtorController::class, 'edit'])->name('debtors.edit');
        Route::put('/duznici/{debtor}', [AdminDebtorController::class, 'update'])->name('debtors.update');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/novo', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
    });

    Route::get('/prodavac', function () {
        return view('vendor.dashboard');
    })->name('vendor.dashboard');

    Route::get('/duznici', [DebtorController::class, 'index'])->name('debtors.index');
    Route::get('/duznici/novo', [DebtorController::class, 'create'])->name('debtors.create');
    Route::post('/duznici', [DebtorController::class, 'store'])->name('debtors.store');
    Route::get('/duznici/{debtor}', [DebtorController::class, 'show'])->name('debtors.show');
    Route::post('/duznici/{debtor}/uplate', [DebtorController::class, 'storePayment'])->name('debtors.payments.store');
    Route::post('/duznici/{debtor}/uplate/storno', [DebtorController::class, 'voidPayment'])
        ->middleware('admin')
        ->name('debtors.payments.void');
});
