<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->query('cari', '');
        $hapusId = $request->query('hapus');

        // Hanya role admin yang boleh memunculkan dialog konfirmasi hapus
        $isAdmin = Auth::guard('admin')->user()?->peran === 'admin';
        $konfirmasi = ($isAdmin && $hapusId) ? Pelanggan::find($hapusId) : null;

        $daftar = Pelanggan::when($cari !== '', function ($query) use ($cari) {
            $query->where('nama', 'like', "%{$cari}%")
                  ->orWhere('no_telepon', 'like', "%{$cari}%");
        })->orderByDesc('id_pelanggan')->get();

        return view('pelanggan.index', compact('daftar', 'cari', 'konfirmasi'));
    }

    public function create()
    {
        return view('pelanggan.form', [
            'pelanggan' => new Pelanggan(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'       => ['required', 'string', 'max:255'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'alamat'     => ['required', 'string'],
            'catatan'    => ['nullable', 'string'],
        ]);

        Pelanggan::create($validated);

        return redirect()->route('pelanggan.index')->with('sukses', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.form', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validated = $request->validate([
            'nama'       => ['required', 'string', 'max:255'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'alamat'     => ['required', 'string'],
            'catatan'    => ['nullable', 'string'],
        ]);

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')->with('sukses', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        abort_if(Auth::guard('admin')->user()?->peran !== 'admin', 403, 'Hanya admin yang dapat menghapus data pelanggan.');

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('sukses', 'Data pelanggan berhasil dihapus.');
    }
}