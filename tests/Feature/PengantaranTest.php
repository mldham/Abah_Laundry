<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengantaranTest extends TestCase
{
    use RefreshDatabase;

    private function akun(string $telepon = '081234567890'): Pelanggan
    {
        return Pelanggan::create(['nama' => 'Budi', 'no_telepon' => $telepon, 'password' => 'rahasia123']);
    }

    private function pesanan(Pelanggan $akun, string $status = 'selesai'): Pesanan
    {
        return $akun->pesanan()->create([
            'layanan' => 'Laundry Kiloan', 'harga' => 7000, 'no_telepon' => $akun->no_telepon,
            'tanggal_pickup' => now()->addDay()->toDateString(), 'alamat' => 'Jl. Contoh',
            'ongkir' => 2000, 'total_berat' => 3, 'total_harga' => 21000, 'status' => $status,
        ]);
    }

    private function ubahStatus(Pesanan $p, string $status)
    {
        $id = $p->id_pesanan;

        return $this->sebagaiAdmin()->put("/admin/order/{$id}", ['total_berat_'.$id => 3, 'status_'.$id => $status]);
    }

    // ---------- admin ----------

    public function test_admin_bisa_menandai_cucian_sedang_diantar_dan_waktunya_tercatat(): void
    {
        $p = $this->pesanan($this->akun());

        $this->ubahStatus($p, 'diantar')->assertRedirect();

        $p->refresh();
        $this->assertSame('diantar', $p->status);
        $this->assertNotNull($p->diantar_pada);
        $this->assertNull($p->diterima_pada);
    }

    public function test_admin_tidak_bisa_menandai_diterima_sendiri(): void
    {
        $p = $this->pesanan($this->akun(), 'diantar');

        $this->ubahStatus($p, 'diterima')->assertSessionHasErrors('status_'.$p->id_pesanan);

        $this->assertSame('diantar', $p->fresh()->status);
    }

    public function test_status_diterima_tetap_aman_saat_admin_hanya_mengubah_berat(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, 'diantar');
        $this->actingAs($akun, 'pelanggan')->post("/data-order/{$p->id_pesanan}/terima");

        $this->ubahStatus($p, 'diterima')->assertSessionHasNoErrors();

        $this->assertSame('diterima', $p->fresh()->status);
    }

    public function test_kembali_ke_tahap_sebelum_diantar_menghapus_waktu_pengantaran(): void
    {
        $p = $this->pesanan($this->akun());
        $this->ubahStatus($p, 'diantar');

        $this->ubahStatus($p, 'selesai');

        $p->refresh();
        $this->assertSame('selesai', $p->status);
        $this->assertNull($p->diantar_pada);
    }

    public function test_pilihan_status_admin_tidak_memuat_diterima_sebelum_waktunya(): void
    {
        $p = $this->pesanan($this->akun(), 'diantar');

        $this->sebagaiAdmin()->get('/admin/order')->assertOk()
            ->assertSee('Sedang diantar')
            ->assertDontSee('value="diterima"', false);
    }

    // ---------- pelanggan ----------

    public function test_tombol_diterima_hanya_muncul_saat_cucian_diantar(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, 'selesai');

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertOk()->assertDontSee('Cucian sudah diterima');

        $p->update(['status' => 'diantar']);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertOk()->assertSee('Cucian sudah diterima');
    }

    public function test_pelanggan_mengonfirmasi_cucian_diterima(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, 'diantar');

        $this->actingAs($akun, 'pelanggan')
            ->post("/data-order/{$p->id_pesanan}/terima")
            ->assertRedirect('/data-order')
            ->assertSessionHas('terima.berhasil', true);

        $p->refresh();
        $this->assertSame('diterima', $p->status);
        $this->assertNotNull($p->diterima_pada);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'pesanan.diterima_pelanggan', 'id_subjek' => $p->id_pesanan]);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertDontSee('Cucian sudah diterima');
    }

    public function test_tidak_bisa_menerima_sebelum_diantar(): void
    {
        $akun = $this->akun();

        foreach (['pending', 'proses', 'selesai'] as $status) {
            $p = $this->pesanan($akun, $status);

            $this->actingAs($akun, 'pelanggan')
                ->post("/data-order/{$p->id_pesanan}/terima")
                ->assertSessionHas('terima.berhasil', false);

            $this->assertSame($status, $p->fresh()->status);
        }
    }

    public function test_pesanan_orang_lain_dijawab_404(): void
    {
        $pemilik = $this->akun('081111111111');
        $p = $this->pesanan($pemilik, 'diantar');

        $this->actingAs($this->akun('082222222222'), 'pelanggan')
            ->post("/data-order/{$p->id_pesanan}/terima")
            ->assertNotFound();

        $this->assertSame('diantar', $p->fresh()->status);
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $p = $this->pesanan($this->akun(), 'diantar');

        $this->post("/data-order/{$p->id_pesanan}/terima")->assertRedirect('/masuk');
    }
}
