<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\QrisTest;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['layanan.qris.statis' => QrisTest::STATIS]);
    }

    private function akun(string $telepon = '081234567890'): Pelanggan
    {
        return Pelanggan::create(['nama' => 'Budi', 'no_telepon' => $telepon, 'password' => 'rahasia123']);
    }

    // Kiloan 3 kg x Rp7.000 = Rp21.000, ongkir Rp2.000, jadi yang dibayar Rp23.000.
    private function pesanan(Pelanggan $akun, array $ubah = []): Pesanan
    {
        return $akun->pesanan()->create(array_merge([
            'layanan' => 'Laundry Kiloan', 'harga' => 7000, 'no_telepon' => $akun->no_telepon,
            'tanggal_pickup' => now()->addDay()->toDateString(), 'alamat' => 'Jl. Contoh',
            'ongkir' => 2000, 'total_berat' => 3, 'total_harga' => 21000,
        ], $ubah));
    }

    private function perluPustakaQr(): void
    {
        if (! class_exists(\BaconQrCode\Writer::class)) {
            $this->markTestSkipped('Jalankan: composer require bacon/bacon-qr-code');
        }
    }

    // ---------- pelanggan ----------

    public function test_total_bayar_adalah_harga_ditambah_ongkir(): void
    {
        $p = $this->pesanan($this->akun());

        $this->assertSame(23000, $p->total_bayar);
        $this->assertSame(0, $this->pesanan($this->akun('081111111111'), ['total_harga' => 0, 'total_berat' => 0])->total_bayar);
    }

    public function test_tombol_bayar_hanya_muncul_setelah_total_ada(): void
    {
        $akun = $this->akun();
        $this->pesanan($akun, ['total_harga' => 0, 'total_berat' => 0]);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertOk()->assertDontSee('Bayar dengan QRIS');

        $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertOk()->assertSee('Bayar dengan QRIS');
    }

    public function test_halaman_bayar_menampilkan_nominal_dan_kode_qr(): void
    {
        $this->perluPustakaQr();
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')
            ->get(route('order.bayar', $p))
            ->assertOk()
            ->assertSee('Rp23.000')
            ->assertSee('Penerima: LAUNDRY ABAH')
            ->assertSee('<svg', false);
    }

    public function test_tanpa_qris_statis_halaman_bayar_memberi_tahu(): void
    {
        config(['layanan.qris.statis' => null]);
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')
            ->get(route('order.bayar', $p))
            ->assertOk()
            ->assertSee('QRIS belum diaktifkan')
            ->assertDontSee('Saya sudah bayar');
    }

    public function test_pesanan_yang_belum_ditimbang_belum_bisa_dibayar(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['total_harga' => 0, 'total_berat' => 0]);

        $this->actingAs($akun, 'pelanggan')->get(route('order.bayar', $p))->assertRedirect(route('order.index'));
        $this->actingAs($akun, 'pelanggan')->post(route('order.bayar.lapor', $p))->assertRedirect(route('order.index'));

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status_bayar' => 'belum_bayar']);
    }

    public function test_pesanan_orang_lain_dijawab_404(): void
    {
        $p = $this->pesanan($this->akun());
        $lain = $this->akun('085555555555');

        $this->actingAs($lain, 'pelanggan')->get(route('order.bayar', $p))->assertNotFound();
        $this->actingAs($lain, 'pelanggan')->post(route('order.bayar.lapor', $p))->assertNotFound();
        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status_bayar' => 'belum_bayar']);
    }

    public function test_tamu_dialihkan_ke_masuk(): void
    {
        $p = $this->pesanan($this->akun());

        $this->get(route('order.bayar', $p))->assertRedirect(route('masuk'));
        $this->post(route('order.bayar.lapor', $p))->assertRedirect(route('masuk'));
    }

    public function test_melapor_sudah_bayar_mengubah_status_dan_tercatat(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')
            ->post(route('order.bayar.lapor', $p))
            ->assertRedirect(route('order.index'))
            ->assertSessionHas('bayar.berhasil', true);

        $this->assertDatabaseHas('data_pesanan', [
            'id_pesanan' => $p->id_pesanan, 'status_bayar' => 'menunggu_verifikasi', 'metode_bayar' => 'qris',
        ]);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'pembayaran.dilaporkan', 'id_subjek' => $p->id_pesanan]);
    }

    public function test_melapor_dua_kali_hanya_tercatat_sekali(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')->post(route('order.bayar.lapor', $p));
        $this->actingAs($akun, 'pelanggan')->post(route('order.bayar.lapor', $p));

        $this->assertSame(1, LogAktivitas::where('aksi', 'pembayaran.dilaporkan')->count());
    }

    public function test_kode_tidak_ditampilkan_lagi_setelah_dilaporkan(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['status_bayar' => 'menunggu_verifikasi']);

        $this->actingAs($akun, 'pelanggan')
            ->get(route('order.bayar', $p))
            ->assertOk()
            ->assertSee('Pembayaran sedang dicek admin')
            ->assertDontSee('Saya sudah bayar')
            ->assertDontSee('<svg', false);
    }

    public function test_pesanan_lunas_tidak_bisa_dilaporkan_lagi(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['status_bayar' => 'lunas']);

        $this->actingAs($akun, 'pelanggan')->post(route('order.bayar.lapor', $p))->assertSessionHas('bayar.berhasil', false);
        $this->assertSame(0, LogAktivitas::where('aksi', 'pembayaran.dilaporkan')->count());
    }

    // ---------- admin ----------

    public function test_admin_menandai_lunas_dan_tercatat(): void
    {
        $p = $this->pesanan($this->akun(), ['status_bayar' => 'menunggu_verifikasi', 'metode_bayar' => 'qris']);

        $this->sebagaiAdmin()
            ->put(route('admin.order.bayar', $p), ['status_bayar' => 'lunas'])
            ->assertRedirect();

        $p->refresh();
        $this->assertSame('lunas', $p->status_bayar);
        $this->assertSame('qris', $p->metode_bayar);
        $this->assertNotNull($p->dibayar_pada);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'pembayaran.dikonfirmasi', 'id_subjek' => $p->id_pesanan]);
    }

    public function test_lunas_tanpa_laporan_pelanggan_dicatat_sebagai_manual(): void
    {
        $p = $this->pesanan($this->akun());

        $this->sebagaiAdmin(User::PERAN_OPERATOR)->put(route('admin.order.bayar', $p), ['status_bayar' => 'lunas']);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status_bayar' => 'lunas', 'metode_bayar' => 'manual']);
    }

    public function test_admin_membatalkan_lunas(): void
    {
        $p = $this->pesanan($this->akun(), ['status_bayar' => 'lunas', 'metode_bayar' => 'qris', 'dibayar_pada' => now()]);

        $this->sebagaiAdmin()->put(route('admin.order.bayar', $p), ['status_bayar' => 'belum_bayar']);

        $p->refresh();
        $this->assertSame('belum_bayar', $p->status_bayar);
        $this->assertNull($p->metode_bayar);
        $this->assertNull($p->dibayar_pada);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'pembayaran.dibatalkan', 'id_subjek' => $p->id_pesanan]);
    }

    public function test_pesanan_tanpa_total_tidak_bisa_dilunasi(): void
    {
        $p = $this->pesanan($this->akun(), ['total_harga' => 0, 'total_berat' => 0]);

        $this->sebagaiAdmin()
            ->put(route('admin.order.bayar', $p), ['status_bayar' => 'lunas'])
            ->assertSessionHasErrors('status_bayar_'.$p->id_pesanan);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status_bayar' => 'belum_bayar']);
    }

    public function test_nilai_status_bayar_yang_aneh_ditolak(): void
    {
        $p = $this->pesanan($this->akun());

        $this->sebagaiAdmin()->put(route('admin.order.bayar', $p), ['status_bayar' => 'menunggu_verifikasi'])->assertStatus(422);
        $this->sebagaiAdmin()->put(route('admin.order.bayar', $p), [])->assertStatus(422);
    }

    public function test_tamu_dan_pelanggan_tidak_bisa_menandai_lunas(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->put(route('admin.order.bayar', $p), ['status_bayar' => 'lunas'])->assertRedirect(route('admin.masuk'));
        $this->actingAs($akun, 'pelanggan')->put(route('admin.order.bayar', $p), ['status_bayar' => 'lunas'])->assertRedirect(route('admin.masuk'));

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status_bayar' => 'belum_bayar']);
    }

    public function test_berat_pesanan_lunas_tidak_bisa_diubah(): void
    {
        $p = $this->pesanan($this->akun(), ['status_bayar' => 'lunas']);
        $id = $p->id_pesanan;

        $this->sebagaiAdmin()
            ->put("/admin/order/{$id}", ['total_berat_'.$id => 5, 'status_'.$id => 'proses'])
            ->assertSessionHasErrors('total_berat_'.$id);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $id, 'total_berat' => 3, 'total_harga' => 21000]);
    }

    public function test_mengubah_status_cucian_pesanan_lunas_tetap_boleh(): void
    {
        $p = $this->pesanan($this->akun(), ['status_bayar' => 'lunas']);
        $id = $p->id_pesanan;

        $this->sebagaiAdmin()->put("/admin/order/{$id}", ['total_berat_'.$id => 3, 'status_'.$id => 'selesai']);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $id, 'status' => 'selesai', 'status_bayar' => 'lunas']);
    }

    public function test_total_berubah_membatalkan_laporan_bayar(): void
    {
        $p = $this->pesanan($this->akun(), ['status_bayar' => 'menunggu_verifikasi', 'metode_bayar' => 'qris']);
        $id = $p->id_pesanan;

        $this->sebagaiAdmin()->put("/admin/order/{$id}", ['total_berat_'.$id => 4, 'status_'.$id => 'proses']);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $id, 'total_harga' => 28000, 'status_bayar' => 'belum_bayar']);
    }

    public function test_halaman_admin_menampilkan_kolom_pembayaran(): void
    {
        $this->pesanan($this->akun(), ['status_bayar' => 'menunggu_verifikasi']);

        $this->sebagaiAdmin()
            ->get('/admin/order')
            ->assertOk()
            ->assertSee('Pembayaran')
            ->assertSee('Menunggu dicek admin')
            ->assertSee('Tandai lunas');
    }
}
