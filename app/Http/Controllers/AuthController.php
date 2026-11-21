<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const ATURAN_TELEPON = ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'];

    private const PESAN = [
        'required'         => ':attribute wajib diisi.',
        'max'              => ':attribute terlalu panjang.',
        'telepon.regex'    => 'Nomor WhatsApp tidak valid. Contoh yang benar: 081234567890.',
        'password.min'     => 'Kata sandi minimal 6 karakter.',
        'password.confirmed' => 'Kata sandi dan ulangannya tidak sama.',
    ];

    private const NAMA = [
        'nama'     => 'Nama lengkap',
        'telepon'  => 'Nomor WhatsApp',
        'password' => 'Kata sandi',
    ];

    public function formMasuk(): View
    {
        return view('auth.masuk');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'telepon'  => self::ATURAN_TELEPON,
            'password' => ['required', 'string'],
        ], self::PESAN, self::NAMA);

        $telepon = Pelanggan::normalisasiTelepon($data['telepon']);

        $akun = Pelanggan::where('no_telepon', $telepon)->whereNotNull('password')->first();

        if (! $akun || ! Hash::check($data['password'], $akun->password)) {
            return back()
                ->withInput($request->only('telepon', 'ingat'))
                ->withErrors(['telepon' => 'Nomor atau kata sandi salah.']);
        }

        Auth::guard('pelanggan')->login($akun, $request->boolean('ingat'));
        $request->session()->regenerate();

        return redirect()->intended(route('order.index'));
    }

    public function formDaftar(): View
    {
        return view('auth.daftar');
    }

    public function daftar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'telepon'  => self::ATURAN_TELEPON,
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], self::PESAN, self::NAMA);

        $telepon = Pelanggan::normalisasiTelepon($data['telepon']);

        if (Pelanggan::where('no_telepon', $telepon)->whereNotNull('password')->exists()) {
            return back()
                ->withInput($request->only('nama', 'telepon'))
                ->withErrors(['telepon' => 'Nomor ini sudah punya akun. Silakan masuk.']);
        }

        $akun = Pelanggan::where('no_telepon', $telepon)
            ->whereNull('password')
            ->orderByDesc('id_pelanggan')
            ->first() ?? new Pelanggan(['no_telepon' => $telepon]);

        $akun->fill(['nama' => $data['nama'], 'password' => $data['password']])->save();

        Auth::guard('pelanggan')->login($akun);
        $request->session()->regenerate();

        return redirect()->intended(route('order.index'))->with('akun_baru', true);
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::guard('pelanggan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('order.create');
    }
}
