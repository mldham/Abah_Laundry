<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LogAktivitas extends Model
{
    public const UPDATED_AT = null;

    public const LABEL = [
        'admin.masuk'                  => 'Admin masuk',
        'admin.masuk_gagal'            => 'Percobaan masuk gagal',
        'admin.keluar'                 => 'Admin keluar',
        'admin.sesi_dihentikan'        => 'Sesi dihentikan',
        'akses.ditolak'                => 'Akses ditolak',
        'pesanan.diubah'               => 'Pesanan diubah',
        'pesanan.dihapus'              => 'Pesanan dihapus',
        'pesanan.dibatalkan_pelanggan' => 'Pesanan dibatalkan pelanggan',
        'pesanan.diterima_pelanggan'   => 'Pelanggan konfirmasi cucian diterima',
        'komplain.dibuat'              => 'Pelanggan mengirim komplain',
        'komplain.ditanggapi'          => 'Komplain ditanggapi',
        'pembayaran.dilaporkan'        => 'Pelanggan melapor sudah bayar',
        'pembayaran.dikonfirmasi'      => 'Pembayaran dikonfirmasi lunas',
        'pembayaran.dibatalkan'        => 'Status lunas dibatalkan',
    ];

    protected $table = 'log_aktivitas';

    protected $guarded = [];

    protected $casts = [
        'detail' => 'array',
    ];

    protected static function booted(): void
    {
        // Jejak audit hanya bertambah. Mengubah atau menghapusnya lewat aplikasi dibatalkan.
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    // Pelaku default-nya admin yang sedang masuk, hanya untuk request ke /admin. Di luar itu kosong,
    // supaya aksi pelanggan tidak tercatat atas nama admin yang kebetulan masuk di browser yang sama.
    public static function catat(
        string $aksi,
        ?Model $subjek = null,
        array $detail = [],
        ?User $pelaku = null,
        ?string $email = null,
    ): self {
        $request = request();
        $pelaku ??= $request->is('admin', 'admin/*') ? auth('admin')->user() : null;

        return self::create([
            'id_user'      => $pelaku?->getKey(),
            'email_dicoba' => $email !== null ? Str::limit($email, 255, '') : null,
            'aksi'         => $aksi,
            'subjek'       => $subjek ? class_basename($subjek) : null,
            'id_subjek'    => $subjek?->getKey(),
            'detail'       => $detail ?: null,
            'ip_address'   => $request->ip(),
            'user_agent'   => Str::limit((string) $request->userAgent(), 255, ''),
        ]);
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getLabelAttribute(): string
    {
        return self::LABEL[$this->aksi] ?? $this->aksi;
    }

    public function getRingkasanAttribute(): string
    {
        $detail = $this->detail ?? [];

        if (isset($detail['sebelum'], $detail['sesudah'])) {
            return collect($detail['sesudah'])
                ->map(fn ($baru, $kolom) => $kolom.': '.($detail['sebelum'][$kolom] ?? '-').' ke '.$baru)
                ->implode('; ');
        }

        return collect($detail)
            ->map(fn ($nilai, $kunci) => $kunci.': '.(is_array($nilai) ? implode('/', $nilai) : $nilai))
            ->implode(', ');
    }
}
