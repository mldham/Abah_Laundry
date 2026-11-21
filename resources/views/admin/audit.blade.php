@extends('layouts.app')

@section('title', 'Audit aktivitas')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}" />
@endpush

@section('content')
<main class="admin-page">
  <header class="admin-heading">
    <h1>Audit aktivitas</h1>
  </header>

  <section class="admin-panel" aria-labelledby="judul-audit">
    <div class="panel-heading">
      <h2 id="judul-audit">Catatan terbaru</h2>
      <p class="keterangan-audit">Catatan hanya bertambah dan tidak bisa diubah dari aplikasi. Waktu dalam WIB.</p>
    </div>

    <div class="tabel-scroll">
      <table class="tabel-admin tabel-audit">
        <thead>
          <tr>
            <th scope="col">Waktu</th>
            <th scope="col">User</th>
            <th scope="col">Aksi</th>
            <th scope="col">Rincian</th>
            <th scope="col">Alamat IP</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($log as $entri)
            <tr>
              <td>
                <time datetime="{{ $entri->created_at->toIso8601String() }}">
                  {{ $entri->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                </time>
              </td>
              <td>
                @if ($entri->pelaku)
                  <strong>{{ $entri->pelaku->name }}</strong>
                  <span class="rincian">{{ $entri->pelaku->label_peran }}</span>
                @elseif ($entri->email_dicoba)
                  <strong>{{ $entri->email_dicoba }}</strong>
                  <span class="rincian">Email yang diketik</span>
                @else
                  <span class="rincian">Pelanggan atau tamu</span>
                @endif
              </td>
              <td>
                {{ $entri->label }}
                @if ($entri->subjek)
                  <span class="rincian">{{ $entri->subjek }} #{{ $entri->id_subjek }}</span>
                @endif
              </td>
              <td class="rincian">{{ $entri->ringkasan }}</td>
              <td class="rincian">{{ $entri->ip_address }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5">
                <div class="kosong-admin">
                  <strong>Belum ada catatan</strong>
                  <p>Catatan muncul setelah ada yang masuk, mengubah, atau menghapus pesanan.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($log->previousPageUrl() || $log->nextPageUrl())
      <nav class="halaman-audit" aria-label="Halaman catatan">
        @if ($log->previousPageUrl())
          <a class="tombol-garis" href="{{ $log->previousPageUrl() }}" rel="prev">Lebih baru</a>
        @endif
        @if ($log->nextPageUrl())
          <a class="tombol-garis" href="{{ $log->nextPageUrl() }}" rel="next">Lebih lama</a>
        @endif
      </nav>
    @endif
  </section>
</main>
@endsection
