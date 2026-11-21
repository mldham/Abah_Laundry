<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT Mencari Cinta Sejati',
                'no_telp'       => '081234567890',
                'alamat'        => 'Jl. Soekarno Hatta No. 112, Bandung',
            ],
            [
                'nama_supplier' => 'CV Harumnyo Oi Oi',
                'no_telp'       => '082198765432',
                'alamat'        => 'Jl. Raya Bojongsoang No. 45, Bandung',
            ],
            [
                'nama_supplier' => 'UD Sabun Mandi Kali',
                'no_telp'       => '085711223344',
                'alamat'        => 'Jl. Buah Batu No. 88, Bandung',
            ],
            [
                'nama_supplier' => 'Toko Grosir Pak Jumat',
                'no_telp'       => '081355667788',
                'alamat'        => 'Jl. Terusan Buah Batu No. 21, Bandung',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::firstOrCreate(
                ['nama_supplier' => $data['nama_supplier']],
                $data
            );
        }
    }
}