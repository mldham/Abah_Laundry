@extends('errors.layout')

@section('judul', 'Sedang dalam perawatan.')
@section('penjelasan', 'Layanan sedang diperbarui dan akan kembali sebentar lagi. Coba buka lagi beberapa menit lagi.')
@section('tindakan')
  <a class="tombol" href="{{ route('order.create') }}">Coba lagi</a>
@endsection
