<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pelanggan')) {
            throw new \RuntimeException('Tabel pelanggan belum ada. Buat atau impor tabel pelanggan terlebih dahulu.');
        }

        $tambahPassword = ! Schema::hasColumn('pelanggan', 'password');
        $tambahRememberToken = ! Schema::hasColumn('pelanggan', 'remember_token');

        if (! $tambahPassword && ! $tambahRememberToken) {
            return;
        }

        Schema::table('pelanggan', function (Blueprint $table) use ($tambahPassword, $tambahRememberToken) {
            if ($tambahPassword) {
                $table->string('password')->nullable();
            }

            if ($tambahRememberToken) {
                $table->rememberToken();
            }
        });
    }

    public function down(): void
    {
    }
};
