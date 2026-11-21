<?php

namespace App\Http\Controllers;

use App\Models\Komplain;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminKomplainController extends Controller
{
    // Yang perlu tindakan di atas: baru, lalu sedang ditangani, lalu selesai. Dalam satu kelompok yang terbaru dulu.
    public function index(Request $request): View
    {
        $saring = $request->query('status');
        $saring = array_key_exists((string) $saring, Komplain::STATUS) ? $saring : null;

        $hitung = Komplain::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        $daftar = Komplain::with(['pesanan', 'pelanggan', 'penanggap'])
            ->when($saring, fn ($q) => $q->where('status', $saring))
            ->orderByRaw("case status when 'baru' then 0 when 'diproses' then 1 else 2 end")
            ->orderByDesc('id_komplain')
            ->get();

        return view('admin.komplain', [
            'daftar' => $daftar,
            'saring' => $saring,
            'hitung' => $hitung,
            'semua'  => $hitung->sum(),
        ]);
    }

    // Nama kolom memakai nomor komplain (status_3, tanggapan_3) supaya galat tampil di kartu yang benar,
    // sama seperti halaman Kelola pesanan. Menandai selesai butuh tanggapan: pelanggan perlu tahu hasilnya.
    public function update(Request $request, Komplain $komplain): RedirectResponse
    {
        $id = $komplain->id_komplain;
        $statusField = 'status_'.$id;
        $tanggapanField = 'tanggapan_'.$id;

        $validator = Validator::make($request->all(), [
            $statusField => ['required', Rule::in(array_keys(Komplain::STATUS))],
            $tanggapanField => ['nullable', 'string', 'max:1000'],
        ], [
            $statusField.'.required' => 'Pilih status komplain.',
            $statusField.'.in' => 'Status tidak valid.',
            $tanggapanField.'.max' => 'Tanggapan terlalu panjang. Maksimal 1000 huruf.',
        ]);

        $validator->after(function ($v) use ($request, $komplain, $statusField, $tanggapanField) {
            $tanggapan = trim((string) $request->input($tanggapanField, ''));

            if ($request->input($statusField) === 'selesai' && $tanggapan === '') {
                $v->errors()->add($tanggapanField, 'Tulis tanggapan sebelum menandai selesai, supaya pelanggan tahu hasilnya.');
            }
        });

        if ($validator->fails()) {
            return redirect()->to(route('admin.komplain.index').'#komplain-'.$id)
                ->withErrors($validator)
                ->withInput();
        }

        $status = $request->input($statusField);
        $tanggapan = trim((string) $request->input($tanggapanField, '')) ?: null;

        $sebelum = [
            'status' => $komplain->status,
            'tanggapan' => $komplain->tanggapan ? 'ada' : 'kosong',
        ];

        $komplain->fill([
            'status' => $status,
            'tanggapan' => $tanggapan,
        ]);

        if (! $komplain->isDirty()) {
            return redirect()->to(route('admin.komplain.index').'#komplain-'.$id)
                ->with('aksi', ['jenis' => 'tetap', 'judul' => "Komplain #{$id} tidak berubah.", 'isi' => 'Status dan tanggapan masih sama seperti sebelumnya.']);
        }

        $komplain->id_user = auth('admin')->id();
        $komplain->selesai_pada = $status === 'selesai' ? ($komplain->getOriginal('selesai_pada') ?? now()) : null;
        $komplain->save();

        LogAktivitas::catat('komplain.ditanggapi', $komplain, [
            'sebelum' => $sebelum,
            'sesudah' => [
                'status' => $status,
                'tanggapan' => $tanggapan ? 'ada' : 'kosong',
            ],
        ]);

        return redirect()->to(route('admin.komplain.index').'#komplain-'.$id)
            ->with('aksi', ['jenis' => 'ubah', 'judul' => "Komplain #{$id} diperbarui.", 'isi' => 'Pelanggan melihat tanggapan ini di halaman Cek pesanan.']);
    }
}
