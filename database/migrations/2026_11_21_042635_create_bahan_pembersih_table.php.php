<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_pembersih', function (Blueprint $table) {
            $table->id('id_bahan');
            $table->foreignId('id_supplier')->constrained('supplier', 'id_supplier')->onDelete('cascade');
            $table->string('nama_bahan');
            $table->string('kategori');
            $table->string('merek')->nullable();
            $table->integer('stok')->default(0);
            $table->string('satuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_pembersih');
    }
};