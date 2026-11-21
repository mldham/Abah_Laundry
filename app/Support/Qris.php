<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Mengubah teks QRIS statis toko menjadi QRIS dinamis yang sudah berisi nominal.
 *
 * Format QRIS mengikuti EMV QRCPS Merchant-Presented Mode: deretan blok "ID(2) + PANJANG(2) + ISI".
 * Yang diubah hanya tiga hal: blok 01 (11 statis jadi 12 dinamis), blok 54 (nominal), dan blok 63 (CRC).
 * Blok pedagang (26 sampai 51), nama toko, dan kota dibiarkan persis seperti aslinya,
 * jadi uangnya tetap masuk ke pedagang pemilik QRIS statis itu.
 */
class Qris
{
    // CRC-16/CCITT-FALSE (poly 0x1021, awal 0xFFFF) dipakai di blok 63. Uji baku: "123456789" menghasilkan 29B1.
    public static function crc16(string $data): string
    {
        $crc = 0xFFFF;

        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $crc ^= ord($data[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    // Empat karakter terakhir adalah CRC dari semua karakter sebelumnya, termasuk "6304".
    public static function sahCrc(string $payload): bool
    {
        $payload = trim($payload);

        return strlen($payload) > 8
            && substr($payload, -8, 4) === '6304'
            && self::crc16(substr($payload, 0, -4)) === strtoupper(substr($payload, -4));
    }

    /**
     * @return array<int, array{0: string, 1: string}> daftar [id, isi] sesuai urutan di teks
     */
    public static function urai(string $payload): array
    {
        $hasil = [];
        $pos = 0;
        $panjang = strlen($payload);

        while ($pos < $panjang) {
            $id = substr($payload, $pos, 2);
            $lenTeks = substr($payload, $pos + 2, 2);

            if (strlen($lenTeks) < 2 || ! ctype_digit($id) || ! ctype_digit($lenTeks)) {
                throw new InvalidArgumentException('Teks QRIS rusak: blok tidak terbaca.');
            }

            $len = (int) $lenTeks;

            if ($pos + 4 + $len > $panjang) {
                throw new InvalidArgumentException('Teks QRIS terpotong.');
            }

            $hasil[] = [$id, substr($payload, $pos + 4, $len)];
            $pos += 4 + $len;
        }

        return $hasil;
    }

    // Isi satu blok tingkat atas, misalnya "59" untuk nama toko. Null kalau tidak ada.
    public static function nilai(string $payload, string $id): ?string
    {
        foreach (self::urai($payload) as [$kode, $isi]) {
            if ($kode === $id) {
                return $isi;
            }
        }

        return null;
    }

    public static function dinamis(string $statis, int $nominal): string
    {
        $statis = trim($statis);

        if ($nominal < 1 || $nominal > 999999999999) {
            throw new InvalidArgumentException('Nominal QRIS harus antara 1 dan 999.999.999.999.');
        }

        if (! self::sahCrc($statis)) {
            throw new InvalidArgumentException('Teks QRIS tidak valid: CRC tidak cocok. Salin ulang teks lengkapnya.');
        }

        $blok = [];
        $adaFormat = false;

        foreach (self::urai($statis) as [$id, $isi]) {
            if ($id === '54' || $id === '63') {
                continue; // nominal lama dibuang, CRC dihitung ulang di bawah
            }

            if ($id === '01') {
                $isi = '12';
                $adaFormat = true;
            }

            $blok[] = [$id, $isi];
        }

        if (! $adaFormat) {
            throw new InvalidArgumentException('Teks QRIS tidak valid: blok 01 tidak ada.');
        }

        // Blok 54 harus berada di antara blok bernomor lebih kecil dan lebih besar darinya.
        $posisi = count($blok);

        foreach ($blok as $i => [$id]) {
            if ((int) $id > 54) {
                $posisi = $i;
                break;
            }
        }

        array_splice($blok, $posisi, 0, [['54', (string) $nominal]]);

        $badan = '';

        foreach ($blok as [$id, $isi]) {
            $badan .= $id.str_pad((string) strlen($isi), 2, '0', STR_PAD_LEFT).$isi;
        }

        $badan .= '6304';

        return $badan.self::crc16($badan);
    }

    // Gambar kode QR sebagai SVG. Butuh paket bacon/bacon-qr-code (composer require bacon/bacon-qr-code).
    public static function svg(string $payload, int $ukuran = 320): string
    {
        $penulis = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle($ukuran),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd(),
            )
        );

        return trim(preg_replace('/^<\?xml[^>]*\?>\s*/', '', $penulis->writeString($payload)));
    }
}
