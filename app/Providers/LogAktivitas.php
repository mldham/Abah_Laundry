<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    public static function catat(string $aksi, array $detail = []): void
    {
        // Dibiarkan kosong sementara sampai tabel dan modul audit/log di-merge
    }
}