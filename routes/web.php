<?php

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

    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->middleware('admin')->name('admin.dashboard');

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
