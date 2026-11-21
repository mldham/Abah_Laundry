<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrukTest extends TestCase
{
    use RefreshDatabase;

    private function akun(string $telepon = '081234567890'): Pelanggan
    {
        return Pelanggan::create(['nama' => 'Budi', 'no_telepon' => $telepon, 'password' => 'rahasia123']);
    }

    // Kiloan 3 kg x Rp7.000 = Rp21.000, ongkir Rp2.000, jadi total Rp23.000.
    private function pesanan(Pelanggan $akun, array $ubah = []): Pesanan
    {
        return $akun->pesanan()->create(array_merge([
            'layanan' => 'Laundry Kiloan', 'harga' => 7000, 'no_telepon' => $akun->no_telepon,
            'tanggal_pickup' => now()->addDay()->toDateString(), 'alamat' => 'Jl. Contoh No. 5',
            'ongkir' => 2000, 'total_berat' => 3, 'total_harga' => 21000,
            // Pelanggan baru bisa melihat struk setelah menerima cucian.
            'status' => 'diterima', 'diterima_pada' => now(),
        ], $ubah));
    }

    public function test_pelanggan_melihat_struk_pesanannya_sendiri(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['notes' => 'Jangan diperas']);

        $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/struk")
            ->assertOk()
            ->assertSee('Struk pemesanan')
            ->assertSee('#'.$p->id_pesanan)
            ->assertSee('Budi')
            ->assertSee('Jl. Contoh No. 5')
            ->assertSee('Jangan diperas')
            ->assertSee('3 kg x Rp7.000', false)
            ->assertSee('Rp21.000')
            ->assertSee('Rp2.000')
            ->assertSee('Rp23.000')
            ->assertSee('Belum dibayar');
    }

    public function test_kepala_struk_memuat_alamat_toko(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/struk")
            ->assertOk()
            ->assertSee('Jl. In')
            ->assertSee('Desa Kandang Sapi, Kec. Kras')
            ->assertSee('Kab. Fakfak');
    }

    public function test_struk_pesanan_orang_lain_dijawab_404(): void
    {
        $p = $this->pesanan($this->akun('081111111111'));

        $this->actingAs($this->akun('082222222222'), 'pelanggan')
            ->get("/data-order/{$p->id_pesanan}/struk")
            ->assertNotFound();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $p = $this->pesanan($this->akun());

        $this->get("/data-order/{$p->id_pesanan}/struk")->assertRedirect('/masuk');
    }

    public function test_struk_sebelum_ditimbang_tidak_menampilkan_total_palsu(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['total_berat' => 0, 'total_harga' => 0]);

        $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/struk")
            ->assertOk()
            ->assertSee('Belum ditimbang')
            ->assertSee('Dihitung setelah ditimbang')
            ->assertDontSee('Rp0');
    }

    public function test_struk_lunas_menampilkan_metode_dan_waktu_bayar(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['status_bayar' => 'lunas', 'metode_bayar' => 'qris', 'dibayar_pada' => now()]);

        $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/struk")
            ->assertOk()
            ->assertSee('Lunas')
            ->assertSee('QRIS')
            ->assertSee('Dibayar');
    }

    public function test_ongkir_gratis_tertulis_gratis(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['ongkir' => 0]);

        $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/struk")
            ->assertSee('Gratis')
            ->assertSee('Rp21.000');
    }

    public function test_admin_dan_operator_bisa_membuka_struk(): void
    {
        $p = $this->pesanan($this->akun());

        foreach ([User::PERAN_ADMIN, User::PERAN_OPERATOR] as $peran) {
            $this->sebagaiAdmin($peran)->get("/admin/order/{$p->id_pesanan}/struk")
                ->assertOk()
                ->assertSee('Struk pemesanan')
                ->assertSee('ke daftar pesanan');
        }
    }

    public function test_struk_admin_tidak_untuk_tamu_atau_pelanggan(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->get("/admin/order/{$p->id_pesanan}/struk")->assertRedirect('/admin/masuk');

        $this->actingAs($akun, 'pelanggan')->get("/admin/order/{$p->id_pesanan}/struk")->assertRedirect('/admin/masuk');
    }

    public function test_tautan_struk_ada_di_cek_pesanan_dan_kelola_pesanan(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertSee(route('order.struk', $p), false);

        $this->sebagaiAdmin()->get('/admin/order')->assertSee(route('admin.order.struk', $p), false);
    }

    public function test_tautan_struk_pelanggan_tersembunyi_sebelum_diterima(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['status' => 'diantar', 'diterima_pada' => null]);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')
            ->assertDontSee(route('order.struk', $p), false);

        // Admin tetap bisa mencetak struk kapan saja.
        $this->sebagaiAdmin()->get('/admin/order')->assertSee(route('admin.order.struk', $p), false);
    }

    public function test_struk_pelanggan_sebelum_diterima_dialihkan_ke_cek_pesanan(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, ['status' => 'diantar', 'diterima_pada' => null]);

        $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/struk")
            ->assertRedirect('/data-order')
            ->assertSessionHas('terima.berhasil', false);
    }
}
