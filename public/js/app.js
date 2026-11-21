// Dipakai semua halaman lewat layouts/app.blade.php:
// 1. kunci tombol kirim saat formulir diproses (form berpenanda data-kunci),
// 2. fokus ke ringkasan galat setelah halaman dimuat ulang,
// 3. dialog konfirmasi untuk aksi yang tidak bisa dibatalkan (form berpenanda data-konfirmasi),
// 4. laci menu di layar kecil (tombol Menu, Escape, klik latar),
// 5. dua penunjang gerak: garis navbar menebal saat digulir, dan bagian halaman panjang muncul saat didatangi.
//    Gayanya ada di motion.css.
// Halaman admin punya logika sendiri di admin.js.
// Dibungkus fungsi supaya nama di dalamnya tidak bocor ke lingkup global:
// app.js dan admin.js dimuat di halaman yang sama, dan nama yang sama (misalnya dialog)
// di dua skrip biasa membuat skrip kedua gagal total.
(() => {
'use strict';


// ---------- konfirmasi (didaftarkan lebih dulu supaya bisa membatalkan pengiriman sebelum dikunci) ----------

const dialog = document.getElementById('dialog-konfirmasi');
let formTertunda = null;

document.addEventListener('submit', (event) => {
  const form = event.target.closest('[data-konfirmasi]');

  if (!form) return;

  // Tanpa <dialog> (browser lama) konfirmasi tetap jalan lewat jendela bawaan.
  if (!dialog || typeof dialog.showModal !== 'function') {
    if (!window.confirm(form.dataset.judul + '?')) event.preventDefault();
    return;
  }

  event.preventDefault();
  formTertunda = form;
  dialog.querySelector('[data-dialog-judul]').textContent = form.dataset.judul + '?';
  dialog.querySelector('[data-dialog-rincian]').textContent = form.dataset.rincian;
  dialog.showModal();
});

if (dialog) {
  dialog.querySelector('[data-dialog-batal]').addEventListener('click', () => dialog.close());

  dialog.querySelector('[data-dialog-ya]').addEventListener('click', (event) => {
    if (!formTertunda) return;

    kunciTombol(event.currentTarget);
    formTertunda.submit();
  });

  // klik di luar kotak dialog menutupnya, sama seperti Escape
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
}

// ---------- tombol terkunci saat proses ----------

function kunciTombol(tombol) {
  if (!tombol || tombol.disabled) return;

  tombol.dataset.teksAwal = tombol.textContent;
  tombol.textContent = tombol.dataset.teksProses || 'Memproses...';
  tombol.disabled = true;
}

document.addEventListener('submit', (event) => {
  if (event.defaultPrevented) return;
  if (!event.target.closest('[data-kunci]')) return;

  kunciTombol(event.submitter);
});

// Tombol "kembali" di browser memulihkan halaman dari cache dengan tombol masih terkunci.
window.addEventListener('pageshow', (event) => {
  if (!event.persisted) return;

  document.querySelectorAll('button[data-teks-awal]').forEach((tombol) => {
    tombol.textContent = tombol.dataset.teksAwal;
    tombol.disabled = false;
    delete tombol.dataset.teksAwal;
  });
});

// ---------- galat setelah halaman dimuat ulang ----------

const ringkasanGalat = document.querySelector('[data-fokus-galat]');

if (ringkasanGalat) ringkasanGalat.focus();

// ---------- gerak: navbar saat digulir ----------

const navbar = document.querySelector('.navbar');

if (navbar) {
  let tergulir = false;

  const periksaGulir = () => {
    const sekarang = window.scrollY > 4;

    if (sekarang === tergulir) return;

    tergulir = sekarang;
    navbar.toggleAttribute('data-gulir', sekarang);
  };

  window.addEventListener('scroll', periksaGulir, { passive: true });
  periksaGulir();
}

// ---------- menu samping di layar kecil ----------
// Di bawah 960px menu menjadi laci dari kiri yang dibuka tombol Menu. Tombol dan gaya laci baru aktif
// di sini (data-drawer), jadi tanpa JavaScript menu tetap tampil sebagai daftar di bawah logo.

const tombolMenu = document.getElementById('tombol-menu');

if (navbar && tombolMenu) {
  const daftarMenu = document.getElementById('menu-utama');

  const aturMenu = (buka) => {
    navbar.toggleAttribute('data-buka', buka);
    tombolMenu.setAttribute('aria-expanded', String(buka));
    tombolMenu.textContent = buka ? 'Tutup' : 'Menu';
  };

  navbar.setAttribute('data-drawer', '');
  tombolMenu.hidden = false;

  tombolMenu.addEventListener('click', () => {
    const buka = !navbar.hasAttribute('data-buka');

    aturMenu(buka);
    if (buka) daftarMenu.querySelector('a, button').focus();
  });

  // klik di latar gelap (di luar laci) menutupnya, sama seperti Escape
  navbar.addEventListener('click', (event) => {
    if (event.target === navbar) aturMenu(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !navbar.hasAttribute('data-buka')) return;

    aturMenu(false);
    tombolMenu.focus();
  });

  // layar dilebarkan sampai navigasi jadi sisi kiri tetap: laci tidak boleh tertinggal terbuka
  window.matchMedia('(min-width: 960px)').addEventListener('change', (event) => {
    if (event.matches) aturMenu(false);
  });
}

// ---------- gerak: bagian muncul saat digulir ----------
// Hanya bagian yang masih di bawah layar yang disembunyikan, jadi tidak ada kedipan di bagian atas,
// dan tanpa JavaScript atau dengan "kurangi gerak" semuanya tampil seperti biasa.

const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!kurangiGerak && 'IntersectionObserver' in window) {
  const pengamat = new IntersectionObserver((daftar) => {
    daftar.forEach((entri) => {
      if (!entri.isIntersecting) return;

      entri.target.classList.add('tampak');
      pengamat.unobserve(entri.target);
    });
  }, { rootMargin: '0px 0px -8% 0px' });

  document.querySelectorAll('.bantuan section, .riwayat li').forEach((bagian) => {
    if (bagian.getBoundingClientRect().top < window.innerHeight) return;

    bagian.classList.add('reveal');
    pengamat.observe(bagian);
  });
}

})();
