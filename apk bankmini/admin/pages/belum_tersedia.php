<?php
// Dipakai admin/index.php untuk modul yang file halamannya belum dibuat.
?>
<div class="mb-4">
  <h3 class="mb-0"><?= e($page_title ?? 'Halaman') ?></h3>
</div>
<div class="alert alert-info">
  <i class="mdi mdi-information-outline mr-1"></i>
  Modul <strong><?= e($page_title ?? '') ?></strong> belum dibuat.
  Buat file <code>admin/pages/<?= e($allowed_pages[$page_key] ?? '') ?></code> untuk mengisinya.
</div>
