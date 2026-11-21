<?php $__env->startSection('title', 'Komplain pelanggan'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php
  $aksi = session('aksi');
  $statusKomplain = \App\Models\Komplain::STATUS;
  $pilihan = [null => 'Semua'] + $statusKomplain;
?>

<?php $__env->startSection('content'); ?>
<?php if($aksi && $aksi['jenis'] !== 'galat'): ?>
  <?php echo $__env->make('admin.notifikasi', ['aksi' => $aksi], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<main class="admin-page">
  <header class="admin-heading">
    <h1>Komplain pelanggan</h1>
    <div class="admin-count" aria-label="Komplain yang belum ditanggapi">
      <span>Belum ditanggapi</span>
      <strong><?php echo e($hitung['baru'] ?? 0); ?></strong>
    </div>
  </header>

  <section class="admin-panel" aria-labelledby="judul-komplain">
    <div class="panel-heading">
      <h2 id="judul-komplain">Daftar komplain</h2>
    </div>

    
    <nav class="alat-admin" aria-label="Saring komplain">
      <div class="saring">
        <?php $__currentLoopData = $pilihan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kunci => $nama): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $aktif = ($saring ?? '') === (string) $kunci; ?>
          <a class="saring-tombol" href="<?php echo e($kunci ? route('admin.komplain.index', ['status' => $kunci]) : route('admin.komplain.index')); ?>"
             <?php if($aktif): ?> aria-current="true" <?php endif; ?>>
            <?php echo e($nama); ?><span><?php echo e($kunci ? ($hitung[$kunci] ?? 0) : $semua); ?></span>
          </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </nav>

    <?php if($daftar->isEmpty()): ?>
      <p class="komplain-kosong">
        <?php echo e($saring ? 'Tidak ada komplain dengan status ini.' : 'Belum ada komplain. Kalau pelanggan melapor, tampil di sini.'); ?>

      </p>
    <?php else: ?>
      <ul class="daftar-komplain">
        <?php $__currentLoopData = $daftar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $id = $k->id_komplain;
            $statusField = 'status_'.$id;
            $tanggapanField = 'tanggapan_'.$id;
          ?>
          <li class="komplain-admin" id="komplain-<?php echo e($id); ?>">
            <div class="komplain-admin-kepala">
              <div>
                <p class="komplain-admin-nama"><?php echo e($k->pelanggan->nama ?? 'Pelanggan'); ?></p>
                <p class="komplain-admin-meta">
                  <?php echo e($k->pesanan->no_telepon ?? ''); ?> &middot; Pesanan #<?php echo e($k->id_pesanan); ?>, <?php echo e($k->pesanan->layanan ?? ''); ?>

                </p>
              </div>
              <span class="label-komplain" data-komplain="<?php echo e($k->status); ?>"><?php echo e($k->label_status); ?></span>
            </div>

            <p class="komplain-admin-jenis">
              <?php echo e($k->label_jenis); ?>

              <span>Dikirim <?php echo e($k->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i')); ?></span>
            </p>
            <p class="komplain-admin-teks"><?php echo e($k->deskripsi); ?></p>

            <?php if($k->pesanan): ?>
              <p class="komplain-admin-tautan">
                <a class="tautan-struk" href="<?php echo e(route('admin.order.struk', $k->pesanan)); ?>" target="_blank" rel="noopener">Lihat struk<span class="sr-only"> pesanan #<?php echo e($k->id_pesanan); ?> (buka di tab baru)</span></a>
              </p>
            <?php endif; ?>

            <form class="komplain-admin-form" method="post" action="<?php echo e(route('admin.komplain.update', $k)); ?>" data-kunci>
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="grup-form">
                <label for="tanggapan-<?php echo e($id); ?>">Tanggapan untuk pelanggan</label>
                <textarea id="tanggapan-<?php echo e($id); ?>" name="<?php echo e($tanggapanField); ?>" rows="3" maxlength="1000"
                          placeholder="Contoh: Maaf ya, cucian dicuci ulang dan diantar lagi besok."
                          <?php if($errors->has($tanggapanField)): ?> aria-invalid="true" aria-describedby="tanggapan-galat-<?php echo e($id); ?>" <?php endif; ?>><?php echo e(old($tanggapanField, $k->tanggapan)); ?></textarea>
                <?php $__errorArgs = [$tanggapanField];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                  <p class="galat" id="tanggapan-galat-<?php echo e($id); ?>"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>

              <div class="komplain-admin-aksi">
                <div class="komplain-admin-status">
                  <label for="status-komplain-<?php echo e($id); ?>">Status</label>
                  <select id="status-komplain-<?php echo e($id); ?>" name="<?php echo e($statusField); ?>"
                          <?php if($errors->has($statusField)): ?> aria-invalid="true" <?php endif; ?>>
                    <?php $__currentLoopData = $statusKomplain; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kunci => $nama): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($kunci); ?>" <?php if(old($statusField, $k->status) === $kunci): echo 'selected'; endif; ?>><?php echo e($nama); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
                <button type="submit" class="tombol-simpan" data-teks-proses="Menyimpan...">Simpan</button>
              </div>
              <?php $__errorArgs = [$statusField];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="galat"><?php echo e($message); ?></p>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

              <?php if($k->penanggap): ?>
                <p class="komplain-admin-jejak">
                  Terakhir diubah oleh <?php echo e($k->penanggap->name); ?>, <?php echo e($k->updated_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H.i')); ?>.
                </p>
              <?php endif; ?>
            </form>
          </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    <?php endif; ?>
  </section>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  
  <script src="<?php echo e(asset('js/admin.js')); ?>?v=<?php echo e(filemtime(public_path('js/admin.js'))); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mldham/Documents/TUGASBESAR_M3_ILHAM/resources/views/admin/komplain.blade.php ENDPATH**/ ?>