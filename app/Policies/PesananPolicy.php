<?php

namespace App\Policies;

use App\Models\Pesanan;
use App\Models\User;

class PesananPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->punyaPeran(User::PERAN_ADMIN, User::PERAN_OPERATOR);
    }

    // Struk hanya dibaca, jadi sama dengan yang boleh membuka daftar.
    public function view(User $user, Pesanan $pesanan): bool
    {
        return $user->punyaPeran(User::PERAN_ADMIN, User::PERAN_OPERATOR);
    }

    public function update(User $user, Pesanan $pesanan): bool
    {
        return $user->punyaPeran(User::PERAN_ADMIN, User::PERAN_OPERATOR);
    }

    // Menghapus tidak bisa dibatalkan, jadi hanya admin.
    public function delete(User $user, Pesanan $pesanan): bool
    {
        return $user->adalahAdmin();
    }
}
