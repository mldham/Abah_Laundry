@extends('errors.layout')

@section('judul', 'Akses ditolak.')
@section('penjelasan', 'Akun ini tidak punya izin untuk halaman atau aksi tersebut. Kalau seharusnya bisa, minta admin mengubah peran akunmu.')
@section('tindakan')
  <a class="tombol" href="{{ route('admin.order.index') }}">Ke daftar pesanan</a>
@endsection
