// Struk pesanan, dipakai dua tempat:
// 1. Halaman struk penuh (admin, dan cadangan pelanggan tanpa JavaScript): tombol Cetak membuka dialog cetak
//    browser, dari situ bisa juga disimpan sebagai PDF.
// 2. Cek pesanan: tautan "Lihat struk" membuka struk sebagai popup di atas halaman dengan latar buram.
//    Isinya diambil dari halaman struk yang sama, jadi tampilan layar dan kertasnya tidak ada dua versi.
// Tombol Cetak baru ditampilkan di sini, karena tanpa JavaScript ia tidak bisa berbuat apa-apa.
(() => {
'use strict';

// ---------- halaman struk penuh ----------

const tombolCetak = document.querySelector('[data-cetak]');

if (tombolCetak) {
  tombolCetak.hidden = false;
  tombolCetak.addEventListener('click', () => window.print());
}

// ---------- popup struk di Cek pesanan ----------

const dialog = document.getElementById('dialog-struk');

if (dialog && typeof dialog.showModal === 'function') {
  const isi = dialog.querySelector('[data-struk-isi]');
  const judul = dialog.querySelector('[data-struk-judul]');
  const cetak = dialog.querySelector('[data-struk-cetak]');
  let pemicu = null;
  let permintaan = 0;

  const tulisPesan = (teks, tautan) => {
    const p = document.createElement('p');

    p.className = 'struk-popup-pesan';
    p.textContent = teks;

    if (tautan) {
      const a = document.createElement('a');

      a.href = tautan;
      a.textContent = 'Buka struk di halaman penuh';
      p.append(' ', a, '.');
    }

    isi.replaceChildren(p);
  };

  const buka = async (tautan) => {
    const nomor = ++permintaan;

    pemicu = tautan;
    judul.textContent = tautan.dataset.judul || 'Struk pesanan';
    cetak.hidden = true;
    tulisPesan('Memuat struk...');

    if (!dialog.open) dialog.showModal();

    try {
      const respons = await fetch(tautan.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });

      if (!respons.ok) throw new Error(String(respons.status));

      const dokumen = new DOMParser().parseFromString(await respons.text(), 'text/html');
      const struk = dokumen.querySelector('.struk');

      if (!struk) throw new Error('tanpa struk');
      if (nomor !== permintaan) return;

      isi.replaceChildren(document.importNode(struk, true));
      cetak.hidden = false;
    } catch (galat) {
      if (nomor !== permintaan) return;

      tulisPesan('Struk belum bisa dimuat.', tautan.href);
    }
  };

  document.addEventListener('click', (event) => {
    const tautan = event.target.closest('[data-struk]');

    // Ctrl, Cmd, Shift, atau klik tengah tetap membuka tautan biasa di tab baru.
    if (!tautan || event.defaultPrevented || event.button !== 0) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    event.preventDefault();
    buka(tautan);
  });

  dialog.querySelector('[data-struk-tutup]').addEventListener('click', () => dialog.close());

  // klik di luar kotak popup menutupnya, sama seperti Escape
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  dialog.addEventListener('close', () => {
    permintaan++;
    isi.replaceChildren();
    if (pemicu) pemicu.focus();
  });

  // Cetak: halaman di belakang disembunyikan lewat CSS cetak, jadi yang keluar di kertas hanya struknya.
  cetak.addEventListener('click', () => window.print());
}
})();
