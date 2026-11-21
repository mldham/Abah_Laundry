<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jika tabel `pelanggan` lama sudah ada, dibiarkan apa adanya
        if (Schema::hasTable('pelanggan')) {
            return;
        }
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id('id_pelanggan');
            $table->string('nama', 100);
            $table->string('no_telepon', 20)->unique();
            $table->text('alamat');
            $table->string('catatan', 500)->nullable();
        });
    }

    public function down(): void
    {
        // Data lama sengaja tidak dihapus
    }
};
