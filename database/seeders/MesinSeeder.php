<?php

namespace Database\Seeders;

use App\Models\Mesin;
use Illuminate\Database\Seeder;

class MesinSeeder extends Seeder
{
    public function run(): void
    {
        $daftarMesin = [
            ['kode' => 'MS-001', 'nama' => 'Mesin Cuci 1',  'jenis' => 'Mesin Cuci',     'merek' => 'Electrolux', 'kapasitas' => 10,   'status' => 'Beroperasi'],
            ['kode' => 'MS-002', 'nama' => 'Mesin Cuci 2',  'jenis' => 'Mesin Cuci',     'merek' => 'LG',         'kapasitas' => 12,   'status' => 'Beroperasi'],
            ['kode' => 'MS-003', 'nama' => 'Mesin Cuci 3',  'jenis' => 'Mesin Cuci',     'merek' => 'Samsung',    'kapasitas' => 8,    'status' => 'Perawatan'],
            ['kode' => 'MS-004', 'nama' => 'Mesin Cuci 4',  'jenis' => 'Mesin Cuci',     'merek' => 'Sharp',      'kapasitas' => 7,    'status' => 'Rusak'],
            ['kode' => 'MS-005', 'nama' => 'Pengering 1',   'jenis' => 'Pengering',      'merek' => 'Electrolux', 'kapasitas' => 10,   'status' => 'Beroperasi'],
            ['kode' => 'MS-006', 'nama' => 'Pengering 2',   'jenis' => 'Pengering',      'merek' => 'LG',         'kapasitas' => 12,   'status' => 'Perawatan'],
            ['kode' => 'MS-007', 'nama' => 'Setrika Uap',   'jenis' => 'Peralatan Lain', 'merek' => 'Philips',    'kapasitas' => null, 'status' => 'Beroperasi'],
            ['kode' => 'MS-008', 'nama' => 'Mesin Press',   'jenis' => 'Peralatan Lain', 'merek' => 'Maxim',      'kapasitas' => null, 'status' => 'Beroperasi'],
        ];

        foreach ($daftarMesin as $mesin) {
            Mesin::updateOrCreate(
                ['kode' => $mesin['kode']],
                $mesin
            );
        }
    }
}
