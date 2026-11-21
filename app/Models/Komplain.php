<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komplain extends Model
{
    // Pilihan masalah untuk pelanggan. Kuncinya yang disimpan di database.
    public const JENIS = [
        'kurang_bersih' => 'Cucian kurang bersih',
        'barang_hilang' => 'Barang hilang atau kurang',
        'barang_rusak'  => 'Barang rusak atau luntur',
        'lainnya'       => 'Masalah lain',
    ];

    // Satu baris penjelas di bawah tiap pilihan, supaya pelanggan tidak ragu memilih.
    public const KETERANGAN_JENIS = [
        'kurang_bersih' => 'Masih ada noda, bau, atau sisa sabun.',
        'barang_hilang' => 'Ada pakaian yang tidak kembali, atau jumlahnya kurang.',
        'barang_rusak'  => 'Sobek, luntur, menyusut, atau kancing lepas.',
        'lainnya'       => 'Selain tiga di atas, misalnya salah alamat atau telat diantar.',
    ];

    public const STATUS = [
        'baru'     => 'Baru',
        'diproses' => 'Sedang ditangani',
        'selesai'  => 'Selesai',
    ];

    protected $table = 'komplain';

    protected $primaryKey = 'id_komplain';

    protected $fillable = [
        'id_pesanan', 'id_pelanggan', 'jenis', 'deskripsi', 'status', 'tanggapan', 'id_user', 'selesai_pada',
    ];

    protected $casts = [
        'selesai_pada' => 'datetime',
    ];

    public function scopeTerbuka(Builder $query): Builder
    {
        return $query->whereIn('status', ['baru', 'diproses']);
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function penanggap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function terbuka(): bool
    {
        return $this->status !== 'selesai';
    }
}
