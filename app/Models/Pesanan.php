<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    public const STATUS = ['pending', 'proses', 'selesai', 'diantar', 'diterima'];

    // Yang boleh dipilih admin. Diterima hanya bisa datang dari pelanggan (tombol di Cek Pesanan),
    // jadi admin tidak punya pilihan itu kecuali pesanannya memang sudah berstatus diterima.
    public const STATUS_ADMIN = ['pending', 'proses', 'selesai', 'diantar'];

    // Tulisan untuk pelanggan. Nilai di database tetap pending, proses, selesai.
    public const LABEL_STATUS = [
        'pending' => 'Menunggu dijemput',
        'proses'  => 'Sedang dikerjakan',
        'selesai' => 'Selesai dicuci',
        'diantar' => 'Sedang diantar',
        'diterima' => 'Diterima',
    ];

    // Penjelasan satu kalimat per tahap, ditampilkan di Cek Pesanan dan Bantuan.
    public const PENJELASAN_STATUS = [
        'pending' => 'Kami menjemput cuciannya sesuai jadwal. Total harga dihitung setelah cucian ditimbang atau dihitung jumlahnya.',
        'proses'  => 'Cuciannya sedang dikerjakan.',
        'selesai' => 'Cuciannya sudah bersih dan menunggu diantar ke alamatmu.',
        'diantar' => 'Cuciannya dalam perjalanan ke alamatmu. Setelah sampai, tekan "Cucian sudah diterima".',
        'diterima' => 'Cuciannya sudah kamu terima. Terima kasih sudah memakai Laundry Abah.',
    ];

    public const STATUS_BAYAR = ['belum_bayar', 'menunggu_verifikasi', 'lunas'];

    public const LABEL_BAYAR = [
        'belum_bayar'         => 'Belum dibayar',
        'menunggu_verifikasi' => 'Menunggu dicek admin',
        'lunas'               => 'Lunas',
    ];

    public const LABEL_METODE = [
        'qris'   => 'QRIS',
        'manual' => 'Dicatat admin',
    ];

    protected $table = 'data_pesanan';
    protected $primaryKey = 'id_pesanan';
    public $timestamps = false;

    protected $attributes = [
        'status_bayar' => 'belum_bayar',
    ];

    protected $fillable = [
        'id_pelanggan', 'layanan', 'harga', 'no_telepon', 'tanggal_pickup',
        'alamat', 'notes', 'ongkir', 'total_berat', 'total_harga', 'status',
        'status_bayar', 'metode_bayar', 'dibayar_pada', 'diantar_pada', 'diterima_pada',
    ];

    protected $casts = [
        'tanggal_pickup' => 'date',
        'dibayar_pada'   => 'datetime',
        'diantar_pada'   => 'datetime',
        'diterima_pada'  => 'datetime',
    ];

    // Urutan tahap (0, 1, 2) untuk penanda progres.
    public function getTahapAttribute(): int
    {
        $i = array_search($this->status, self::STATUS, true);

        return $i === false ? 0 : $i;
    }

    // Satuan mengikuti layanan yang dipesan (kg, pasang, pcs), supaya "3 kg" tidak muncul untuk sepatu.
    public function getSatuanAttribute(): string
    {
        $info = collect(config('layanan.daftar'))->firstWhere('nama', $this->layanan);

        return $info['satuan'] ?? 'unit';
    }

    public function getLabelJumlahAttribute(): string
    {
        return $this->satuan === 'kg' ? 'Berat' : 'Jumlah';
    }

    // Yang dibayar pelanggan: harga cucian ditambah ongkir. Nol selama cucian belum ditimbang.
    public function getTotalBayarAttribute(): int
    {
        return $this->total_harga > 0 ? (int) $this->total_harga + (int) $this->ongkir : 0;
    }

    public function getLabelBayarAttribute(): string
    {
        return self::LABEL_BAYAR[$this->status_bayar] ?? (string) $this->status_bayar;
    }

    public function getLabelMetodeAttribute(): ?string
    {
        return $this->metode_bayar ? (self::LABEL_METODE[$this->metode_bayar] ?? $this->metode_bayar) : null;
    }

    // Bisa dibayar begitu totalnya ada dan belum lunas.
    public function bisaDibayar(): bool
    {
        return $this->total_harga > 0 && $this->status_bayar !== 'lunas';
    }

    // Pelanggan hanya boleh membatalkan sebelum cuciannya dijemput.
    public function bisaDibatalkan(): bool
    {
        return $this->status === 'pending';
    }

    // Terbaru di atas. Di halaman daftar, muat dengan with('komplain') supaya tidak satu query per baris.
    public function komplain(): HasMany
    {
        return $this->hasMany(Komplain::class, 'id_pesanan', 'id_pesanan')->orderByDesc('id_komplain');
    }

    public function punyaKomplainTerbuka(): bool
    {
        return $this->komplain->contains(fn (Komplain $k) => $k->terbuka());
    }

    // Masalah baru bisa dilaporkan setelah cucian ada di tangan (diantar) atau baru disadari sesudah
    // diterima. Sebelum itu belum ada yang bisa dikomplain. Satu komplain terbuka per pesanan: yang
    // sebelumnya harus selesai dulu, supaya admin tidak menerima laporan ganda untuk hal yang sama.
    public function bisaDikomplain(): bool
    {
        return in_array($this->status, ['diantar', 'diterima'], true) && ! $this->punyaKomplainTerbuka();
    }

    // Pelanggan baru boleh menekan "Cucian sudah diterima" setelah admin menandai cucian sedang diantar.
    public function bisaDiterima(): bool
    {
        return $this->status === 'diantar';
    }

    // Pilihan status di halaman admin. Pesanan yang sudah diterima menampilkan status itu apa adanya.
    public function statusUntukAdmin(): array
    {
        return $this->status === 'diterima' ? self::STATUS : self::STATUS_ADMIN;
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
