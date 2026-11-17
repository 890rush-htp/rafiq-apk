<?php
// Halaman: Akun Pengguna (Tambah / Edit)
// Hanya konten; layout & fungsi (tambah_admin, edit_admin, tampil, e, dll)
// disiapkan oleh admin/index.php lewat inc/functions.php.

if (!isAdmin()) {
    echo '<div class="alert alert-danger">
            <i class="mdi mdi-lock-outline mr-1"></i>
            Akses ditolak. Hanya <strong>Admin</strong> yang boleh menambah atau mengedit akun pengguna.
          </div>
          <a href="index.php?pages=akun_pengguna" class="btn btn-light btn-sm">
            <i class="mdi mdi-arrow-left mr-1"></i> Kembali
          </a>';
    return;
}

$KEMBALI   = 'index.php?pages=akun_pengguna';
$id_edit   = trim((string) ($_GET['id'] ?? ''));
$edit_mode = $id_edit !== '';

// Data awal form
$data = [
    'id_user' => '', 'email' => '', 'nama_admin' => '', 'telepon_admin' => '',
    'alamat_admin' => '', 'jenis_kelamin' => 'L', 'path_photo_admin' => '',
];

if ($edit_mode) {
    // Escape ID untuk mencegah SQL Injection (karena tampil() tidak mendukung prepared statement)
    $id_edit_esc = mysqli_real_escape_string($GLOBALS['KONEKSI'], $id_edit);

    $rows = tampil(
        "SELECT u.id_user, u.email, a.nama_admin, a.telepon_admin,
                a.alamat_admin, a.jenis_kelamin, a.path_photo_admin
         FROM tbl_users u
         JOIN tbl_admin a ON a.id_user = u.id_user
         WHERE u.id_user = '$id_edit_esc'
         LIMIT 1"
    );

    if (empty($rows)) {
        $_SESSION['Sess_FormErrors'] = ['Data admin dengan ID tersebut tidak ditemukan.'];
        header('Location: ' . $KEMBALI);
        exit;
    }

    $data = $rows[0];
    $data['jenis_kelamin'] = kode_jk($data['jenis_kelamin']); // '' kalau data lama belum diisi
} else {
    $data['id_user'] = autonumber('tbl_users', 'id_user', 4, 'ADM'); // otomatis: ADM0001, ADM0002, ...
}

// Simpan saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_akun'])) {
    if (!csrf_valid()) {
        $_SESSION['Sess_FormErrors'] = ['Sesi tidak valid. Silakan muat ulang halaman.'];
    } else {
        $ok = $edit_mode ? edit_admin($_POST, $_FILES) : tambah_admin($_POST, $_FILES);
        if ($ok) {
            header('Location: ' . $KEMBALI);
            exit;
        }
        // Gagal: isi ulang form dengan input terakhir (kecuali password & foto)
        $data['id_user']       = $edit_mode ? $id_edit : trim($_POST['IdAdmin'] ?? '');
        $data['email']         = trim($_POST['Email'] ?? '');
        $data['nama_admin']    = trim($_POST['NamaAdmin'] ?? '');
        $data['telepon_admin'] = trim($_POST['TeleponAdmin'] ?? '');
        $data['alamat_admin']  = trim($_POST['AlamatAdmin'] ?? '');
        $data['jenis_kelamin'] = in_array(strtolower($_POST['Jengkel'] ?? ''), ['laki-laki', 'l'], true) ? 'L' : 'P';
    }
}

$foto = foto_admin_url($data['path_photo_admin'] ?? '');

// Cek apakah kolom id_user masih bertipe integer
$id_user_masih_int = false;
if (!$edit_mode) {
    $cek_kolom = tampil("SHOW COLUMNS FROM tbl_users LIKE 'id_user'");
    $id_user_masih_int = !empty($cek_kolom) && preg_match('/int/i', $cek_kolom[0]['Type'] ?? '');
}
?>
<div class="mb-4">
  <h3 class="mb-0"><?= $edit_mode ? 'Edit Akun Admin' : 'Tambah Akun Admin' ?></h3>
  <p class="text-muted mb-0">Form kelola akun login admin (tbl_users & tbl_admin).</p>
</div>

<?php tampil_pesan_session(); ?>

