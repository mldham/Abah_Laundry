<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminKomplainController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\OrderController;

Route::redirect('/', '/bahan');

Route::middleware('guest:pelanggan')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'masuk'])->middleware('throttle:6,1')->name('masuk.proses');
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->middleware('throttle:6,1')->name('daftar.proses');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/bahan');

    Route::middleware('guest:admin')->group(function () {
        Route::get('/masuk', [AdminAuthController::class, 'formMasuk'])->name('masuk');
        Route::post('/masuk', [AdminAuthController::class, 'masuk'])->middleware('throttle:admin-masuk')->name('masuk.proses');
    });

    Route::middleware(['auth:admin'])->group(function () {
        Route::post('/keluar', [AdminAuthController::class, 'keluar'])->name('keluar');

        Route::middleware('peran:admin,operator')->group(function () {
            Route::get('/order', [AdminOrderController::class, 'index'])->name('order.index');
            Route::get('/order/{pesanan}/struk', [AdminOrderController::class, 'struk'])->middleware('can:view,pesanan')->name('order.struk');
            Route::put('/order/{pesanan}', [AdminOrderController::class, 'update'])->middleware('can:update,pesanan')->name('order.update');
            Route::put('/order/{pesanan}/bayar', [AdminOrderController::class, 'bayar'])->middleware('can:update,pesanan')->name('order.bayar');
            Route::get('/komplain', [AdminKomplainController::class, 'index'])->name('komplain.index');
            Route::put('/komplain/{komplain}', [AdminKomplainController::class, 'update'])->name('komplain.update');
            Route::delete('/order/{pesanan}', [AdminOrderController::class, 'destroy'])->middleware('can:delete,pesanan')->name('order.destroy');
        });

        Route::get('/audit', [AdminAuditController::class, 'index'])->middleware('peran:admin')->name('audit');
    });
});

// Manajemen Bahan Pembersih
Route::middleware(['auth:admin'])
    ->prefix('bahan')
    ->name('bahan.')
    ->group(function () {
        // Admin & Operator: Lihat, Tambah, dan Edit
        Route::middleware('peran:admin,operator')->group(function () {
            Route::get('/', [BahanController::class, 'index'])->name('index');
            Route::get('/tambah', [BahanController::class, 'create'])->name('create');
            Route::post('/tambah', [BahanController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [BahanController::class, 'edit'])->name('edit');
            Route::put('/edit/{id}', [BahanController::class, 'update'])->name('update');
        });

        // Khusus Admin: Hapus data bahan
        Route::middleware('peran:admin')->group(function () {
            Route::delete('/hapus/{id}', [BahanController::class, 'destroy'])->name('destroy');
        });
    });