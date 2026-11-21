<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function pesanan(): Pesanan
    {
        $pelanggan = Pelanggan::create(['nama' => 'Budi', 'no_telepon' => '081234567890', 'password' => 'rahasia123']);

        return $pelanggan->pesanan()->create([
            'layanan' => 'Laundry Kiloan', 'harga' => 7000, 'no_telepon' => '081234567890',
            'tanggal_pickup' => now()->addDay()->toDateString(), 'alamat' => 'Jl. Contoh',
        ]);
    }

    private function dataUbah(Pesanan $p, int $berat = 3, string $status = 'proses'): array
    {
        return ['total_berat_'.$p->id_pesanan => $berat, 'status_'.$p->id_pesanan => $status];
    }

    // ---------- autentikasi ----------

    public function test_tamu_dialihkan_ke_masuk_admin_dari_semua_halaman_admin(): void
    {
        $p = $this->pesanan();

        $this->get('/admin/order')->assertRedirect(route('admin.masuk'));
        $this->get('/admin/audit')->assertRedirect(route('admin.masuk'));
        $this->get('/admin')->assertRedirect('/admin/order');
        $this->put("/admin/order/{$p->id_pesanan}", $this->dataUbah($p))->assertRedirect(route('admin.masuk'));
        $this->delete("/admin/order/{$p->id_pesanan}")->assertRedirect(route('admin.masuk'));

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status' => 'pending']);
    }

    public function test_pelanggan_yang_sudah_masuk_tidak_otomatis_jadi_admin(): void
    {
        $akun = Pelanggan::create(['nama' => 'Budi', 'no_telepon' => '081234567890', 'password' => 'rahasia123']);

        $this->actingAs($akun, 'pelanggan')->get('/admin/order')->assertRedirect(route('admin.masuk'));
    }

    public function test_admin_yang_sudah_masuk_tidak_otomatis_jadi_pelanggan(): void
    {
        $this->sebagaiAdmin()->get('/data-order')->assertRedirect(route('masuk'));
    }

    public function test_halaman_masuk_admin_tampil_untuk_tamu(): void
    {
        $this->get('/admin/masuk')->assertOk()->assertSee('Masuk admin')->assertSee('Email');
        $this->get('/masuk')->assertSee('Masuk sebagai admin');
    }

    public function test_masuk_admin_berhasil_dan_dicatat(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'a@laundry.test', 'password' => 'rahasia123']);

        $this->post('/admin/masuk', ['email' => 'a@laundry.test', 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.order.index'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'admin.masuk', 'id_user' => $admin->id]);
    }

    public function test_tujuan_semula_diingat_sampai_selesai_masuk(): void
    {
        User::factory()->admin()->create(['email' => 'a@laundry.test', 'password' => 'rahasia123']);

        $this->get('/admin/audit')->assertRedirect(route('admin.masuk'));
        $this->post('/admin/masuk', ['email' => 'a@laundry.test', 'password' => 'rahasia123'])
            ->assertRedirect(url('/admin/audit'));
    }

    public function test_masuk_gagal_pesannya_sama_dan_email_dicatat(): void
    {
        User::factory()->admin()->create(['email' => 'a@laundry.test', 'password' => 'rahasia123']);

        $this->from('/admin/masuk')->post('/admin/masuk', ['email' => 'a@laundry.test', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
        $this->from('/admin/masuk')->post('/admin/masuk', ['email' => 'tidak@ada.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest('admin');
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'admin.masuk_gagal', 'email_dicoba' => 'tidak@ada.test', 'id_user' => null]);
        $this->assertSame(2, LogAktivitas::where('aksi', 'admin.masuk_gagal')->count());
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        User::factory()->admin()->nonaktif()->create(['email' => 'a@laundry.test', 'password' => 'rahasia123']);

        $this->from('/admin/masuk')->post('/admin/masuk', ['email' => 'a@laundry.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest('admin');
    }

    public function test_akun_yang_dinonaktifkan_saat_sesi_berjalan_langsung_terlempar(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'admin')->get('/admin/order')->assertOk();

        $admin->forceFill(['aktif' => false])->save();

        $this->get('/admin/order')
            ->assertRedirect(route('admin.masuk'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'admin.sesi_dihentikan', 'id_user' => $admin->id]);
    }

    public function test_percobaan_masuk_admin_dibatasi(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/masuk', ['email' => 'a@laundry.test', 'password' => 'salah']);
        }

        $this->post('/admin/masuk', ['email' => 'a@laundry.test', 'password' => 'salah'])->assertStatus(429);
    }

    public function test_admin_yang_sudah_masuk_dialihkan_dari_halaman_masuk(): void
    {
        $this->sebagaiAdmin()->get('/admin/masuk')->assertRedirect(route('admin.order.index'));
    }

    public function test_keluar_mengakhiri_sesi_dan_dicatat(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'admin')->post('/admin/keluar')->assertRedirect(route('admin.masuk'));

        $this->assertGuest('admin');
        $this->get('/admin/order')->assertRedirect(route('admin.masuk'));
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'admin.keluar', 'id_user' => $admin->id]);
    }

    public function test_halaman_admin_melarang_cache_browser(): void
    {
        $this->sebagaiAdmin()->get('/admin/order')->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_peran_tidak_bisa_diisi_massal(): void
    {
        $user = User::create(['name' => 'X', 'email' => 'x@laundry.test', 'password' => 'rahasia123', 'peran' => 'admin', 'aktif' => false]);

        $this->assertSame('operator', $user->fresh()->peran);
        $this->assertTrue($user->fresh()->aktif);
    }

    // ---------- otorisasi ----------

    public function test_operator_boleh_melihat_dan_mengubah_pesanan(): void
    {
        $p = $this->pesanan();

        $this->sebagaiAdmin(User::PERAN_OPERATOR)->get('/admin/order')->assertOk();
        $this->put("/admin/order/{$p->id_pesanan}", $this->dataUbah($p))->assertRedirectContains('/admin/order#pesanan-');

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan, 'status' => 'proses', 'total_harga' => 21000]);
    }

    public function test_operator_tidak_bisa_menghapus_dan_penolakan_dicatat(): void
    {
        $p = $this->pesanan();
        $operator = User::factory()->create();

        $this->actingAs($operator, 'admin')->delete("/admin/order/{$p->id_pesanan}")->assertForbidden();

        $this->assertDatabaseHas('data_pesanan', ['id_pesanan' => $p->id_pesanan]);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'akses.ditolak', 'id_user' => $operator->id]);
    }

    public function test_tombol_hapus_hanya_tampil_untuk_admin(): void
    {
        $this->pesanan();

        $formHapus = 'name="_method" value="DELETE"';

        $this->sebagaiAdmin(User::PERAN_OPERATOR)->get('/admin/order')->assertOk()->assertDontSee($formHapus, false);
        $this->sebagaiAdmin(User::PERAN_ADMIN)->get('/admin/order')->assertOk()->assertSee($formHapus, false);
    }

    public function test_hanya_admin_boleh_membuka_audit(): void
    {
        $operator = User::factory()->create();

        $this->actingAs($operator, 'admin')->get('/admin/audit')->assertForbidden();
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'akses.ditolak', 'id_user' => $operator->id]);

        $this->sebagaiAdmin(User::PERAN_ADMIN)->get('/admin/audit')->assertOk()->assertSee('Audit aktivitas');
    }

    public function test_navbar_menampilkan_menu_sesuai_peran(): void
    {
        $this->sebagaiAdmin(User::PERAN_OPERATOR)->get('/admin/order')
            ->assertSee('Kelola pesanan')->assertSee('Operator:')->assertDontSee('>Audit<', false);

        $this->sebagaiAdmin(User::PERAN_ADMIN)->get('/admin/order')->assertSee('>Audit<', false);
    }

    public function test_halaman_403_memberi_langkah_berikutnya(): void
    {
        $this->sebagaiAdmin(User::PERAN_OPERATOR)->get('/admin/audit')
            ->assertForbidden()
            ->assertSee('Akses ditolak.')
            ->assertSee('Ke daftar pesanan');
    }

    // ---------- audit ----------

    public function test_ubah_pesanan_dicatat_dengan_nilai_sebelum_dan_sesudah(): void
    {
        $p = $this->pesanan();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'admin')->put("/admin/order/{$p->id_pesanan}", $this->dataUbah($p));

        $log = LogAktivitas::where('aksi', 'pesanan.diubah')->first();
        $this->assertSame($admin->id, $log->id_user);
        $this->assertSame('Pesanan', $log->subjek);
        $this->assertSame($p->id_pesanan, $log->id_subjek);
        $this->assertSame('pending', $log->detail['sebelum']['status']);
        $this->assertSame('proses', $log->detail['sesudah']['status']);
        $this->assertStringContainsString('status: pending ke proses', $log->ringkasan);
    }

    public function test_simpan_tanpa_perubahan_tidak_dicatat(): void
    {
        $p = $this->pesanan();

        $this->sebagaiAdmin()->put("/admin/order/{$p->id_pesanan}", $this->dataUbah($p, 0, 'pending'));

        $this->assertDatabaseMissing('log_aktivitas', ['aksi' => 'pesanan.diubah']);
    }

    public function test_hapus_pesanan_dicatat_beserta_isinya(): void
    {
        $p = $this->pesanan();

        $this->sebagaiAdmin()->delete("/admin/order/{$p->id_pesanan}")->assertRedirect(route('admin.order.index'));

        $log = LogAktivitas::where('aksi', 'pesanan.dihapus')->first();
        $this->assertSame($p->id_pesanan, $log->id_subjek);
        $this->assertSame('Budi', $log->detail['pelanggan']);
        $this->assertSame('Laundry Kiloan', $log->detail['layanan']);
    }

    public function test_pembatalan_pelanggan_dicatat_tanpa_pelaku_admin(): void
    {
        $p = $this->pesanan();
        $akun = Pelanggan::first();

        $this->sebagaiAdmin();
        $this->actingAs($akun, 'pelanggan')->delete("/data-order/{$p->id_pesanan}");

        $log = LogAktivitas::where('aksi', 'pesanan.dibatalkan_pelanggan')->first();
        $this->assertNotNull($log);
        $this->assertNull($log->id_user);
        $this->assertSame($akun->id_pelanggan, $log->detail['id_pelanggan']);
    }

    public function test_halaman_audit_menampilkan_catatan_dan_menyaring_html(): void
    {
        $this->post('/admin/masuk', ['email' => '<script>alert(1)</script>@x.test', 'password' => 'salah']);
        LogAktivitas::catat('admin.masuk_gagal', email: '<b>jahat</b>@x.test');

        $this->sebagaiAdmin()->get('/admin/audit')
            ->assertOk()
            ->assertSee('Percobaan masuk gagal')
            ->assertSee('&lt;b&gt;jahat&lt;/b&gt;@x.test', false)
            ->assertDontSee('<b>jahat</b>', false);
    }

    public function test_halaman_audit_kosong_punya_keadaan_kosong(): void
    {
        $this->sebagaiAdmin();
        LogAktivitas::query()->delete();

        $this->get('/admin/audit')->assertOk()->assertSee('Belum ada catatan');
    }

    public function test_catatan_audit_tidak_bisa_diubah_atau_dihapus(): void
    {
        $log = LogAktivitas::catat('admin.masuk_gagal', email: 'a@x.test');

        $log->update(['aksi' => 'diubah']);
        $log->delete();

        $this->assertDatabaseHas('log_aktivitas', ['id' => $log->id, 'aksi' => 'admin.masuk_gagal']);
    }
}