<?php if (!$edit_mode && $id_user_masih_int): ?>
  <div class="alert alert-warning">
    Kolom <code>id_user</code> di database masih berupa angka, jadi ID baru akan berupa angka (bukan ADM0001).
    Jalankan <code>migrasi_id_user.php</code> sekali untuk mengubahnya.
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form action="index.php?pages=akun_tambah<?= $edit_mode ? '&id=' . urlencode($id_edit) : '' ?>" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div class="form-group">
        <label>ID User</label>
        <?php if ($edit_mode): ?>
          <input type="text" class="form-control" value="<?= e($data['id_user']) ?>" disabled>
          <input type="hidden" name="IdUser" value="<?= e($data['id_user']) ?>">
        <?php else: ?>
          <input type="text" name="IdAdmin" class="form-control" value="<?= e($data['id_user']) ?>" readonly>
          <small class="text-muted">Dibuat otomatis (ADM0001, ADM0002, ...).</small>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label>Foto Admin</label>
        <input type="file" name="Photo" id="foto_admin" class="form-control"
               accept="image/jpeg,image/png,image/bmp" onchange="cekFoto(this)">
        <small class="text-muted d-block mt-1">
          Format JPG/PNG/BMP. <?= $edit_mode ? 'Kosongkan jika tidak ingin mengganti foto.' : 'Opsional.' ?>
        </small>
        <div class="mt-2">
          <img id="preview_foto" src="<?= e($foto) ?>" class="img-thumbnail" alt="Preview foto"
               style="width:120px;height:120px;object-fit:cover;<?= $foto === '' ? 'display:none;' : '' ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="Email" class="form-control" value="<?= e($data['email']) ?>" placeholder="Email" required>
      </div>

      <?php if (!$edit_mode): ?>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="Password" class="form-control" placeholder="Password (min. 6 karakter)" required>
      </div>

      <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="Password2" class="form-control" placeholder="Ulangi Password" required>
      </div>
      <?php endif; ?>

      <div class="form-group">
        <label>Nama</label>
        <input type="text" name="NamaAdmin" class="form-control" value="<?= e($data['nama_admin']) ?>" placeholder="Nama lengkap" required>
      </div>

      <div class="form-group">
        <label>Nomor Telepon</label>
        <input type="text" name="TeleponAdmin" class="form-control" value="<?= e($data['telepon_admin']) ?>" placeholder="Nomor telepon" required>
      </div>

      <div class="form-group">
        <label>Alamat</label>
        <textarea name="AlamatAdmin" class="form-control" rows="2" placeholder="Alamat" required><?= e($data['alamat_admin']) ?></textarea>
      </div>

      <div class="form-group">
        <label>Jenis Kelamin</label><br>
        <div class="form-check form-check-inline">
          <label class="form-check-label">
            <input class="form-check-input" type="radio" name="Jengkel" value="Laki-laki" required <?= $data['jenis_kelamin'] === 'L' ? 'checked' : '' ?>><i class="input-helper"></i> Laki-laki
          </label>
        </div>
        <div class="form-check form-check-inline">
          <label class="form-check-label">
            <input class="form-check-input" type="radio" name="Jengkel" value="Perempuan" <?= $data['jenis_kelamin'] === 'P' ? 'checked' : '' ?>><i class="input-helper"></i> Perempuan
          </label>
        </div>
      </div>

      <?php if ($edit_mode): ?>
        <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#ModalGantiPassword">
          <i class="mdi mdi-key-variant mr-1"></i> Ubah Password
        </button>
      <?php endif; ?>
      <button type="submit" name="simpan_akun" class="btn btn-primary"><?= $edit_mode ? 'Update' : 'Simpan' ?></button>
      <a href="<?= e($KEMBALI) ?>" class="btn btn-light">Batal</a>
    </form>
  </div>
</div>

<?php if ($edit_mode): ?>
<!-- MODAL GANTI PASSWORD (diletakkan di luar <form> utama) -->
<div class="modal fade" id="ModalGantiPassword" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h4 class="modal-title">
          <i class="fas fa-key"></i> Ganti Password Admin
        </h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <form id="FormGantiPassword" method="post">
        <div class="modal-body">
          <input type="hidden" name="IdUserPassword" id="GantiPasswordId" value="<?= e($data['id_user']) ?>">

          <div class="alert alert-info">
            <strong>Admin:</strong> <span id="GantiPasswordNama"><?= e($data['nama_admin']) ?></span>
          </div>

          <div class="form-group">
            <label>Password Lama <span class="text-danger">*</span></label>
            <input type="password" class="form-control" name="PassLama" required>
          </div>

          <div class="form-group">
            <label>Password Baru <span class="text-danger">*</span></label>
            <input type="password" class="form-control" name="PassBaru" required>
            <small>Minimal 6 karakter</small>
          </div>

          <div class="form-group">
            <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
            <input type="password" class="form-control" name="PassBaruConfirm" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" name="GantiPassword" class="btn btn-warning">
            <i class="fas fa-save"></i> Ganti Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function cekFoto(input) {
    var maxSize = 2 * 1024 * 1024;
    var preview = document.getElementById('preview_foto');
    if (!input.files || !input.files[0]) return;
    if (input.files[0].size > maxSize) {
      alert('Ukuran foto melebihi 2MB. Silakan pilih file yang lebih kecil.');
      input.value = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) { preview.src = e.target.result; preview.style.display = 'inline-block'; };
    reader.readAsDataURL(input.files[0]);
  }

<?php if ($edit_mode): ?>
  $(document).ready(function () {
    $('#FormGantiPassword').on('submit', function (e) {
      e.preventDefault();

      var pass_baru = $('#FormGantiPassword input[name="PassBaru"]').val();
      var pass_baru_confirm = $('#FormGantiPassword input[name="PassBaruConfirm"]').val();

      if (pass_baru !== pass_baru_confirm) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Password Baru dan Konfirmasi tidak sama!'
        });
        return false;
      }

      if (pass_baru.length < 6) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Password minimal 6 karakter!'
        });
        return false;
      }

      Swal.fire({
        title: 'Mengganti password...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      var formData = new FormData(this);
      formData.append('ganti_password', '1');

      $.ajax({
        url: 'pages/user_admin/proses_modal_admin.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          Swal.close();
          if (response.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: response.message,
              showConfirmButton: false,
              timer: 1500
            }).then(function () {
              $('#ModalGantiPassword').modal('hide');
              $('#FormGantiPassword')[0].reset();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: response.message
            });
          }
        },
        error: function (xhr, status, error) {
          Swal.close();
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan: ' + error
          });
        }
      });
    });
  });
<?php endif; ?>
</script>