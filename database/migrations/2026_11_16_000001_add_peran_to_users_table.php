<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Akun lama otomatis jadi operator (hak paling kecil). Naikkan ke admin lewat seeder.
            $table->string('peran', 20)->default('operator')->after('password');
            $table->boolean('aktif')->default(true)->after('peran');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['peran', 'aktif']);
        });
    }
};
