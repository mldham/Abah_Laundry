<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    public static function catat(string $aksi, ?string $email = null, mixed ...$detail): void
    {
        // Placeholder sementara agar proses login, gagal login, dan logout berjalan normal tanpa error
    }
}