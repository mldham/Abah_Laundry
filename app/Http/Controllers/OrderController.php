<?php

namespace App\Http\Controllers;

use App\Models\Komplain;
use App\Models\LogAktivitas;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Support\Qris;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    // pengganti order.html
    public function create(): View
    {
        $akun = Auth::guard('pelanggan')->user();

        // Yang sudah masuk tidak perlu mengetik ulang alamat: diisi dari pesanan terakhirnya.
        $alamatTerakhir = $akun
            ? Pesanan::where('no_telepon', Pelanggan::normalisasiTelepon($akun->no_telepon))
                ->orderByDesc('id_pesanan')
                ->value('alamat')
            : null;

        return view('order', [
            'layanan' => config('layanan.daftar'),
            'alamatTerakhir' => $alamatTerakhir,
            'ongkir' => config('layanan.ongkir'),
            'areaGratis' => ucfirst(config('layanan.area_gratis_ongkir')),
        ]);
    }

    // pengganti order.php
    public function store(Request $request): RedirectResponse
    {
        $daftar = collect(config('layanan.daftar'))->keyBy('nama');
        $akun = Auth::guard('pelanggan')->user();

        $aturan = [
            'layanan' => ['required', Rule::in($daftar->keys()->all())],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'alamat'  => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
        ];

        // Yang sudah masuk memakai nama dan nomor dari akunnya, jadi dua kolom ini tidak diminta.
        if (! $akun) {
            $aturan['nama'] = ['required', 'string', 'max:100'];
            $aturan['telepon'] = ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'];
        }

        $data = $request->validate($aturan, [
            'layanan.required' => 'Pilih salah satu layanan.',
            'layanan.in'       => 'Layanan tidak valid.',
            'telepon.regex'    => 'Nomor WhatsApp tidak valid. Contoh yang benar: 081234567890.',
            'required'         => ':attribute wajib diisi.',
            'date'             => ':attribute tidak valid.',
            'after_or_equal'   => ':attribute tidak boleh sebelum hari ini.',
            'max'              => ':attribute terlalu panjang.',
        ], [
            'nama'    => 'Nama lengkap',
            'telepon' => 'Nomor WhatsApp',
            'tanggal' => 'Tanggal jemput',
            'alamat'  => 'Alamat jemput',
        ]);

        // Disimpan dalam bentuk seragam (08...) supaya pencarian di halaman Cek Pesanan cocok.
        $telepon = Pelanggan::normalisasiTelepon($akun ? $akun->no_telepon : $data['telepon']);

        $ongkir = Str::contains(Str::lower($data['alamat']), config('layanan.area_gratis_ongkir'))
            ? 0
            : config('layanan.ongkir');

        $baru = DB::transaction(function () use ($data, $daftar, $ongkir, $telepon, $akun) {
            $pelanggan = $akun ?? Pelanggan::create([
                'nama'       => $data['nama'],
                'no_telepon' => $telepon,
            ]);

            return $pelanggan->pesanan()->create([
                'layanan'        => $data['layanan'],
                'harga'          => $daftar[$data['layanan']]['harga'],
                'no_telepon'     => $telepon,
                'tanggal_pickup' => $data['tanggal'],
                'alamat'         => $data['alamat'],
                'notes'          => $data['catatan'] ?? null,
                'ongkir'         => $ongkir,
            ]);
        });

        // Isi flash dipakai view: nomor pesanan untuk menandai kartunya di Cek Pesanan,
        // dan jadwal jemput untuk kabar di halaman daftar.
        $kabar = [
            'id'     => $baru->id_pesanan,
            'jemput' => $baru->tanggal_pickup->locale('id')->translatedFormat('l, j F Y'),
        ];

        if ($akun) {
            return redirect()
                ->route('order.index')
                ->with('pesanan_baru', $kabar);
        }

        // Tamu: tawarkan akun dengan nama dan nomor yang baru diisi, supaya order berikutnya
        // dan Cek Pesanan tidak perlu mengisi lagi.
        return redirect()
            ->route('daftar')
            ->withInput(['nama' => $data['nama'], 'telepon' => $telepon])
            ->with('pesanan_baru', $kabar);
    }

    // Pelanggan membatalkan pesanannya sendiri selama belum dijemput. Dibatalkan = dihapus,
    // jadi admin tidak lagi melihatnya. Pesanan orang lain dijawab 404, bukan "dilarang",
    // supaya nomor pesanan tidak bisa ditebak.
    public function batal(Pesanan $pesanan): RedirectResponse
    {
        $akun = Auth::guard('pelanggan')->user();

        abort_unless($pesanan->no_telepon === Pelanggan::normalisasiTelepon($akun->no_telepon), 404);

        $id = $pesanan->id_pesanan;
        $layanan = $pesanan->layanan;

        if (! $pesanan->bisaDibatalkan()) {
            return redirect()->route('order.index')->with('batal', [
                'berhasil' => false,
                'judul' => "Pesanan #{$id} tidak bisa dibatalkan.",
                'isi' => 'Cuciannya sudah dijemput atau sedang dikerjakan.',
            ]);
        }

        $pesanan->delete();

        LogAktivitas::catat('pesanan.dibatalkan_pelanggan', $pesanan, [
            'id_pelanggan' => $akun->getKey(),
            'layanan' => $layanan,
        ]);

        return redirect()->route('order.index')->with('batal', [
            'berhasil' => true,
            'judul' => "Pesanan #{$id} dibatalkan.",
            'isi' => "{$layanan} dihapus dari daftar pesananmu.",
        ]);
    }

    // Halaman bayar: kode QRIS dinamis berisi nominal pesanan (harga cucian + ongkir).
    // Pesanan orang lain dijawab 404, sama seperti saat membatalkan.
    public function bayar(Pesanan $pesanan): View|RedirectResponse
    {
        $this->pastikanMilik($pesanan);

        if ($pesanan->total_harga <= 0) {
            return $this->kabarBayar(false, "Pesanan #{$pesanan->id_pesanan} belum bisa dibayar.", 'Totalnya baru ada setelah cucian ditimbang atau dihitung.');
        }

        $qr = $penerima = $galat = null;

        // Kode hanya ditampilkan selama belum dilaporkan atau dilunasi, supaya tidak dibayar dua kali.
        if ($pesanan->status_bayar === 'belum_bayar') {
            $statis = (string) config('layanan.qris.statis');

            if (blank($statis)) {
                $galat = 'QRIS belum diaktifkan oleh toko. Hubungi admin lewat halaman Bantuan.';
            } else {
                try {
                    $payload = Qris::dinamis($statis, $pesanan->total_bayar);
                    $penerima = Qris::nilai($payload, '59');
                    $qr = Qris::svg($payload);
                } catch (Throwable $e) {
                    report($e);
                    $galat = 'Kode QRIS belum bisa dibuat. Hubungi admin lewat halaman Bantuan.';
                }
            }
        }

        return view('bayar', [
            'pesanan' => $pesanan,
            'qr' => $qr,
            'penerima' => $penerima,
            'galat' => $galat,
        ]);
    }

    // Pelanggan melapor sudah membayar. Statusnya baru jadi Lunas setelah admin mencocokkan dengan mutasi QRIS.
    public function laporBayar(Pesanan $pesanan): RedirectResponse
    {
        $akun = $this->pastikanMilik($pesanan);

        if (! $pesanan->bisaDibayar()) {
            $isi = $pesanan->status_bayar === 'lunas'
                ? 'Pembayarannya sudah tercatat lunas.'
                : 'Totalnya baru ada setelah cucian ditimbang atau dihitung.';

            return $this->kabarBayar(false, "Pesanan #{$pesanan->id_pesanan} tidak perlu dibayar lagi.", $isi);
        }

        if ($pesanan->status_bayar !== 'menunggu_verifikasi') {
            $pesanan->update(['status_bayar' => 'menunggu_verifikasi', 'metode_bayar' => 'qris']);

            LogAktivitas::catat('pembayaran.dilaporkan', $pesanan, [
                'id_pelanggan' => $akun->getKey(),
                'metode' => 'qris',
                'nominal' => $pesanan->total_bayar,
            ]);
        }

        return $this->kabarBayar(true, "Pembayaran pesanan #{$pesanan->id_pesanan} dilaporkan.", 'Admin akan mencocokkannya dengan mutasi QRIS, lalu statusnya berubah jadi Lunas.');
    }

    // Struk pesanan untuk dicetak atau disimpan sebagai PDF lewat dialog cetak browser.
    // Pesanan orang lain dijawab 404, sama seperti saat membatalkan dan membayar.
    public function struk(Pesanan $pesanan): View|RedirectResponse
    {
        $this->pastikanMilik($pesanan);

        // Tombol "Lihat struk" baru tampil setelah cucian diterima, jadi alamatnya juga ditutup sebelum itu.
        if ($pesanan->status !== 'diterima') {
            return $this->kabarTerima(false, "Struk pesanan #{$pesanan->id_pesanan} belum bisa dilihat.", 'Struk tersedia setelah kamu menekan "Cucian sudah diterima".');
        }

        return view('struk', [
            'pesanan' => $pesanan->load('pelanggan'),
            'kembali' => route('order.index'),
            'kembaliTeks' => 'ke Cek pesanan',
        ]);
    }

    // Pelanggan mengonfirmasi cuciannya sudah sampai. Hanya dari status diantar, jadi tidak ada cara
    // melompati tahap. Pesanan orang lain dijawab 404, sama seperti saat membatalkan dan membayar.
    public function terima(Pesanan $pesanan): RedirectResponse
    {
        $akun = $this->pastikanMilik($pesanan);
        $id = $pesanan->id_pesanan;

        if ($pesanan->status === 'diterima') {
            return $this->kabarTerima(true, "Pesanan #{$id} sudah kamu terima.", 'Tidak ada yang perlu dilakukan lagi.');
        }

        if (! $pesanan->bisaDiterima()) {
            return $this->kabarTerima(false, "Pesanan #{$id} belum bisa dikonfirmasi.", 'Cuciannya belum ditandai sedang diantar.');
        }

        $pesanan->update(['status' => 'diterima', 'diterima_pada' => now()]);

        LogAktivitas::catat('pesanan.diterima_pelanggan', $pesanan, [
            'id_pelanggan' => $akun->getKey(),
            'layanan' => $pesanan->layanan,
        ]);

        return $this->kabarTerima(true, "Pesanan #{$id} diterima.", 'Terima kasih sudah memakai Laundry Abah.');
    }

    // Formulir komplain. Pesanan orang lain dijawab 404. Pesanan yang belum waktunya dikomplain, atau yang
    // komplain sebelumnya masih ditangani, dikembalikan ke Cek Pesanan dengan alasannya.
    public function komplain(Pesanan $pesanan): View|RedirectResponse
    {
        $this->pastikanMilik($pesanan);

        if ($alasan = $this->alasanTidakBisaKomplain($pesanan)) {
            return $this->kabarKomplain(false, "Pesanan #{$pesanan->id_pesanan} belum bisa dikomplain.", $alasan);
        }

        return view('komplain', ['pesanan' => $pesanan]);
    }

    public function kirimKomplain(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $akun = $this->pastikanMilik($pesanan->load('komplain'));

        if ($alasan = $this->alasanTidakBisaKomplain($pesanan)) {
            return $this->kabarKomplain(false, "Pesanan #{$pesanan->id_pesanan} belum bisa dikomplain.", $alasan);
        }

        $data = $request->validate([
            'jenis'     => ['required', Rule::in(array_keys(Komplain::JENIS))],
            'deskripsi' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'jenis.required'     => 'Pilih masalah yang paling mendekati.',
            'jenis.in'           => 'Pilihan masalah tidak valid.',
            'deskripsi.required' => 'Ceritakan masalahnya supaya bisa kami cek.',
            'deskripsi.min'      => 'Ceritakan sedikit lebih lengkap, minimal 10 huruf.',
            'deskripsi.max'      => 'Terlalu panjang. Maksimal 1000 huruf.',
        ]);

        $komplain = Komplain::create([
            'id_pesanan'   => $pesanan->id_pesanan,
            'id_pelanggan' => $akun->getKey(),
            'jenis'        => $data['jenis'],
            'deskripsi'    => trim($data['deskripsi']),
            'status'       => 'baru',
        ]);

        LogAktivitas::catat('komplain.dibuat', $komplain, [
            'pesanan' => $pesanan->id_pesanan,
            'jenis'   => $data['jenis'],
        ]);

        return $this->kabarKomplain(true, 'Komplain terkirim.', 'Admin akan menanggapi, dan jawabannya tampil di kartu pesanan ini.');
    }

    private function alasanTidakBisaKomplain(Pesanan $pesanan): ?string
    {
        if ($pesanan->bisaDikomplain()) {
            return null;
        }

        return $pesanan->punyaKomplainTerbuka()
            ? 'Komplain sebelumnya masih ditangani. Tunggu tanggapan admin dulu.'
            : 'Komplain bisa dikirim setelah cucian diantar.';
    }

    private function kabarKomplain(bool $berhasil, string $judul, string $isi): RedirectResponse
    {
        return redirect()->route('order.index')->with('komplain', compact('berhasil', 'judul', 'isi'));
    }

    private function kabarTerima(bool $berhasil, string $judul, string $isi): RedirectResponse
    {
        return redirect()->route('order.index')->with('terima', compact('berhasil', 'judul', 'isi'));
    }

    private function pastikanMilik(Pesanan $pesanan): Pelanggan
    {
        $akun = Auth::guard('pelanggan')->user();

        abort_unless($pesanan->no_telepon === Pelanggan::normalisasiTelepon($akun->no_telepon), 404);

        return $akun;
    }

    private function kabarBayar(bool $berhasil, string $judul, string $isi): RedirectResponse
    {
        return redirect()->route('order.index')->with('bayar', compact('berhasil', 'judul', 'isi'));
    }

    // Halaman Cek Pesanan: pesanan milik nomor WhatsApp akun yang sedang masuk.
    public function index(): View
    {
        $akun = Auth::guard('pelanggan')->user();

        $pesanan = Pesanan::with('komplain')
            ->where('no_telepon', Pelanggan::normalisasiTelepon($akun->no_telepon))
            ->orderByDesc('id_pesanan')
            ->limit(20)
            ->get();

        return view('data-order', ['akun' => $akun, 'pesanan' => $pesanan]);
    }
}
