// Halaman admin: hitung total langsung, konfirmasi hapus lewat <dialog>,
// tombol terkunci saat proses, notifikasi hijau yang hilang sendiri setelah 5 detik,
// dan perpindahan ke pesan gagal setelah halaman dimuat ulang.
// Dibungkus fungsi supaya nama di dalamnya tidak bocor ke lingkup global:
// app.js dan admin.js dimuat di halaman yang sama, dan nama yang sama (misalnya dialog)
// di dua skrip biasa membuat skrip kedua gagal total.
(() => {
'use strict';


const rupiah = (angka) => 'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(angka);

// ---------- total harga langsung ----------

document.addEventListener('input', (event) => {
  const input = event.target.closest('[data-berat]');

  if (!input) return;

  const baris = input.closest('tr');
  const tarif = Number(baris.dataset.tarif || 0);
  const jumlah = Math.max(0, Number(input.value || 0));

  baris.querySelector('[data-total]').textContent = rupiah(Math.round(jumlah * tarif));
});

// ---------- tombol terkunci saat proses ----------

function kunciTombol(tombol) {
  if (!tombol || tombol.disabled) return;

  tombol.dataset.teksAwal = tombol.textContent;
  tombol.textContent = tombol.dataset.teksProses || 'Memproses...';
  tombol.disabled = true;
}

function bukaKunci() {
  document.querySelectorAll('button[data-teks-awal]').forEach((tombol) => {
    tombol.textContent = tombol.dataset.teksAwal;
    tombol.disabled = false;
    delete tombol.dataset.teksAwal;
  });
}

// Tombol "kembali" di browser memulihkan halaman dari cache dengan tombol masih terkunci.
window.addEventListener('pageshow', (event) => {
  if (event.persisted) bukaKunci();
});

// ---------- konfirmasi hapus ----------

const dialog = document.getElementById('dialog-hapus');
let formHapus = null;

document.addEventListener('submit', (event) => {
  const form = event.target.closest('[data-hapus]');

  if (form) {
    // Tanpa <dialog> (browser lama) hapus tetap jalan lewat konfirmasi bawaan.
    if (!dialog || typeof dialog.showModal !== 'function') {
      if (!window.confirm(form.dataset.judul + '?')) event.preventDefault();
      return;
    }

    event.preventDefault();
    formHapus = form;
    dialog.querySelector('[data-dialog-judul]').textContent = form.dataset.judul + '?';
    dialog.querySelector('[data-dialog-rincian]').textContent = form.dataset.rincian;
    dialog.showModal();
    return;
  }

  kunciTombol(event.submitter);
});

if (dialog) {
  dialog.querySelector('[data-dialog-batal]').addEventListener('click', () => dialog.close());

  dialog.querySelector('[data-dialog-hapus]').addEventListener('click', (event) => {
    if (!formHapus) return;

    kunciTombol(event.currentTarget);
    formHapus.submit();
  });

  // klik di luar kotak dialog menutupnya, sama seperti Escape
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
}

// ---------- saring dan cari ----------

const kolomCari = document.getElementById('cari-pesanan');
const tombolSaring = document.querySelectorAll('[data-saring]');
const kotakHasilSaring = document.querySelector('[data-hasil-saring]');

if (kolomCari && kotakHasilSaring) {
  const barisData = document.querySelectorAll('tr[data-status]');
  const teksHasil = kotakHasilSaring.querySelector('[data-hasil-saring-teks]');
  let statusAktif = 'semua';

  const terapkan = () => {
    const kata = kolomCari.value.trim().toLowerCase();
    let tampil = 0;

    barisData.forEach((baris) => {
      const cocokStatus = statusAktif === 'semua' || baris.dataset.status === statusAktif;
      const cocokKata = kata === '' || baris.dataset.cari.includes(kata);
      // Baris yang sedang menampilkan pesan gagal tetap terlihat supaya pesannya tidak hilang.
      const tampilkan = (cocokStatus && cocokKata) || baris.classList.contains('baris-galat');

      baris.hidden = !tampilkan;

      const berikutnya = baris.nextElementSibling;
      if (berikutnya && berikutnya.classList.contains('hasil-aksi')) berikutnya.hidden = !tampilkan;

      if (tampilkan) tampil += 1;
    });

    const disaring = statusAktif !== 'semua' || kata !== '';

    kotakHasilSaring.hidden = !disaring;

    if (!disaring) return;

    teksHasil.textContent = tampil === 0
      ? 'Tidak ada pesanan yang cocok. Hapus kata pencarian atau pilih Semua.'
      : 'Menampilkan ' + tampil + ' dari ' + barisData.length + ' pesanan.';
  };

  tombolSaring.forEach((tombol) => {
    tombol.addEventListener('click', () => {
      statusAktif = tombol.dataset.saring;
      tombolSaring.forEach((t) => t.setAttribute('aria-pressed', String(t === tombol)));
      terapkan();
    });
  });

  kolomCari.addEventListener('input', terapkan);

  kotakHasilSaring.querySelector('[data-atur-ulang]').addEventListener('click', () => {
    statusAktif = 'semua';
    kolomCari.value = '';
    tombolSaring.forEach((t) => t.setAttribute('aria-pressed', String(t.dataset.saring === 'semua')));
    terapkan();
    kolomCari.focus();
  });
}

// ---------- notifikasi aksi selesai ----------

const WAKTU_NOTIFIKASI = 5000;
const notifikasi = document.querySelector('[data-notifikasi]');

if (notifikasi) {
  let penghitung = null;

  // Lama garis di CSS mengikuti angka yang sama dengan timer.
  notifikasi.style.setProperty('--waktu', WAKTU_NOTIFIKASI + 'ms');

  const sembunyikan = () => {
    clearTimeout(penghitung);

    // Fokus yang ada di dalam pita (tombol Tutup) dikembalikan ke judul daftar, bukan hilang ke <body>.
    const fokusDiDalam = notifikasi.contains(document.activeElement);

    document.querySelectorAll('.baris-baru').forEach((baris) => baris.classList.remove('baris-baru'));

    if (fokusDiDalam) {
      const judul = document.getElementById('judul-daftar-pesanan');
      if (judul) judul.focus();
    }

    // Pita naik pelan lalu dibuang (gerak keluar ada di motion.css). Dengan "kurangi gerak"
    // animasinya 0,01ms, jadi pita tetap hilang langsung. Batas 400ms menjaga pita tetap terbuang
    // kalau animationend tidak pernah datang.
    notifikasi.classList.add('keluar');
    // animationend dari garis penghitung di dalam pita ikut naik ke sini, jadi hanya yang dari pita sendiri dihitung.
    notifikasi.addEventListener('animationend', (event) => {
      if (event.target === notifikasi) notifikasi.remove();
    });
    setTimeout(() => notifikasi.remove(), 400);
  };

  const mulaiHitung = () => {
    clearTimeout(penghitung);
    notifikasi.classList.remove('berhenti');
    penghitung = setTimeout(sembunyikan, WAKTU_NOTIFIKASI);
  };

  const tahanHitung = () => {
    clearTimeout(penghitung);
    notifikasi.classList.add('berhenti');
  };

  // Selama kursor atau fokus ada di pita, hitungan dan garis berhenti supaya sempat dibaca.
  // Setelah lepas, keduanya mulai lagi dari 5 detik penuh.
  notifikasi.addEventListener('mouseenter', tahanHitung);
  notifikasi.addEventListener('mouseleave', mulaiHitung);
  notifikasi.addEventListener('focusin', tahanHitung);
  notifikasi.addEventListener('focusout', mulaiHitung);

  notifikasi.querySelector('[data-notifikasi-tutup]').addEventListener('click', sembunyikan);

  mulaiHitung();
}

// ---------- pesan gagal setelah redirect ----------

const hasil = document.querySelector('[data-hasil]');

if (hasil) {
  const barisHasil = hasil.closest('tr');
  const barisAsal = barisHasil.previousElementSibling;
  const punyaBaris = barisAsal && barisAsal.matches('.baris-baru, .baris-galat');

  const tutup = document.createElement('button');
  tutup.type = 'button';
  tutup.className = 'hasil-tutup';
  tutup.textContent = 'Tutup';
  tutup.addEventListener('click', () => {
    if (punyaBaris) {
      barisAsal.classList.remove('baris-baru', 'baris-galat');
    }
    barisHasil.remove();
    history.replaceState(null, '', location.pathname + location.search);

    const tujuan = punyaBaris
      ? barisAsal.querySelector('input, select')
      : document.getElementById('judul-daftar-pesanan');
    if (tujuan) tujuan.focus();
  });
  hasil.appendChild(tutup);

  // Pindah ke hasilnya dan bacakan ke pembaca layar. Tanpa animasi gulir.
  hasil.scrollIntoView({ block: 'center' });
  hasil.focus({ preventScroll: true });
}

})();
