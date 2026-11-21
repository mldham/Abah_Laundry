<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Null untuk pelanggan yang order tanpa akun (baris lama dan order tamu).
            $table->string('password')->nullable()->after('no_telepon');
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token']);
        });
    }
};
