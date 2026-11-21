<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\View\View;

class BantuanController extends Controller
{
    // Semua angka di halaman Bantuan dibaca dari config/layanan.php dan model Pesanan,
    // supaya tidak ada harga atau aturan yang ditulis dua kali.
    public function __invoke(): View
    {
        $nomor = preg_replace('/\D+/', '', (string) config('layanan.kontak_whatsapp'));

        if ($nomor !== '' && str_starts_with($nomor, '0')) {
            $nomor = '62'.substr($nomor, 1);
        }

        return view('bantuan', [
            'layanan'  => config('layanan.daftar'),
            'ongkir'   => config('layanan.ongkir'),
            'areaGratis' => ucfirst(config('layanan.area_gratis_ongkir')),
            'status'   => Pesanan::STATUS,
            'label'    => Pesanan::LABEL_STATUS,
            'penjelasan' => Pesanan::PENJELASAN_STATUS,
            'linkWa'   => $nomor !== '' ? 'https://wa.me/'.$nomor : null,
        ]);
    }
}
