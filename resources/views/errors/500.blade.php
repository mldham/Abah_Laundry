@extends('errors.layout')

@section('judul', 'Ada masalah di pihak kami.')
@section('penjelasan', 'Permintaanmu belum selesai diproses. Coba lagi sebentar lagi. Kalau pesanan baru saja dikirim, cek dulu di Cek pesanan sebelum mengirim ulang supaya tidak dobel.')
@section('tindakan')
  <a class="tombol" href="{{ route('order.index') }}">Cek pesanan</a>
@endsection
