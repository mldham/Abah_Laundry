<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    public $timestamps = false;
    protected $guarded = [];

    public static function catat(string $aksi, ?string $keterangan = null): void
    {
        if (! Schema::hasTable('log_aktivitas')) {
            return;
        }

        $data = [];
        $user = Auth::guard('admin')->user() ?? Auth::user();

        if (Schema::hasColumn('log_aktivitas', 'user_id')) {
            $data['user_id'] = $user?->id;
        }
        if (Schema::hasColumn('log_aktivitas', 'aksi')) {
            $data['aksi'] = $aksi;
        }
        if (Schema::hasColumn('log_aktivitas', 'keterangan')) {
            $data['keterangan'] = $keterangan;
        }
        if (Schema::hasColumn('log_aktivitas', 'ip_address')) {
            $data['ip_address'] = request()->ip();
        }
        if (Schema::hasColumn('log_aktivitas', 'created_at')) {
            $data['created_at'] = now();
        }

        static::create($data);
    }
}