<?php

// Satu-satunya tempat daftar layanan dan harga.
// Sebelumnya harga ditulis tiga kali: data-harga di HTML, variabel di order.php, dan teks "Rp7.000 / kg".
return [
    'daftar' => [
        ['id' => 'kiloan',   'nama' => 'Laundry Kiloan',  'harga' => 7000,  'satuan' => 'kg',     'gambar' => 'Kiloan.png',  'lebar' => 120, 'deskripsi' => 'Cocok buat sehari-hari.'],
        ['id' => 'express',  'nama' => 'Laundry Express', 'harga' => 12000, 'satuan' => 'kg',     'gambar' => 'express.png', 'lebar' => 75,  'deskripsi' => 'Selesai 3 jam aje.'],
        ['id' => 'sepatu',   'nama' => 'Laundry Sepatu',  'harga' => 15000, 'satuan' => 'pasang', 'gambar' => 'sepatu.png',  'lebar' => 120, 'deskripsi' => 'Membersihkan sepatu kotor Anda.'],
        ['id' => 'selimut',  'nama' => 'Laundry Selimut', 'harga' => 20000, 'satuan' => 'pcs',    'gambar' => 'selimut.png', 'lebar' => 120, 'deskripsi' => 'ini Khusus buat selimut dan bed cover aja ye.'],
    ],

    // Alamat toko, dicetak di kepala struk. Ganti di sini kalau alamatnya berubah atau sudah lengkap.
    'toko' => [
        'jalan'     => 'Jl. In',
        'desa'      => 'Kandang Sapi',
        'kecamatan' => 'Kras',
        'kabupaten' => 'Fakfak',
    ],

    // Aturan ongkir dari order.php: alamat mengandung "bojongsoang" gratis, selain itu Rp2.000.
    'ongkir' => 2000,
    'area_gratis_ongkir' => 'bojongsoang',

    // Nomor WhatsApp admin untuk halaman Bantuan. Kosong = tombol hubungi tidak ditampilkan.
    // Isi lewat KONTAK_WHATSAPP di .env, contoh: 081234567890.
    'kontak_whatsapp' => env('KONTAK_WHATSAPP'),

    // Teks QRIS statis toko (bukan gambarnya), diawali 000201010211. Isi lewat QRIS_STATIS di .env.
    // Kosong = tombol bayar tetap tampil, tetapi halamannya memberi tahu bahwa QRIS belum diaktifkan.
    'qris' => [
        'statis' => env('QRIS_STATIS'),
    ],
];
