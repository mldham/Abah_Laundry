<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Komplain pelanggan atas satu pesanan (cucian kurang bersih, barang hilang, dan sebagainya).
    // Satu pesanan boleh punya beberapa komplain dari waktu ke waktu, tapi hanya satu yang terbuka sekaligus
    // (aturan itu dijaga di aplikasi, bukan di database).
    public function up(): void
    {
        Schema::create('komplain', function (Blueprint $table) {
            $table->id('id_komplain');
            $table->foreignId('id_pesanan')->constrained('data_pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->foreignId('id_pelanggan')->constrained('pelanggan', 'id_pelanggan')->cascadeOnDelete();
            $table->string('jenis', 20);                          // kurang_bersih, barang_hilang, barang_rusak, lainnya
            $table->text('deskripsi');
            $table->string('status', 20)->default('baru')->index(); // baru, diproses, selesai
            $table->text('tanggapan')->nullable();                // jawaban admin yang dibaca pelanggan
            $table->foreignId('id_user')->nullable()->constrained('users')->nullOnDelete(); // admin yang terakhir menanggapi
            $table->dateTime('selesai_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komplain');
    }
};
