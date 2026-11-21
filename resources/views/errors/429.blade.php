@extends('errors.layout')

@php $detik = (int) ($exception->getHeaders()['Retry-After'] ?? 60); @endphp

@section('judul', 'Terlalu banyak percobaan.')
@section('penjelasan', 'Demi keamanan akun, percobaan dibatasi. Tunggu sekitar '.max(1, (int) ceil($detik / 60)).' menit, lalu coba lagi.')
@section('tindakan')
  <a class="tombol" href="{{ url()->previous(route('masuk')) }}">Kembali dan coba lagi nanti</a>
@endsection
