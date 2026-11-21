<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Dibuat karena model Mesin (tabel 'mesin') belum punya migration.
     * Dilindungi hasTable() supaya tidak error kalau tabel sudah Anda buat manual.
     */
    public function up(): void
    {
        if (Schema::hasTable('mesin')) {
            return;
        }

        Schema::create('mesin', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('jenis');
            $table->string('merek');
            $table->unsignedInteger('kapasitas')->nullable();
            $table->string('status')->default('Beroperasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesin');
    }
};
