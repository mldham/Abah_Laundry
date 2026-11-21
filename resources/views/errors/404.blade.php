@extends('errors.layout')

@section('judul', 'Halaman tidak ditemukan.')
@section('penjelasan', 'Alamat yang dibuka tidak ada atau sudah dipindah. Cek lagi tulisannya, atau mulai dari halaman order.')
@section('tindakan')
  <a class="tombol" href="{{ route('order.create') }}">Ke halaman order</a>
@endsection
