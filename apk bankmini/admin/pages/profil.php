<?php
// Profil akun yang sedang login (hanya baca).

// 1. Amankan email dari session menggunakan escape string
// (Gunakan $GLOBALS['KONEKSI'] agar variabel koneksi terbaca di file ini)
$email_esc = mysqli_real_escape_string($GLOBALS['KONEKSI'], $_SESSION['Sess_Email'] ?? '');

// 2. PERBAIKAN: 
// - Tanda '?' dihapus, diganti langsung dengan variabel $email_esc
// - Typo 'path_photo_admin' diubah jadi 'path_photo_admin' (sesuaikan dengan database Anda)
$rows = tampil(
    "SELECT u.id_user, u.email, u.created_at, t.tipe_user,
            a.nama_admin, a.telepon_admin, a.alamat_admin, a.jenis_kelamin, a.path_photo_admin
     FROM tbl_users u
     LEFT JOIN tbl_tipe_user t ON t.id_tipe_user = u.role
     LEFT JOIN tbl_admin a ON a.id_user = u.id_user
     WHERE u.email = '$email_esc' 
     LIMIT 1"
);

$p = $rows[0] ?? null;
?>
<div class="mb-4"><h3 class="mb-0">Profil</h3></div>

<?php if (!$p): ?>
  <div class="alert alert-warning">Data profil tidak ditemukan.</div>
<?php else: $foto = foto_admin_url($p['path_photo_admin'] ?? ''); ?>
  <div class="card shadow-sm"><div class="card-body">
    <div class="d-flex align-items-center mb-4">
      <?php if ($foto !== ''): ?>
        <img src="<?= e($foto) ?>" class="rounded-circle mr-3" style="width:80px;height:80px;object-fit:cover;" alt="Foto">
      <?php else: ?>
        <i class="mdi mdi-account-circle mr-3" style="font-size:80px;line-height:1;"></i>
      <?php endif; ?>
      <div>
        <h4 class="mb-0"><?= e($p['nama_admin'] ?: '-') ?></h4>
        <span class="badge badge-primary"><?= e($p['tipe_user'] ?: '-') ?></span>
      </div>
    </div>
    <table class="table table-sm mb-0">
      <tr><th style="width:200px">ID User</th><td><?= e($p['id_user']) ?></td></tr>
      <tr><th>Email</th><td><?= e($p['email']) ?></td></tr>
      <tr><th>Telepon</th><td><?= e($p['telepon_admin'] ?: '-') ?></td></tr>
      <tr><th>Alamat</th><td><?= e($p['alamat_admin'] ?: '-') ?></td></tr>
      <tr><th>Jenis Kelamin</th><td><?= (['L' => 'Laki-laki', 'P' => 'Perempuan'][kode_jk($p['jenis_kelamin'] ?? '')] ?? '-') ?></td></tr>
      <tr><th>Terdaftar</th><td><?= e($p['created_at']) ?></td></tr>
    </table>
  </div></div>
<?php endif; ?>