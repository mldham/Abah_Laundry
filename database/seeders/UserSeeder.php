<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kata sandi di bawah hanya untuk pengembangan, jangan sampai ter-seed di server sungguhan.
        if (app()->isProduction()) {
            return;
        }

        $users = [
            ['name' => 'Admin', 'email' => 'admin@gmail.com', 'password' => 'password123', 'peran' => User::PERAN_ADMIN],
            ['name' => 'Ilham Ganteng', 'email' => 'ilham@gmail.com', 'password' => 'password123', 'peran' => User::PERAN_ADMIN],
            ['name' => 'Gondrong keren', 'email' => 'test@gmail.com', 'password' => 'password123', 'peran' => User::PERAN_OPERATOR],
        ];

        // unguarded karena peran tidak ada di $fillable.
        User::unguarded(function () use ($users) {
            foreach ($users as $data) {
                User::updateOrCreate(['email' => $data['email']], $data + ['aktif' => true]);
            }
        });
    }
}
