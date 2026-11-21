<?php

use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminKomplainController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/order');

Route::get('/order', [OrderController::class, 'create'])->name('order.create');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/bantuan', BantuanController::class)->name('bantuan');

// Akun pelanggan. Percobaan masuk dan daftar dibatasi 6 kali per menit per alamat IP.
Route::middleware('guest:pelanggan')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'masuk'])->middleware('throttle:6,1')->name('masuk.proses');
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->middleware('throttle:6,1')->name('daftar.proses');
});

Route::middleware('auth:pelanggan')->group(function () {
    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
    Route::get('/data-order', [OrderController::class, 'index'])->name('order.index');
    Route::delete('/data-order/{pesanan}', [OrderController::class, 'batal'])->name('order.batal');
    Route::get('/data-order/{pesanan}/struk', [OrderController::class, 'struk'])->name('order.struk');
    Route::post('/data-order/{pesanan}/terima', [OrderController::class, 'terima'])->name('order.terima');
    Route::get('/data-order/{pesanan}/komplain', [OrderController::class, 'komplain'])->name('order.komplain');
    Route::post('/data-order/{pesanan}/komplain', [OrderController::class, 'kirimKomplain'])->name('order.komplain.kirim');
    Route::get('/data-order/{pesanan}/bayar', [OrderController::class, 'bayar'])->name('order.bayar');
    Route::post('/data-order/{pesanan}/bayar', [OrderController::class, 'laporBayar'])->name('order.bayar.lapor');
});

// Area admin. Operator boleh mengubah pesanan; hapus pesanan dan audit hanya untuk admin.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/order');

    Route::middleware('guest:admin')->group(function () {
        Route::get('/masuk', [AdminAuthController::class, 'formMasuk'])->name('masuk');
        Route::post('/masuk', [AdminAuthController::class, 'masuk'])->middleware('throttle:admin-masuk')->name('masuk.proses');
    });

    Route::middleware(['auth:admin', 'akun.aktif', 'tanpa-cache'])->group(function () {
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
