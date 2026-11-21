// Filter kategori dan pencarian di halaman Kelola stock (client-side, tanpa muat ulang).
(() => {
  "use strict";

  const tabel = document.getElementById("bahanTable");
  const cari = document.getElementById("searchInput");
  if (!tabel || !cari) return;

  const baris = tabel.querySelectorAll("tbody tr[data-kategori]");
  const kosong = document.getElementById("hasil-kosong");
  const tombolFilter = document.querySelectorAll(".pil[data-kategori]");
  let kategoriAktif = "all";

  function terapkan() {
    const kata = cari.value.trim().toLowerCase();
    let tampil = 0;

    baris.forEach((tr) => {
      const cocok =
        (kategoriAktif === "all" || tr.dataset.kategori === kategoriAktif) &&
        tr.textContent.toLowerCase().includes(kata);

      tr.hidden = !cocok;
      if (cocok) tampil++;
    });

    if (kosong) kosong.hidden = tampil > 0 || baris.length === 0;
  }

  tombolFilter.forEach((tombol) => {
    tombol.addEventListener("click", () => {
      kategoriAktif = tombol.dataset.kategori;
      tombolFilter.forEach((t) =>
        t.setAttribute("aria-pressed", String(t === tombol)),
      );
      terapkan();
    });
  });

  cari.addEventListener("input", terapkan);
})();
