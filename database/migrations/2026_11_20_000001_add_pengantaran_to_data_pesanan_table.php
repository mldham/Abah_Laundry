<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Dua tahap baru setelah selesai: diantar (diatur admin) dan diterima (dikonfirmasi pelanggan).
    // Kolom status sudah string, jadi nilai baru tidak butuh perubahan di sana. Dua kolom ini mencatat kapan.
    public function up(): void
    {
        Schema::table('data_pesanan', function (Blueprint $table) {
            $table->dateTime('diantar_pada')->nullable();
            $table->dateTime('diterima_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('data_pesanan', function (Blueprint $table) {
            $table->dropColumn(['diantar_pada', 'diterima_pada']);
        });
    }
};
