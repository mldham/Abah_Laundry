<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Status bayar terpisah dari status cucian (pending, proses, selesai): cucian bisa selesai sebelum dibayar.
    public function up(): void
    {
        Schema::table('data_pesanan', function (Blueprint $table) {
            $table->string('status_bayar', 20)->default('belum_bayar'); // belum_bayar, menunggu_verifikasi, lunas
            $table->string('metode_bayar', 20)->nullable();             // qris, manual
            $table->dateTime('dibayar_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('data_pesanan', function (Blueprint $table) {
            $table->dropColumn(['status_bayar', 'metode_bayar', 'dibayar_pada']);
        });
    }
};
