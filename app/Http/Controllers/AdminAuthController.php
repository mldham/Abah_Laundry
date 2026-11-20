<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function formMasuk(): View
    {
        return view('admin.masuk');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'email'    => 'Format email tidak valid.',
            'max'      => ':attribute terlalu panjang.',
        ], [
            'email'    => 'Email',
            'password' => 'Kata sandi',
        ]);

        // Akun nonaktif gagal dengan pesan yang sama, supaya email terdaftar tidak bisa ditebak.
        $berhasil = Auth::guard('admin')->attempt([
            'email'    => $data['email'],
            'password' => $data['password'],
            'aktif'    => true,
        ]);

        if (! $berhasil) {
            LogAktivitas::catat('admin.masuk_gagal', email: $data['email']);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        LogAktivitas::catat('admin.masuk');

        return redirect()->intended(route('admin.order.index'));
    }

    public function keluar(Request $request): RedirectResponse
    {
        LogAktivitas::catat('admin.keluar');

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.masuk');
    }
}
