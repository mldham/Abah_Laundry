<?php $__env->startSection('title', 'Admin Laundry Abah'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin.css'))); ?>" />
<?php $__env->stopPush(); ?>

<?php
  $label = \App\Models\Pesanan::LABEL_STATUS;
  $aksi = session('aksi');
  $tampilNotifikasi = $aksi && $aksi['jenis'] !== 'galat';
  $jumlahStatus = $pesanan->countBy('status');
?>

<?php $__env->startSection('content'); ?>
<?php if($tampilNotifikasi): ?>
  <?php echo $__env->make('admin.notifikasi', ['aksi' => $aksi], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<main class="admin-page">
  <header class="admin-heading">
    <h1>Manajemen pesanan</h1>
    <div class="admin-count" aria-label="Jumlah pesanan saat ini">
      <span>Total pesanan</span>
      <strong><?php echo e($pesanan->count()); ?></strong>
    </div>
  </header>

  <section class="admin-panel" aria-labelledby="judul-daftar-pesanan">
    <div class="panel-heading">
      <h2 id="judul-daftar-pesanan" tabindex="-1">Daftar pesanan</h2>
    </div>

    
    <?php if($pesanan->isNotEmpty()): ?>
      <div class="alat-admin">
        <div class="saring" role="group" aria-label="Saring menurut status">
          <button type="button" class="saring-tombol" data-saring="semua" aria-pressed="true">
            Semua <span><?php echo e($pesanan->count()); ?></span>
          </button>
          <?php $__currentLoopData = \App\Models\Pesanan::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" class="saring-tombol" data-saring="<?php echo e($status); ?>" aria-pressed="false">
              <?php echo e($label[$status]); ?> <span><?php echo e($jumlahStatus[$status] ?? 0); ?></span>
            </button>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="cari-pesanan">
          <label class="sr-only" for="cari-pesanan">Cari pesanan</label>
          <input type="search" id="cari-pesanan" placeholder="Cari nama, nomor telepon, atau #pesanan" autocomplete="off" />
        </div>
      </div>
      <div class="hasil-saring" data-hasil-saring role="status" hidden>
        <p data-hasil-saring-teks></p>
        <button type="button" class="tombol-garis" data-atur-ulang>Tampilkan semua</button>
      </div>
    <?php endif; ?>

    <div class="tabel-scroll">
      <table class="tabel-admin">
        <thead>
          <tr>
            <th scope="col">Pesanan</th>
            <th scope="col">Layanan</th>
            <th scope="col">Berat atau jumlah</th>
            <th scope="col">Total harga</th>
            <th scope="col">Status</th>
            <th scope="col">Pembayaran <span class="ket-kolom">dengan ongkir</span></th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $pesanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
              $infoLayanan = collect(config('layanan.daftar'))->firstWhere('nama', $p->layanan);
              $satuan = $infoLayanan['satuan'] ?? 'unit';
              $hargaSatuan = (int) ($p->harga ?: ($infoLayanan['harga'] ?? 0));
              $formId = 'form-pesanan-'.$p->id_pesanan;
              $beratField = 'total_berat_'.$p->id_pesanan;
              $statusField = 'status_'.$p->id_pesanan;
              $beratSekarang = old($beratField, $p->total_berat);
              $barisIni = $aksi && ($aksi['id'] ?? null) == $p->id_pesanan;
              $galatBaris = $barisIni ? collect($errors->get($beratField))->merge($errors->get($statusField))->merge($errors->get('status_bayar_'.$p->id_pesanan)) : collect();
            ?>

            <tr id="pesanan-<?php echo e($p->id_pesanan); ?>"
                data-tarif="<?php echo e($hargaSatuan); ?>"
                data-status="<?php echo e($p->status); ?>"
                data-cari="<?php echo e(mb_strtolower($p->pelanggan->nama.' '.$p->no_telepon.' #'.$p->id_pesanan.' '.$p->layanan)); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses(['baris-baru' => $barisIni && $galatBaris->isEmpty(), 'baris-galat' => $barisIni && $galatBaris->isNotEmpty()]); ?>">
              <td>
                <div class="identitas-pesanan">
                  <strong><?php echo e($p->pelanggan->nama); ?></strong>
                  <span>#<?php echo e($p->id_pesanan); ?>, <?php echo e($p->no_telepon); ?></span>
                  <?php $komplainTerbuka = $p->komplain->where('status', '!=', 'selesai'); ?>
                  <?php if($komplainTerbuka->isNotEmpty()): ?>
                    <a class="tautan-komplain" href="<?php echo e(route('admin.komplain.index')); ?>#komplain-<?php echo e($komplainTerbuka->first()->id_komplain); ?>">Komplain <?php echo e($komplainTerbuka->contains('status', 'baru') ? 'baru' : 'ditangani'); ?><?php if($komplainTerbuka->count() > 1): ?> (<?php echo e($komplainTerbuka->count()); ?>)<?php endif; ?></a>
                  <?php endif; ?>
                  <a class="tautan-struk" href="<?php echo e(route('admin.order.struk', $p)); ?>" target="_blank" rel="noopener">Cetak struk<span class="sr-only"> pesanan #<?php echo e($p->id_pesanan); ?> (buka di tab baru)</span></a>
                </div>
              </td>
              <td>
                <div class="info-layanan">
                  <strong><?php echo e($p->layanan); ?></strong>
                  <span>Rp<?php echo e(number_format($hargaSatuan, 0, ',', '.')); ?> / <?php echo e($satuan); ?></span>
                </div>
              </td>
              <td>
                <label class="sr-only" for="berat-<?php echo e($p->id_pesanan); ?>">
                  <?php echo e($satuan === 'kg' ? 'Berat pesanan dalam kilogram' : 'Jumlah pesanan dalam '.$satuan); ?> #<?php echo e($p->id_pesanan); ?>

                </label>
                <div class="sel-berat">
                  <div class="input-berat">
                    <input
                      id="berat-<?php echo e($p->id_pesanan); ?>"
                      type="number"
                      name="<?php echo e($beratField); ?>"
                      form="<?php echo e($formId); ?>"
                      value="<?php echo e($beratSekarang); ?>"
                      min="0"
                      max="100000"
                      step="1"
                      inputmode="numeric"
                      data-berat
                      <?php if($errors->has($beratField)): ?> aria-invalid="true" <?php endif; ?>
                    />
                    <span><?php echo e($satuan); ?></span>
                  </div>
                  <?php if((int) $p->total_berat === 0): ?>
                    <span class="belum-ditimbang">Belum ditimbang</span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div class="harga-hasil" aria-live="polite">
                  <strong data-total>Rp<?php echo e(number_format((int) $beratSekarang * $hargaSatuan, 0, ',', '.')); ?></strong>
                </div>
              </td>
              <td>
                <label class="sr-only" for="status-<?php echo e($p->id_pesanan); ?>">Status pesanan #<?php echo e($p->id_pesanan); ?></label>
                <div class="sel-status">
                  <select id="status-<?php echo e($p->id_pesanan); ?>" name="<?php echo e($statusField); ?>" form="<?php echo e($formId); ?>"
                          <?php if($errors->has($statusField)): ?> aria-invalid="true" <?php endif; ?>>
                    <?php $__currentLoopData = $p->statusUntukAdmin(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($status); ?>" <?php if(old($statusField, $p->status) === $status): echo 'selected'; endif; ?>><?php echo e($label[$status]); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                  
                  <?php if($p->status === 'diterima' && $p->diterima_pada): ?>
                    <span class="ket-status">Dikonfirmasi pelanggan, <?php echo e($p->diterima_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M, H.i')); ?></span>
                  <?php elseif($p->status === 'diantar' && $p->diantar_pada): ?>
                    <span class="ket-status">Berangkat <?php echo e($p->diantar_pada->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M, H.i')); ?></span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div class="sel-bayar">
                  <?php if($p->total_harga > 0): ?>
                    <div class="info-bayar">
                      <span class="label-bayar" data-bayar="<?php echo e($p->status_bayar); ?>"><?php echo e($p->label_bayar); ?></span>
                      <span class="nominal-bayar">Rp<?php echo e(number_format($p->total_bayar, 0, ',', '.')); ?></span>
                    </div>
                    <div class="aksi-bayar">
                      <?php if($p->status_bayar !== 'lunas'): ?>
                        <form action="<?php echo e(route('admin.order.bayar', $p)); ?>" method="POST">
                          <?php echo csrf_field(); ?>
                          <?php echo method_field('PUT'); ?>
                          <input type="hidden" name="status_bayar" value="lunas" />
                          <button type="submit" class="tombol-bayar-admin utama" data-teks-proses="Menyimpan...">Tandai lunas</button>
                        </form>
                      <?php endif; ?>
                      <?php if($p->status_bayar !== 'belum_bayar'): ?>
                        <form action="<?php echo e(route('admin.order.bayar', $p)); ?>" method="POST">
                          <?php echo csrf_field(); ?>
                          <?php echo method_field('PUT'); ?>
                          <input type="hidden" name="status_bayar" value="belum_bayar" />
                          <button type="submit" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['tombol-bayar-admin', 'bahaya' => $p->status_bayar === 'lunas']); ?>" data-teks-proses="Menyimpan..."><?php echo e($p->status_bayar === 'lunas' ? 'Batalkan lunas' : 'Belum masuk'); ?></button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="belum-ditimbang">Belum ada total</span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div class="aksi-pesanan">
                  <form id="<?php echo e($formId); ?>" action="<?php echo e(route('admin.order.update', $p)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <button type="submit" class="tombol-simpan" data-teks-proses="Menyimpan...">Simpan</button>
                  </form>
                  <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $p)): ?>
                    <form
                      action="<?php echo e(route('admin.order.destroy', $p)); ?>"
                      method="POST"
                      data-hapus
                      data-judul="Hapus pesanan #<?php echo e($p->id_pesanan); ?>"
                      data-rincian="<?php echo e($p->pelanggan->nama); ?>, <?php echo e($p->layanan); ?>"
                    >
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>
                      <button type="submit" class="tombol-hapus">Hapus</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>

            <?php if($barisIni && $galatBaris->isNotEmpty()): ?>
              <?php echo $__env->make('admin.hasil', ['aksi' => $aksi, 'galat' => $galatBaris], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7">
                <div class="kosong-admin">
                  <strong>Belum ada pesanan</strong>
                  <p>Pesanan pelanggan akan muncul di halaman ini setelah mereka melakukan order.</p>
                  <a href="<?php echo e(route('order.create')); ?>">Lihat halaman order</a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <dialog class="dialog-hapus" id="dialog-hapus" aria-labelledby="dialog-hapus-judul">
    <h2 id="dialog-hapus-judul" data-dialog-judul>Hapus pesanan?</h2>
    <p>Pesanan yang dihapus tidak bisa dikembalikan.</p>
    <p class="dialog-rincian" data-dialog-rincian></p>
    <div class="dialog-aksi">
      <button type="button" class="tombol-batal" data-dialog-batal autofocus>Batal</button>
      <button type="button" class="tombol-hapus tombol-hapus-final" data-dialog-hapus data-teks-proses="Menghapus...">Hapus pesanan</button>
    </div>
  </dialog>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  
  <script src="<?php echo e(asset('js/admin.js')); ?>?v=<?php echo e(filemtime(public_path('js/admin.js'))); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/admin/order.blade.php ENDPATH**/ ?>