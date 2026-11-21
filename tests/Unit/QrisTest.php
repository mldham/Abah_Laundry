<?php

namespace Tests\Unit;

use App\Support\Qris;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QrisTest extends TestCase
{
    // QRIS statis buatan sendiri untuk uji (bukan milik pedagang mana pun). Nilai CRC dihitung terpisah.
    public const STATIS = '00020101021151440014ID.CO.QRIS.WWW0215ID10200000000010303UMI5204581253033605802ID5912LAUNDRY ABAH6007BANDUNG61054028762070703A0163046576';
    public const DINAMIS_23000 = '00020101021251440014ID.CO.QRIS.WWW0215ID10200000000010303UMI5204581253033605405230005802ID5912LAUNDRY ABAH6007BANDUNG61054028762070703A01630417C1';

    public function test_crc16_cocok_dengan_uji_baku(): void
    {
        $this->assertSame('29B1', Qris::crc16('123456789'));
    }

    public function test_crc_qris_statis_sah_dan_yang_diubah_tidak_sah(): void
    {
        $this->assertTrue(Qris::sahCrc(self::STATIS));
        $this->assertFalse(Qris::sahCrc(str_replace('LAUNDRY', 'LAUNDRI', self::STATIS)));
    }

    public function test_dinamis_menghasilkan_teks_yang_diharapkan(): void
    {
        $this->assertSame(self::DINAMIS_23000, Qris::dinamis(self::STATIS, 23000));
    }

    public function test_dinamis_mengubah_format_nominal_dan_crc_saja(): void
    {
        $hasil = Qris::dinamis(self::STATIS, 23000);

        $this->assertTrue(Qris::sahCrc($hasil));
        $this->assertSame('12', Qris::nilai($hasil, '01'));
        $this->assertSame('23000', Qris::nilai($hasil, '54'));

        // Data pedagang tidak boleh berubah.
        foreach (['51', '52', '53', '58', '59', '60', '61', '62'] as $id) {
            $this->assertSame(Qris::nilai(self::STATIS, $id), Qris::nilai($hasil, $id), "Blok {$id} berubah.");
        }

        // Blok 54 berada tepat sebelum blok 58, sesuai urutan EMV.
        $ids = array_column(Qris::urai($hasil), 0);
        $this->assertSame(['00', '01', '51', '52', '53', '54', '58', '59', '60', '61', '62', '63'], $ids);
    }

    public function test_nominal_lama_diganti_bukan_ditambah(): void
    {
        $hasil = Qris::dinamis(Qris::dinamis(self::STATIS, 1000), 5000);

        $this->assertSame('5000', Qris::nilai($hasil, '54'));
        $this->assertCount(1, array_filter(Qris::urai($hasil), fn ($b) => $b[0] === '54'));
        $this->assertTrue(Qris::sahCrc($hasil));
    }

    public function test_nama_toko_terbaca(): void
    {
        $this->assertSame('LAUNDRY ABAH', Qris::nilai(self::STATIS, '59'));
        $this->assertNull(Qris::nilai(self::STATIS, '99'));
    }

    public function test_nominal_nol_atau_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Qris::dinamis(self::STATIS, 0);
    }

    public function test_teks_dengan_crc_salah_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Qris::dinamis(substr(self::STATIS, 0, -4).'0000', 10000);
    }

    public function test_teks_terpotong_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Qris::urai('0002010102');
    }
}
