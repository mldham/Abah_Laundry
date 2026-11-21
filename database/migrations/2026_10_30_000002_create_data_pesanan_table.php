<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->foreignId('id_pelanggan')
                ->constrained('pelanggan', 'id_pelanggan')
                ->cascadeOnDelete();
            $table->string('layanan', 50);
            $table->unsignedInteger('harga');                       // harga satuan saat dipesan
            $table->string('no_telepon', 20);
            $table->date('tanggal_pickup');
            $table->text('alamat');
            $table->text('notes')->nullable();
            $table->unsignedInteger('ongkir')->default(0);
            $table->unsignedInteger('total_berat')->default(0);     // diisi admin setelah ditimbang
            $table->unsignedInteger('total_harga')->default(0);     // diisi admin
            $table->string('status', 20)->default('pending');      // pending, proses, selesai
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pesanan');
    }
};
