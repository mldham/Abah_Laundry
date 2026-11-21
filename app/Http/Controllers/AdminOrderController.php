<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(): View
    {
        $pesanan = Pesanan::with(['pelanggan', 'komplain'])->orderByDesc('id_pesanan')->get();

        return view('admin.order', compact('pesanan'));
    }

    // Umpan balik disimpan di session 'aksi'. Yang selesai tampil sebagai pita hijau di atas halaman
    // (admin/notifikasi.blade.php, hilang sendiri setelah 5 detik). Yang gagal tampil sebagai baris di bawah
    // baris yang salah (admin/hasil.blade.php). Redirect membawa anchor supaya baris itu langsung terlihat.
    public function update(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $id = $pesanan->id_pesanan;
        $beratField = 'total_berat_'.$id;
        $statusField = 'status_'.$id;

        $validator = Validator::make($request->all(), [
            $beratField => ['required', 'integer', 'min:0', 'max:100000'],
            $statusField => ['required', Rule::in($pesanan->statusUntukAdmin())],
        ], [
            $beratField.'.required' => 'Isi berat atau jumlah pesanan.',
            $beratField.'.integer' => 'Berat atau jumlah harus berupa angka bulat.',
            $beratField.'.min' => 'Berat atau jumlah tidak boleh di bawah 0.',
            $beratField.'.max' => 'Berat atau jumlah terlalu besar. Maksimal 100.000.',
            $statusField.'.required' => 'Pilih status pesanan.',
            $statusField.'.in' => 'Status yang dipilih tidak dikenal.',
        ]);

        // Cucian yang belum ditimbang atau dihitung belum bisa dikerjakan atau selesai: total harganya masih Rp0.
        $validator->after(function ($v) use ($beratField, $statusField, $pesanan) {
            if ($v->errors()->hasAny([$beratField, $statusField])) {
                return;
            }

            $data = $v->getData();

            if ($data[$statusField] !== 'pending' && (int) $data[$beratField] === 0) {
                $v->errors()->add($beratField, 'Isi berat atau jumlah dulu sebelum status diubah dari "Menunggu dijemput".');
            }

            // Pesanan yang sudah lunas tidak boleh berubah totalnya diam-diam. Batalkan status lunas dulu.
            if ($pesanan->status_bayar === 'lunas' && (int) $data[$beratField] !== (int) $pesanan->total_berat) {
                $v->errors()->add($beratField, 'Pesanan ini sudah lunas, jadi berat atau jumlahnya tidak bisa diubah. Batalkan status lunas dulu.');
            }
        });

        if ($validator->fails()) {
            return $this->kembaliKeBaris($id)
                ->withErrors($validator)
                ->withInput()
                ->with('aksi', ['jenis' => 'galat', 'id' => $id, 'judul' => 'Belum tersimpan.']);
        }

        $data = $validator->validated();
        $totalBerat = (int) $data[$beratField];
        $infoLayanan = collect(config('layanan.daftar'))->firstWhere('nama', $pesanan->layanan);
        $satuan = $infoLayanan['satuan'] ?? 'unit';
        $hargaSatuan = (int) ($pesanan->harga ?: ($infoLayanan['harga'] ?? 0));
        $totalHarga = $totalBerat * $hargaSatuan;

        $sebelum = $pesanan->only(['total_berat', 'total_harga', 'status', 'status_bayar']);

        $ubahan = [
            'total_berat' => $totalBerat,
            'total_harga' => $totalHarga,
            'status' => $data[$statusField],
        ];

        // Waktu pengantaran mengikuti status. Kembali ke tahap sebelum diantar menghapus keduanya,
        // dan dari diterima kembali ke diantar (misalnya pelanggan salah tekan) menghapus waktu terima.
        if ($data[$statusField] === 'diantar' && $pesanan->status !== 'diantar') {
            $ubahan['diantar_pada'] = $pesanan->status === 'diterima' ? $pesanan->diantar_pada : now();
            $ubahan['diterima_pada'] = null;
        } elseif (! in_array($data[$statusField], ['diantar', 'diterima'], true)) {
            $ubahan['diantar_pada'] = null;
            $ubahan['diterima_pada'] = null;
        }

        // Kode QRIS yang sudah dibayar pelanggan memuat nominal lama. Kalau totalnya berubah,
        // laporan "sudah bayar" itu tidak berlaku lagi dan pelanggan membayar sesuai total baru.
        if ($pesanan->status_bayar === 'menunggu_verifikasi' && $totalHarga !== (int) $pesanan->total_harga) {
            $ubahan['status_bayar'] = 'belum_bayar';
            $ubahan['metode_bayar'] = null;
        }

        $pesanan->update($ubahan);

        if ($pesanan->wasChanged()) {
            // Dua kolom waktu ikut berubah bersama status, jadi tidak perlu muncul lagi di jejak audit.
            $perubahan = Arr::except($pesanan->getChanges(), ['diantar_pada', 'diterima_pada']);

            LogAktivitas::catat('pesanan.diubah', $pesanan, [
                'sebelum' => array_intersect_key($sebelum, $perubahan),
                'sesudah' => $perubahan,
            ]);
        }

        // Hanya sebut yang benar-benar berubah.
        $ubah = [];
        if ($pesanan->wasChanged('status')) {
            $ubah[] = 'status jadi '.mb_strtolower(Pesanan::LABEL_STATUS[$pesanan->status]);
        }
        if ($pesanan->wasChanged('total_berat')) {
            $ubah[] = $totalBerat.' '.$satuan;
        }
        if ($pesanan->wasChanged('total_harga') || $pesanan->wasChanged('total_berat')) {
            $ubah[] = 'total Rp'.number_format($totalHarga, 0, ',', '.');
        }
        if ($pesanan->wasChanged('status_bayar')) {
            $ubah[] = 'laporan bayar dibatalkan karena total berubah';
        }

        $aksi = $ubah === []
            ? ['jenis' => 'tetap', 'id' => $id, 'judul' => 'Tidak ada perubahan.', 'isi' => 'Berat dan status masih sama seperti sebelumnya.']
            : ['jenis' => 'ubah', 'id' => $id, 'judul' => 'Tersimpan.', 'isi' => ucfirst(implode(', ', $ubah)).'.'];

        return $this->kembaliKeBaris($id)->with('aksi', $aksi);
    }

    // Admin mencocokkan laporan bayar dengan mutasi QRIS: tandai lunas, atau kembalikan ke belum dibayar.
    public function bayar(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $id = $pesanan->id_pesanan;
        $baru = $request->input('status_bayar');

        abort_unless(in_array($baru, ['lunas', 'belum_bayar'], true), 422);

        if ($baru === 'lunas' && $pesanan->total_harga <= 0) {
            return $this->kembaliKeBaris($id)
                ->withErrors(['status_bayar_'.$id => 'Isi berat atau jumlah dulu. Pesanan ini belum punya total untuk dilunasi.'])
                ->with('aksi', ['jenis' => 'galat', 'id' => $id, 'judul' => 'Belum tersimpan.']);
        }

        $sebelum = $pesanan->status_bayar;

        if ($sebelum === $baru) {
            return $this->kembaliKeBaris($id)->with('aksi', [
                'jenis' => 'tetap', 'id' => $id,
                'judul' => 'Tidak ada perubahan.',
                'isi' => 'Status pembayaran masih sama seperti sebelumnya.',
            ]);
        }

        $pesanan->update([
            'status_bayar' => $baru,
            // Lunas tanpa laporan dari pelanggan berarti dicatat manual (misalnya bayar tunai).
            'metode_bayar' => $baru === 'lunas' ? ($pesanan->metode_bayar ?: 'manual') : null,
            'dibayar_pada' => $baru === 'lunas' ? now() : null,
        ]);

        LogAktivitas::catat($baru === 'lunas' ? 'pembayaran.dikonfirmasi' : 'pembayaran.dibatalkan', $pesanan, [
            'sebelum' => ['status_bayar' => $sebelum],
            'sesudah' => ['status_bayar' => $baru],
            'nominal' => $pesanan->total_bayar,
        ]);

        return $this->kembaliKeBaris($id)->with('aksi', [
            'jenis' => 'ubah', 'id' => $id,
            'judul' => 'Tersimpan.',
            'isi' => $baru === 'lunas'
                ? "Pesanan #{$id} ditandai lunas, Rp".number_format($pesanan->total_bayar, 0, ',', '.').'.'
                : "Pesanan #{$id} dikembalikan ke belum dibayar.",
        ]);
    }

    // Struk dibuka di tab baru dari daftar, jadi admin tetap di daftarnya (saringan dan pencarian tidak hilang).
    public function struk(Pesanan $pesanan): View
    {
        return view('struk', [
            'pesanan' => $pesanan->load('pelanggan'),
            'kembali' => route('admin.order.index').'#pesanan-'.$pesanan->id_pesanan,
            'kembaliTeks' => 'ke daftar pesanan',
        ]);
    }

    public function destroy(Pesanan $pesanan): RedirectResponse
    {
        $id = $pesanan->id_pesanan;
        $nama = $pesanan->pelanggan->nama ?? 'pelanggan';
        $layanan = $pesanan->layanan;

        // Isi pesanan dicatat sebelum dihapus karena barisnya tidak akan ada lagi.
        $isi = ['pelanggan' => $nama, 'layanan' => $layanan]
            + $pesanan->only(['status', 'total_berat', 'total_harga']);

        $pesanan->delete();

        LogAktivitas::catat('pesanan.dihapus', $pesanan, $isi);

        return redirect()->route('admin.order.index')->with('aksi', [
            'jenis' => 'hapus',
            'id' => null,
            'judul' => 'Dihapus.',
            'isi' => "Pesanan #{$id} atas nama {$nama} ({$layanan}) sudah dihapus.",
        ]);
    }

    private function kembaliKeBaris(int $id): RedirectResponse
    {
        return redirect()->to(route('admin.order.index').'#pesanan-'.$id);
    }
}
