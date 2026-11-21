{{-- Baris pesan gagal di tabel admin, tepat di bawah baris yang salah. Bahan: $galat (daftar pesan gagal) dan $aksi (isi flash 'aksi'). --}}
<tr class="hasil-aksi hasil-galat">
  <td colspan="7">
    <div class="hasil" data-hasil tabindex="-1" role="alert">
      <div>
        <p class="hasil-judul">{{ $aksi['judul'] }}</p>
        <ul>
          @foreach ($galat as $pesan)
            <li>{{ $pesan }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </td>
</tr>
