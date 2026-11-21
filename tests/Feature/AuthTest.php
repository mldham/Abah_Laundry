<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function dataDaftar(array $ubah = []): array
    {
        return array_merge([
            'nama'                  => 'Budi',
            'telepon'               => '0812 3456 7890',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $ubah);
    }

    private function akun(string $telepon = '081234567890'): Pelanggan
    {
        return Pelanggan::create(['nama' => 'Budi', 'no_telepon' => $telepon, 'password' => 'rahasia123']);
    }

    public function test_halaman_masuk_dan_daftar_tampil_untuk_tamu(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Nomor WhatsApp')->assertSee('Kata sandi');
        $this->get('/daftar')->assertOk()->assertSee('Buat akun');
    }

    public function test_daftar_membuat_akun_dan_langsung_masuk(): void
    {
        $this->post('/daftar', $this->dataDaftar())->assertRedirect(route('order.index'));

        $this->assertAuthenticated('pelanggan');
        $this->assertDatabaseHas('pelanggan', ['nama' => 'Budi', 'no_telepon' => '081234567890']);
        $this->assertNotSame('rahasia123', Pelanggan::first()->password);
    }

    public function test_daftar_memakai_baris_tamu_yang_sudah_ada_untuk_nomor_itu(): void
    {
        Pelanggan::create(['nama' => 'Budi Lama', 'no_telepon' => '081234567890']);

        $this->post('/daftar', $this->dataDaftar());

        $this->assertDatabaseCount('pelanggan', 1);
        $this->assertDatabaseHas('pelanggan', ['nama' => 'Budi']);
    }

    public function test_nomor_yang_sudah_punya_akun_ditolak(): void
    {
        $this->akun();

        $this->post('/daftar', $this->dataDaftar())->assertSessionHasErrors('telepon');

        $this->assertDatabaseCount('pelanggan', 1);
    }

    public function test_daftar_menolak_kata_sandi_pendek_dan_ulangan_beda(): void
    {
        $this->post('/daftar', $this->dataDaftar(['password' => '123', 'password_confirmation' => '123']))
            ->assertSessionHasErrors('password');

        $this->post('/daftar', $this->dataDaftar(['password_confirmation' => 'lain-lagi']))
            ->assertSessionHasErrors('password');

        $this->assertGuest('pelanggan');
    }

    public function test_masuk_dengan_nomor_berformat_apa_pun(): void
    {
        $this->akun();

        $this->post('/masuk', ['telepon' => '+62 812-3456-7890', 'password' => 'rahasia123'])
            ->assertRedirect(route('order.index'));

        $this->assertAuthenticated('pelanggan');
    }

    public function test_masuk_gagal_pesannya_sama_untuk_nomor_tak_terdaftar_dan_kata_sandi_salah(): void
    {
        $this->akun();

        $this->from('/masuk')->post('/masuk', ['telepon' => '081234567890', 'password' => 'salah'])
            ->assertSessionHasErrors(['telepon' => 'Nomor atau kata sandi salah.']);
        $this->assertGuest('pelanggan');

        $this->from('/masuk')->post('/masuk', ['telepon' => '089999999999', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['telepon' => 'Nomor atau kata sandi salah.']);
        $this->assertGuest('pelanggan');
    }

    public function test_baris_tamu_tanpa_kata_sandi_tidak_bisa_dipakai_masuk(): void
    {
        Pelanggan::create(['nama' => 'Tamu', 'no_telepon' => '081234567890']);

        $this->post('/masuk', ['telepon' => '081234567890', 'password' => ''])->assertSessionHasErrors('password');
        $this->post('/masuk', ['telepon' => '081234567890', 'password' => 'apa-saja'])->assertSessionHasErrors('telepon');

        $this->assertGuest('pelanggan');
    }

    public function test_tujuan_semula_diingat_sampai_selesai_masuk(): void
    {
        $this->akun();

        $this->get('/data-order')->assertRedirect(route('masuk'));
        $this->assertSame(url('/data-order'), session('url.intended'));

        $this->post('/masuk', ['telepon' => '081234567890', 'password' => 'rahasia123'])
            ->assertRedirect(url('/data-order'));
        $this->assertNull(session('url.intended'));
    }

    public function test_yang_sudah_masuk_dialihkan_dari_halaman_masuk_dan_daftar(): void
    {
        $this->actingAs($this->akun(), 'pelanggan');

        $this->get('/masuk')->assertRedirect(route('order.index'));
        $this->get('/daftar')->assertRedirect(route('order.index'));
    }

    public function test_keluar_mengakhiri_sesi(): void
    {
        $this->actingAs($this->akun(), 'pelanggan');

        $this->post('/keluar')->assertRedirect(route('order.create'));

        $this->assertGuest('pelanggan');
        $this->get('/data-order')->assertRedirect(route('masuk'));
    }

    public function test_navbar_menampilkan_masuk_untuk_tamu_dan_keluar_untuk_akun(): void
    {
        $this->get('/order')->assertSee('Masuk')->assertSee('Daftar')->assertDontSee('Keluar');

        $this->actingAs($this->akun(), 'pelanggan')
            ->get('/order')
            ->assertSee('Halo, Budi')
            ->assertSee('Keluar');
    }

    public function test_percobaan_masuk_dibatasi(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/masuk', ['telepon' => '081234567890', 'password' => 'salah']);
        }

        $this->post('/masuk', ['telepon' => '081234567890', 'password' => 'salah'])->assertStatus(429);
    }

    public function test_daftar_menampilkan_kabar_akun_dibuat_sekali_saja(): void
    {
        $this->post('/daftar', $this->dataDaftar());

        $this->get('/data-order')->assertSee('Akun dibuat.')->assertSee('Pesanan berikutnya tampil di halaman ini.');
        $this->get('/data-order')->assertDontSee('Akun dibuat.');
    }
}
