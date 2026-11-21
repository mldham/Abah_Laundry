<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function dataValid(array $ubah = []): array
    {
        return array_merge([
            'layanan' => 'Laundry Kiloan',
            'nama'    => 'Budi',
            'telepon' => '081234567890',
            'tanggal' => now()->addDay()->toDateString(),
            'alamat'  => 'Jl. Contoh No. 1, Bandung',
            'catatan' => '',
        ], $ubah);
    }

    private function akun(string $nama = 'Budi', string $telepon = '081234567890'): Pelanggan
    {
        return Pelanggan::create(['nama' => $nama, 'no_telepon' => $telepon, 'password' => 'rahasia123']);
    }

    public function test_halaman_order_tampil(): void
    {
        $this->get('/order')->assertOk()->assertSee('Laundry Express');
    }

    public function test_tamu_melihat_kolom_nama_dan_nomor(): void
    {
        $this->get('/order')->assertSee('name="nama"', false)->assertSee('name="telepon"', false);
    }

    public function test_yang_sudah_masuk_tidak_diminta_nama_dan_nomor(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')
            ->get('/order')
            ->assertOk()
            ->assertSee('Atas nama')
            ->assertDontSee('name="nama"', false)
            ->assertDontSee('name="telepon"', false);
    }

    public function test_order_tamu_tersimpan_dan_diarahkan_ke_daftar_dengan_isian_terisi(): void
    {
        $this->post('/order', $this->dataValid())
            ->assertRedirect(route('daftar'))
            ->assertSessionHas('_old_input.nama', 'Budi')
            ->assertSessionHas('_old_input.telepon', '081234567890');

        $this->assertDatabaseHas('data_pesanan', ['layanan' => 'Laundry Kiloan', 'harga' => 7000, 'ongkir' => 2000]);
    }

    public function test_order_dari_akun_memakai_nama_dan_nomor_akun_tanpa_baris_pelanggan_baru(): void
    {
        $akun = $this->akun();

        $this->actingAs($akun, 'pelanggan')
            ->post('/order', $this->dataValid(['nama' => null, 'telepon' => null]))
            ->assertRedirect(route('order.index'));

        $this->assertDatabaseCount('pelanggan', 1);
        $this->assertDatabaseHas('data_pesanan', [
            'id_pelanggan' => $akun->id_pelanggan,
            'no_telepon'   => '081234567890',
        ]);
    }

    public function test_alamat_bojongsoang_gratis_ongkir(): void
    {
        $this->post('/order', $this->dataValid(['alamat' => 'Jl. Raya Bojongsoang No. 5']));

        $this->assertDatabaseHas('data_pesanan', ['ongkir' => 0]);
    }

    public function test_layanan_tidak_valid_ditolak(): void
    {
        $this->post('/order', $this->dataValid(['layanan' => 'Layanan Palsu']))->assertSessionHasErrors('layanan');

        $this->assertDatabaseCount('data_pesanan', 0);
    }

    public function test_admin_menghitung_total_harga_otomatis_dari_berat_dan_tarif_layanan(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $pesanan = Pesanan::first();

        $this->put("/admin/order/{$pesanan->id_pesanan}", [
            'total_berat_'.$pesanan->id_pesanan => 3,
            'status_'.$pesanan->id_pesanan => 'proses',
            'total_harga' => 1,
        ])->assertRedirectContains('/admin/order#pesanan-'.$pesanan->id_pesanan);

        $this->assertDatabaseHas('data_pesanan', [
            'id_pesanan' => $pesanan->id_pesanan,
            'status' => 'proses',
            'total_berat' => 3,
            'total_harga' => 21000,
        ]);
    }

    public function test_admin_memakai_tarif_layanan_yang_dipesan(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid(['layanan' => 'Laundry Sepatu']));
        $pesanan = Pesanan::first();

        $this->put("/admin/order/{$pesanan->id_pesanan}", [
            'total_berat_'.$pesanan->id_pesanan => 2,
            'status_'.$pesanan->id_pesanan => 'proses',
        ])->assertRedirectContains('/admin/order#pesanan-'.$pesanan->id_pesanan);

        $this->assertDatabaseHas('data_pesanan', [
            'id_pesanan' => $pesanan->id_pesanan,
            'harga' => 15000,
            'total_berat' => 2,
            'total_harga' => 30000,
        ]);
    }

    public function test_admin_menghapus_satu_pesanan_dari_database(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $pesanan = Pesanan::first();
        $idPelanggan = $pesanan->id_pelanggan;

        $this->delete("/admin/order/{$pesanan->id_pesanan}")
            ->assertRedirect(route('admin.order.index'))
            ->assertSessionHas('aksi.jenis', 'hapus');

        $this->assertDatabaseMissing('data_pesanan', ['id_pesanan' => $pesanan->id_pesanan]);
        $this->assertDatabaseHas('pelanggan', ['id_pelanggan' => $idPelanggan]);
    }

    public function test_halaman_admin_menampilkan_total_otomatis_dan_tombol_hapus(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $pesanan = Pesanan::first();

        $this->get('/admin/order')
            ->assertOk()
            ->assertSee('data-total', false)
            ->assertSee('name="total_berat_'.$pesanan->id_pesanan.'"', false)
            ->assertSee('name="status_'.$pesanan->id_pesanan.'"', false)
            ->assertSee('data-hapus', false)
            ->assertSee('id="dialog-hapus"', false)
            ->assertDontSee('name="total_harga"', false);
    }

    public function test_cek_pesanan_tanpa_masuk_diarahkan_ke_halaman_masuk(): void
    {
        $this->post('/order', $this->dataValid());

        $this->get('/data-order')->assertRedirect(route('masuk'));
    }

    public function test_cek_pesanan_hanya_menampilkan_milik_akun_yang_masuk(): void
    {
        $this->post('/order', $this->dataValid(['alamat' => 'Alamat Rahasia Budi']));
        $this->post('/order', $this->dataValid([
            'layanan' => 'Laundry Sepatu', 'nama' => 'Sari', 'telepon' => '089999999999', 'alamat' => 'Alamat Rahasia Sari',
        ]));

        $this->actingAs($this->akun(), 'pelanggan')
            ->get('/data-order')
            ->assertOk()
            ->assertSee('Laundry Kiloan')
            ->assertDontSee('Laundry Sepatu')
            ->assertDontSee('Alamat Rahasia Budi')
            ->assertDontSee('Sari');
    }

    public function test_cek_pesanan_tidak_lagi_menerima_nomor_dari_url(): void
    {
        $this->post('/order', $this->dataValid(['layanan' => 'Laundry Sepatu', 'nama' => 'Sari', 'telepon' => '089999999999']));

        $this->actingAs($this->akun(), 'pelanggan')
            ->get('/data-order?telepon=089999999999')
            ->assertOk()
            ->assertDontSee('Laundry Sepatu')
            ->assertSee('Belum pernah order');
    }

    public function test_pesanan_tamu_dengan_nomor_berbeda_format_tetap_cocok_dengan_akun(): void
    {
        $this->post('/order', $this->dataValid(['telepon' => '+62 812-3456-7890']));

        $this->actingAs($this->akun(), 'pelanggan')->get('/data-order')->assertSee('Laundry Kiloan');
    }

    public function test_akun_tanpa_pesanan_menampilkan_keadaan_kosong(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')
            ->get('/data-order')
            ->assertSee('Belum pernah order')
            ->assertSee('Buat pesanan baru');
    }

    // ---------- umpan balik setelah aksi ----------

    public function test_simpan_menyebut_hanya_yang_berubah_dan_muncul_sebagai_notifikasi_hijau(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $id = Pesanan::first()->id_pesanan;

        $this->put("/admin/order/{$id}", ['total_berat_'.$id => 3, 'status_'.$id => 'proses'])
            ->assertSessionHas('aksi.judul', 'Tersimpan.')
            ->assertSessionHas('aksi.isi', 'Status jadi sedang dikerjakan, 3 kg, total Rp21.000.');

        $this->get('/admin/order')
            ->assertSee('Tersimpan.')
            ->assertSee('Status jadi sedang dikerjakan, 3 kg, total Rp21.000.')
            ->assertSee('data-notifikasi', false)
            ->assertSee('baris-baru', false)
            ->assertDontSee('pesan-sukses', false);
    }

    public function test_simpan_tanpa_perubahan_berkata_jujur(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $id = Pesanan::first()->id_pesanan;

        $this->put("/admin/order/{$id}", ['total_berat_'.$id => 0, 'status_'.$id => 'pending'])
            ->assertSessionHas('aksi.judul', 'Tidak ada perubahan.');
    }

    public function test_simpan_gagal_menandai_baris_dan_input_yang_salah(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $id = Pesanan::first()->id_pesanan;

        $this->put("/admin/order/{$id}", ['total_berat_'.$id => '', 'status_'.$id => 'proses'])
            ->assertRedirectContains('/admin/order#pesanan-'.$id)
            ->assertSessionHasErrors('total_berat_'.$id);

        $this->get('/admin/order')
            ->assertSee('Belum tersimpan.')
            ->assertSee('Isi berat atau jumlah pesanan.')
            ->assertSee('baris-galat', false)
            ->assertDontSee('data-notifikasi', false)
            ->assertSee('aria-invalid="true"', false);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $id, 'status' => 'pending']);
    }

    public function test_hapus_menyebut_siapa_dan_layanan_apa(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $id = Pesanan::first()->id_pesanan;

        $this->delete("/admin/order/{$id}")
            ->assertSessionHas('aksi.judul', 'Dihapus.')
            ->assertSessionHas('aksi.isi', "Pesanan #{$id} atas nama Budi (Laundry Kiloan) sudah dihapus.");

        $this->get('/admin/order')
            ->assertSee('Dihapus.')
            ->assertSee('data-notifikasi', false)
            ->assertSee('Belum ada pesanan');
    }

    public function test_notifikasi_hanya_sekali_dan_hilang_setelah_dimuat_ulang(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $id = Pesanan::first()->id_pesanan;

        $this->put("/admin/order/{$id}", ['total_berat_'.$id => 3, 'status_'.$id => 'proses']);

        $this->get('/admin/order')->assertSee('data-notifikasi', false);
        $this->get('/admin/order')->assertDontSee('data-notifikasi', false);
    }

    public function test_order_tamu_menampilkan_kabar_spesifik_di_halaman_daftar(): void
    {
        $this->post('/order', $this->dataValid(['tanggal' => '2030-01-15']));
        $id = Pesanan::first()->id_pesanan;

        $this->get('/daftar')
            ->assertSee("Pesanan #{$id} sudah masuk.")
            ->assertSee('Dijemput Selasa, 15 Januari 2030.')
            ->assertDontSee('berhasil dibuat')
            ->assertDontSee('pesan-sukses', false);
    }

    public function test_order_dari_akun_menandai_kartu_pesanan_di_cek_pesanan(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')->post('/order', $this->dataValid(['nama' => null, 'telepon' => null]));

        $this->get('/data-order')
            ->assertSee('Pesanan sudah masuk.')
            ->assertDontSee('pesan-sukses', false);

        // kabar hanya sekali: muat ulang tidak mengulanginya
        $this->get('/data-order')->assertDontSee('Pesanan sudah masuk.');
    }

    // ---------- heuristik usability: kontrol pengguna, pencegahan galat, bantuan ----------

    public function test_pelanggan_membatalkan_pesanan_yang_belum_dijemput(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')->post('/order', $this->dataValid(['nama' => null, 'telepon' => null]));
        $id = Pesanan::first()->id_pesanan;

        $this->delete("/data-order/{$id}")
            ->assertRedirect(route('order.index'))
            ->assertSessionHas('batal.berhasil', true)
            ->assertSessionHas('batal.judul', "Pesanan #{$id} dibatalkan.");

        $this->assertDatabaseMissing('data_pesanan', ['id_pesanan' => $id]);
    }

    public function test_pesanan_yang_sudah_diproses_tidak_bisa_dibatalkan_pelanggan(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')->post('/order', $this->dataValid(['nama' => null, 'telepon' => null]));
        $pesanan = Pesanan::first();
        $pesanan->update(['status' => 'proses']);

        $this->delete("/data-order/{$pesanan->id_pesanan}")
            ->assertSessionHas('batal.berhasil', false);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $pesanan->id_pesanan]);
    }

    public function test_pelanggan_tidak_bisa_membatalkan_pesanan_orang_lain(): void
    {
        $this->post('/order', $this->dataValid(['nama' => 'Sari', 'telepon' => '089999999999']));
        $id = Pesanan::first()->id_pesanan;

        $this->actingAs($this->akun(), 'pelanggan')->delete("/data-order/{$id}")->assertNotFound();

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $id]);
    }

    public function test_tombol_batal_hanya_muncul_untuk_pesanan_yang_menunggu_dijemput(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')->post('/order', $this->dataValid(['nama' => null, 'telepon' => null]));

        $this->get('/data-order')->assertSee('Batalkan pesanan')->assertSee('data-konfirmasi', false);

        Pesanan::first()->update(['status' => 'selesai']);

        $this->get('/data-order')->assertDontSee('data-konfirmasi', false);
    }

    public function test_cek_pesanan_memakai_satuan_dan_penjelasan_sesuai_layanan_dan_status(): void
    {
        $this->post('/order', $this->dataValid(['layanan' => 'Laundry Sepatu']));

        $this->actingAs($this->akun(), 'pelanggan')->get('/data-order')
            ->assertSee('Jumlah')
            ->assertSee('Dihitung saat dijemput')
            ->assertDontSee('Ditimbang saat dijemput')
            ->assertSee('Kami menjemput cuciannya sesuai jadwal.');

        Pesanan::first()->update(['status' => 'proses', 'total_berat' => 2, 'total_harga' => 30000]);

        $this->get('/data-order')->assertSee('2 pasang')->assertDontSee('2 kg');
    }

    public function test_admin_tidak_bisa_memajukan_status_sebelum_berat_diisi(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());
        $id = Pesanan::first()->id_pesanan;

        $this->put("/admin/order/{$id}", ['total_berat_'.$id => 0, 'status_'.$id => 'proses'])
            ->assertSessionHasErrors('total_berat_'.$id);

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $id, 'status' => 'pending']);
    }

    public function test_order_gagal_menautkan_ringkasan_ke_kolom_yang_salah(): void
    {
        $this->from('/order')->post('/order', $this->dataValid(['alamat' => '']));

        $this->get('/order')
            ->assertSee('data-fokus-galat', false)
            ->assertSee('href="#alamat"', false)
            ->assertSee('id="alamat-galat"', false)
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_order_menjelaskan_ongkir_sebelum_dikirim_dan_tombolnya_menyebut_aksinya(): void
    {
        $this->get('/order')
            ->assertSee('Ongkir Rp2.000, gratis untuk alamat di Bojongsoang.')
            ->assertSee('Kirim pesanan')
            ->assertDontSee('Lanjut')
            ->assertDontSee('Pickup');
    }

    public function test_alamat_terakhir_terisi_untuk_akun_yang_sudah_masuk(): void
    {
        $this->actingAs($this->akun(), 'pelanggan')
            ->post('/order', $this->dataValid(['nama' => null, 'telepon' => null, 'alamat' => 'Jl. Melati No. 5']));

        $this->get('/order')
            ->assertSee('Jl. Melati No. 5')
            ->assertSee('Diisi dari pesanan terakhir.');
    }

    public function test_halaman_bantuan_memakai_harga_dan_ongkir_dari_config(): void
    {
        $this->get('/bantuan')
            ->assertOk()
            ->assertSee('Rp7.000 / kg')
            ->assertSee('Menunggu dijemput')
            ->assertSee('Ongkir Rp2.000')
            ->assertDontSee('wa.me');

        config(['layanan.kontak_whatsapp' => '081234567890']);

        $this->get('/bantuan')->assertSee('https://wa.me/6281234567890', false);
    }

    public function test_halaman_tidak_ditemukan_berbahasa_indonesia_dan_menunjuk_langkah_berikutnya(): void
    {
        $this->get('/tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan.')
            ->assertSee('Ke halaman order');
    }

    public function test_navbar_menandai_halaman_aktif_dan_punya_tautan_bantuan(): void
    {
        $this->get('/order')
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('bantuan'), false);
    }

    public function test_admin_punya_penyaring_status_dan_pencarian(): void
    {
        $this->sebagaiAdmin();
        $this->post('/order', $this->dataValid());

        $this->get('/admin/order')
            ->assertSee('data-saring="semua"', false)
            ->assertSee('data-saring="proses"', false)
            ->assertSee('id="cari-pesanan"', false)
            ->assertSee('data-cari="budi', false);
    }
}
