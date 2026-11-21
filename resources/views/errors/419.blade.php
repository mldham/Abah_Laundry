@extends('errors.layout')

@section('judul', 'Halaman ini sudah kedaluwarsa.')
@section('penjelasan', 'Formulir terlalu lama dibiarkan terbuka, jadi pengirimannya ditolak demi keamanan. Tidak ada yang tersimpan. Buka lagi halamannya, isi ulang, lalu kirim.')
@section('tindakan')
  <a class="tombol" href="{{ url()->previous(route('order.create')) }}">Buka halaman tadi</a>
@endsection
