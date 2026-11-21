<?php $__env->startSection('title', 'Order Laundry'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/order.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php
  $alamatIsi = old('alamat', $alamatTerakhir ?? '');
  $alamatDariTerakhir = old('alamat') === null && filled($alamatTerakhir ?? null);
  $petunjukAlamat = 'alamat-petunjuk'.($errors->has('alamat') ? ' alamat-galat' : '');
?>

<?php $__env->startSection('content'); ?>
<main class="isi">

  <div class="order-box">
    <form id="form_order_laundry" action="<?php echo e(route('order.store')); ?>" method="post" data-kunci>
      <?php echo csrf_field(); ?>
      <section class="form-order">
        <?php if($errors->any()): ?>
          <div class="kabar kabar-galat" role="alert" tabindex="-1" data-fokus-galat>
            <p class="kabar-judul">Pesanan belum terkirim. Perbaiki yang berikut:</p>
            <ul>
              <?php $__currentLoopData = $errors->messages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kolom => $daftarPesan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><a href="#<?php echo e($kolom === 'layanan' ? $layanan[0]['id'] : $kolom); ?>"><?php echo e($daftarPesan[0]); ?></a></li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          </div>
        <?php endif; ?>

        <h1>Pilih layanan</h1>
        <p class="keterangan">Mau laundry kayak gimana bos.</p>
        <div class="bagian-layanan" role="radiogroup" aria-label="Pilih layanan"
             <?php if($errors->has('layanan')): ?> aria-describedby="layanan-galat" <?php endif; ?>>
          <?php $__currentLoopData = $layanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="jenis-layanan">
              <input
                type="radio"
                name="layanan"
                id="<?php echo e($item['id']); ?>"
                value="<?php echo e($item['nama']); ?>"
                data-harga="<?php echo e($item['harga']); ?>"
                required
                <?php if(old('layanan') === $item['nama']): echo 'checked'; endif; ?>
                <?php if($errors->has('layanan')): ?> aria-invalid="true" <?php endif; ?>
              />
              <label for="<?php echo e($item['id']); ?>">
                <img class="ikon-layanan" src="<?php echo e(asset('img/'.$item['gambar'])); ?>" alt="" width="80" height="80" />
                <span class="info-layanan">
                  <span class="nama"><?php echo e($item['nama']); ?></span>
                  <span class="deskripsi"><?php echo e($item['deskripsi']); ?></span>
                  <span class="harga">Rp<?php echo e(number_format($item['harga'], 0, ',', '.')); ?> / <?php echo e($item['satuan']); ?></span>
                </span>
              </label>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php $__errorArgs = ['layanan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <p class="galat" id="layanan-galat"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <div class="bagian-form">
          <h2>Data pelanggan</h2>
          <?php if(auth()->guard('pelanggan')->check()): ?>
            <p class="atas-nama">
              Atas nama <strong><?php echo e(auth('pelanggan')->user()->nama); ?></strong>,
              WhatsApp <?php echo e(auth('pelanggan')->user()->no_telepon); ?>.
            </p>
          <?php else: ?>
            <div class="grup-form">
              <label for="nama">Nama lengkap</label>
              <input type="text" id="nama" name="nama" value="<?php echo e(old('nama')); ?>" required autocomplete="name"
                     placeholder="Masukkan nama lengkap"
                     <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" aria-describedby="nama-galat" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> />
              <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="galat" id="nama-galat"><?php echo e($message); ?></p>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          <?php endif; ?>
          <div class="baris-form">
            <?php if(auth()->guard('pelanggan')->guest()): ?>
              <div class="grup-form">
                <label for="telepon">Nomor WhatsApp</label>
                <input type="tel" id="telepon" name="telepon" value="<?php echo e(old('telepon')); ?>" required
                       inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx"
                       <?php $__errorArgs = ['telepon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" aria-describedby="telepon-galat" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> />
                <?php $__errorArgs = ['telepon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                  <p class="galat" id="telepon-galat"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
            <?php endif; ?>
            <div class="grup-form">
              <label for="tanggal">Tanggal jemput</label>
              <input type="date" id="tanggal" name="tanggal" value="<?php echo e(old('tanggal')); ?>" min="<?php echo e(now()->toDateString()); ?>" required
                     <?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" aria-describedby="tanggal-galat" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> />
              <?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="galat" id="tanggal-galat"><?php echo e($message); ?></p>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          </div>
          <div class="grup-form">
            <label for="alamat">Alamat jemput</label>
            <textarea id="alamat" name="alamat" rows="4" required autocomplete="street-address"
                      placeholder="Masukkan alamat lengkap"
                      aria-describedby="<?php echo e($petunjukAlamat); ?>"
                      <?php $__errorArgs = ['alamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>><?php echo e($alamatIsi); ?></textarea>
            <?php $__errorArgs = ['alamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <p class="galat" id="alamat-galat"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <p class="petunjuk" id="alamat-petunjuk">
              <?php if($alamatDariTerakhir): ?>
                Diisi dari pesanan terakhir. Ubah kalau jemputnya di tempat lain.
              <?php endif; ?>
              Ongkir Rp<?php echo e(number_format($ongkir, 0, ',', '.')); ?>, gratis untuk alamat di <?php echo e($areaGratis); ?>.
            </p>
          </div>
          <div class="grup-form">
            <label for="catatan">Catatan <span class="opsional">(boleh dikosongkan)</span></label>
            <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: Jangan dijemur langsung matahari yah."><?php echo e(old('catatan')); ?></textarea>
          </div>
        </div>

        <button type="submit" class="tombol tombol-utama tombol-blok" data-teks-proses="Mengirim pesanan...">Kirim pesanan</button>
      </section>
    </form>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/order.blade.php ENDPATH**/ ?>