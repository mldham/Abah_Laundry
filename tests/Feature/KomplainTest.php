<?php

namespace Tests\Feature;

use App\Models\Komplain;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KomplainTest extends TestCase
{
    use RefreshDatabase;

    private function akun(string $telepon = '081234567890'): Pelanggan
    {
        return Pelanggan::create(['nama' => 'Budi', 'no_telepon' => $telepon, 'password' => 'rahasia123']);
    }

    private function pesanan(Pelanggan $akun, string $status = 'diantar'): Pesanan
    {
        return $akun->pesanan()->create([
            'layanan' => 'Laundry Kiloan', 'harga' => 7000, 'no_telepon' => $akun->no_telepon,
            'tanggal_pickup' => now()->addDay()->toDateString(), 'alamat' => 'Jl. Contoh',
            'ongkir' => 2000, 'total_berat' => 3, 'total_harga' => 21000, 'status' => $status,
        ]);
    }

    private function komplain(Pesanan $p, array $ubah = []): Komplain
    {
        return Komplain::create(array_merge([
            'id_pesanan' => $p->id_pesanan, 'id_pelanggan' => $p->id_pelanggan,
            'jenis' => 'kurang_bersih', 'deskripsi' => 'Kaos putih masih ada noda.', 'status' => 'baru',
        ], $ubah));
    }

    private function kirim(Pelanggan $akun, Pesanan $p, array $data = [])
    {
        return $this->actingAs($akun, 'pelanggan')->post("/data-order/{$p->id_pesanan}/komplain", array_merge([
            'jenis' => 'barang_hilang', 'deskripsi' => 'Dua kaos kaki tidak ikut kembali.',
        ], $data));
    }

    private function ubah(Komplain $k, string $status, string $tanggapan = '', string $peran = User::PERAN_ADMIN)
    {
        $id = $k->id_komplain;

        return $this->sebagaiAdmin($peran)->put("/admin/komplain/{$id}", ['status_'.$id => $status, 'tanggapan_'.$id => $tanggapan]);
    }

    // ---------- pelanggan: membuka formulir ----------

    public function test_formulir_terbuka_saat_cucian_diantar_atau_sudah_diterima(): void
    {
        $akun = $this->akun();

        foreach (['diantar', 'diterima'] as $status) {
            $p = $this->pesanan($akun, $status);

            $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/komplain")
                ->assertOk()
                ->assertSee('Ada masalah dengan pesanan #'.$p->id_pesanan)
                ->assertSee('Cucian kurang bersih')
                ->assertSee('Barang hilang atau kurang');
        }
    }

    public function test_formulir_ditolak_sebelum_cucian_diantar(): void
    {
        $akun = $this->akun();

        foreach (['pending', 'proses', 'selesai'] as $status) {
            $p = $this->pesanan($akun, $status);

            $this->actingAs($akun, 'pelanggan')->get("/data-order/{$p->id_pesanan}/komplain")
                ->assertRedirect('/data-order')
                ->assertSessionHas('komplain.berhasil', false);

            $this->kirim($akun, $p)->assertSessionHas('komplain.berhasil', false);
            $this->assertSame(0, $p->komplain()->count());
        }
    }

    // ---------- pelanggan: mengirim ----------

    public function test_pelanggan_mengirim_komplain_dan_tercatat_di_audit(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->kirim($akun, $p)
            ->assertRedirect('/data-order')
            ->assertSessionHas('komplain.berhasil', true);

        $k = $p->komplain()->first();
        $this->assertSame('barang_hilang', $k->jenis);
        $this->assertSame('baru', $k->status);
        $this->assertSame($akun->getKey(), $k->id_pelanggan);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'komplain.dibuat', 'id_subjek' => $k->id_komplain]);
    }

    public function test_jenis_dan_cerita_wajib_diisi(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->kirim($akun, $p, ['jenis' => '', 'deskripsi' => ''])->assertSessionHasErrors(['jenis', 'deskripsi']);
        $this->kirim($akun, $p, ['deskripsi' => 'pendek'])->assertSessionHasErrors('deskripsi');
        $this->kirim($akun, $p, ['jenis' => 'ngawur'])->assertSessionHasErrors('jenis');
        $this->kirim($akun, $p, ['deskripsi' => str_repeat('a', 1001)])->assertSessionHasErrors('deskripsi');

        $this->assertSame(0, $p->komplain()->count());
    }

    public function test_satu_komplain_terbuka_per_pesanan_dan_boleh_lagi_setelah_selesai(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);

        $this->kirim($akun, $p)->assertSessionHas('komplain.berhasil', true);
        $this->kirim($akun, $p)->assertSessionHas('komplain.berhasil', false);
        $this->assertSame(1, $p->komplain()->count());

        $p->komplain()->first()->update(['status' => 'selesai']);

        $this->kirim($akun, $p->fresh())->assertSessionHas('komplain.berhasil', true);
        $this->assertSame(2, $p->komplain()->count());
    }

    public function test_pesanan_orang_lain_dijawab_404(): void
    {
        $p = $this->pesanan($this->akun('081111111111'));
        $lain = $this->akun('082222222222');

        $this->actingAs($lain, 'pelanggan')->get("/data-order/{$p->id_pesanan}/komplain")->assertNotFound();
        $this->kirim($lain, $p)->assertNotFound();
        $this->assertSame(0, $p->komplain()->count());
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $p = $this->pesanan($this->akun());

        $this->get("/data-order/{$p->id_pesanan}/komplain")->assertRedirect('/masuk');
        $this->post("/data-order/{$p->id_pesanan}/komplain", [])->assertRedirect('/masuk');
    }

    // ---------- pelanggan: kartu pesanan ----------

    public function test_tombol_ada_masalah_muncul_di_sebelah_tombol_diterima(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun, 'selesai');

        $this->actingAs($akun, 'pelanggan')->get('/data-order')->assertDontSee('Ada masalah');

        $p->update(['status' => 'diantar']);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')
            ->assertSee('Cucian sudah diterima')
            ->assertSee('Ada masalah')
            ->assertSee(route('order.komplain', $p), false);
    }

    public function test_kartu_menampilkan_komplain_dan_tanggapan_dan_menyembunyikan_tombol(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);
        $this->komplain($p, ['status' => 'diproses', 'tanggapan' => 'Kami cuci ulang besok.']);

        $this->actingAs($akun, 'pelanggan')->get('/data-order')
            ->assertSee('Cucian kurang bersih')
            ->assertSee('Sedang ditangani')
            ->assertSee('Kaos putih masih ada noda.')
            ->assertSee('Tanggapan admin')
            ->assertSee('Kami cuci ulang besok.')
            ->assertDontSee('Ada masalah');
    }

    public function test_komplain_pesanan_lama_tetap_terlihat_di_daftar_pesanan_lainnya(): void
    {
        $akun = $this->akun();
        $lama = $this->pesanan($akun, 'diterima');
        $this->komplain($lama, ['tanggapan' => 'Sudah kami cek.']);
        $this->pesanan($akun, 'proses');

        $this->actingAs($akun, 'pelanggan')->get('/data-order')
            ->assertSee('Sudah kami cek.')
            ->assertSee('Kaos putih masih ada noda.');
    }

    // ---------- admin ----------

    public function test_admin_dan_operator_melihat_daftar_komplain(): void
    {
        $p = $this->pesanan($this->akun());
        $this->komplain($p);

        foreach ([User::PERAN_ADMIN, User::PERAN_OPERATOR] as $peran) {
            $this->sebagaiAdmin($peran)->get('/admin/komplain')
                ->assertOk()
                ->assertSee('Komplain pelanggan')
                ->assertSee('Budi')
                ->assertSee('Kaos putih masih ada noda.');
        }
    }

    public function test_halaman_komplain_admin_tidak_untuk_tamu_atau_pelanggan(): void
    {
        $akun = $this->akun();
        $p = $this->pesanan($akun);
        $k = $this->komplain($p);

        $this->get('/admin/komplain')->assertRedirect('/admin/masuk');
        $this->actingAs($akun, 'pelanggan')->get('/admin/komplain')->assertRedirect('/admin/masuk');
        $this->actingAs($akun, 'pelanggan')->put("/admin/komplain/{$k->id_komplain}", [])->assertRedirect('/admin/masuk');
    }

    public function test_saringan_status_menyaring_daftar(): void
    {
        $p = $this->pesanan($this->akun());
        $this->komplain($p, ['status' => 'baru', 'deskripsi' => 'Masih baru dan belum dibaca.']);
        $this->komplain($p, ['status' => 'selesai', 'deskripsi' => 'Sudah beres sejak lama.']);

        $this->sebagaiAdmin()->get('/admin/komplain?status=baru')
            ->assertSee('Masih baru dan belum dibaca.')
            ->assertDontSee('Sudah beres sejak lama.');
    }

    public function test_admin_menanggapi_dan_mengubah_status(): void
    {
        $k = $this->komplain($this->pesanan($this->akun()));

        $this->ubah($k, 'diproses', 'Kami cuci ulang besok.')
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $k->refresh();
        $this->assertSame('diproses', $k->status);
        $this->assertSame('Kami cuci ulang besok.', $k->tanggapan);
        $this->assertNotNull($k->id_user);
        $this->assertNull($k->selesai_pada);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'komplain.ditanggapi', 'id_subjek' => $k->id_komplain]);
    }

    public function test_menandai_selesai_butuh_tanggapan(): void
    {
        $k = $this->komplain($this->pesanan($this->akun()));

        $this->ubah($k, 'selesai', '   ')->assertSessionHasErrors('tanggapan_'.$k->id_komplain);

        $this->assertSame('baru', $k->fresh()->status);
    }

    public function test_selesai_mencatat_waktu_dan_dibuka_lagi_menghapusnya(): void
    {
        $k = $this->komplain($this->pesanan($this->akun()));

        $this->ubah($k, 'selesai', 'Sudah dicuci ulang dan diantar.');
        $this->assertNotNull($k->fresh()->selesai_pada);

        $this->ubah($k, 'diproses', 'Sudah dicuci ulang dan diantar.');
        $this->assertNull($k->fresh()->selesai_pada);
    }

    public function test_operator_boleh_menanggapi(): void
    {
        $k = $this->komplain($this->pesanan($this->akun()));

        $this->ubah($k, 'diproses', 'Sedang kami cek.', User::PERAN_OPERATOR)->assertSessionHasNoErrors();

        $this->assertSame('diproses', $k->fresh()->status);
    }

    public function test_tidak_ada_perubahan_tidak_mencatat_audit(): void
    {
        $k = $this->komplain($this->pesanan($this->akun()), ['status' => 'diproses', 'tanggapan' => 'Sedang dicek.']);

        $this->ubah($k, 'diproses', 'Sedang dicek.')->assertRedirect();

        $this->assertDatabaseMissing('log_aktivitas', ['aksi' => 'komplain.ditanggapi']);
    }

    public function test_status_tidak_valid_ditolak(): void
    {
        $k = $this->komplain($this->pesanan($this->akun()));

        $this->ubah($k, 'ngawur', 'x')->assertSessionHasErrors('status_'.$k->id_komplain);
    }

    public function test_menu_admin_menampilkan_jumlah_komplain_baru(): void
    {
        $p = $this->pesanan($this->akun());
        $this->komplain($p);
        $this->komplain($p, ['status' => 'diproses']);

        $this->sebagaiAdmin()->get('/admin/order')
            ->assertSee('<span class="lencana">1<span class="sr-only"> komplain baru</span></span>', false);
    }

    public function test_kelola_pesanan_menautkan_ke_komplain_yang_terbuka(): void
    {
        $p = $this->pesanan($this->akun());
        $k = $this->komplain($p);

        $this->sebagaiAdmin()->get('/admin/order')
            ->assertSee('Komplain baru')
            ->assertSee('#komplain-'.$k->id_komplain, false);
    }

    public function test_menghapus_pesanan_ikut_menghapus_komplainnya(): void
    {
        $p = $this->pesanan($this->akun());
        $this->komplain($p);

        $this->sebagaiAdmin()->delete("/admin/order/{$p->id_pesanan}");

        $this->assertSame(0, Komplain::count());
    }
}
