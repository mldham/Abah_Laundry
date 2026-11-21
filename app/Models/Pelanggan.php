<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelanggan extends Authenticatable
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;

    protected $fillable = ['nama', 'no_telepon', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_pelanggan', 'id_pelanggan');
    }

    // "+62 812-3456-7890" dan "0812 3456 7890" sama-sama menjadi "081234567890".
    // Dipakai saat daftar, masuk, dan order supaya nomor selalu cocok saat dicari.
    public static function normalisasiTelepon(string $nomor): string
    {
        $angka = preg_replace('/\D+/', '', $nomor);

        return str_starts_with($angka, '62') ? '0'.substr($angka, 2) : $angka;
    }
}
