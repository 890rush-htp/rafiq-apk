<?php
// Halaman: Akun Pengguna (daftar)
// Hanya konten; layout (topbar & sidebar) disiapkan admin/index.php,
// yang juga sudah memuat inc/functions.php (tampil, e, isAdmin, dll).

$BOLEH_KELOLA = isAdmin();

tampil_pesan_session();
pastikan_tabel_profil(); // tabel profil untuk akun non-Admin (fungsi tambahan)

$ROWS = tampil(
    "SELECT u.id_user, u.email, u.role, u.created_at,
            t.tipe_user,
            a.id_admin, a.nama_admin, a.alamat_admin, a.telepon_admin, a.jenis_kelamin, a.path_photo_admin,
            p.nama AS nama_profil, p.telepon AS telepon_profil, p.alamat AS alamat_profil
     FROM tbl_users u
     LEFT JOIN tbl_tipe_user t ON t.id_tipe_user = u.role
     LEFT JOIN tbl_admin a ON a.id_user = u.id_user
     LEFT JOIN tbl_profil_user p ON p.id_user = u.id_user
     ORDER BY u.id_user ASC"
);
$ID_LOGIN = $_SESSION['Sess_id_user'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-0">Akun Pengguna</h3>
    <p class="text-muted mb-0">Manajemen data akun login (tbl_users).</p>
  </div>
  <?php if ($BOLEH_KELOLA): ?>
    <div>
      <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#modalTambahAkun">
        <i class="mdi mdi-plus mr-1"></i> Tambah
      </button>
      <a href="index.php?pages=akun_tambah" class="btn btn-primary btn-sm">
        <i class="mdi mdi-plus-circle mr-1"></i> Tambah Data
      </a>
    </div>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-hover" id="table1">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Telepon</th>
            <th>Alamat</th>
            <th>Tipe User</th>
            <th class="text-center" style="width: 340px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($ROWS)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">Belum ada data akun pengguna.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($ROWS as $ROW):
              $FOTO = foto_admin_url($ROW['path_photo_admin'] ?? '');
              $NAMA_TAMPIL = $ROW['nama_admin'] ?: ($ROW['nama_profil'] ?: '-');
              $TELEPON_TAMPIL = $ROW['telepon_admin'] ?: ($ROW['telepon_profil'] ?: '');
              $ALAMAT_TAMPIL = $ROW['alamat_admin'] ?: ($ROW['alamat_profil'] ?: '');
            ?>
              <tr>
                <td><?= e($ROW['id_user']) ?></td>
                <td>
                  <?php if ($FOTO !== ''): ?>
                    <img src="<?= e($FOTO) ?>" class="rounded-circle mr-2" style="width:32px;height:32px;object-fit:cover;" alt="">
                  <?php else: ?>
                    <i class="mdi mdi-account-circle mr-1" style="font-size:22px;vertical-align:middle;"></i>
                  <?php endif; ?>
                  <?= e($NAMA_TAMPIL) ?>
                </td>
                <td><?= e($ROW['email']) ?></td>
                <td><?= e($TELEPON_TAMPIL ?: '-') ?></td>
                <td><?= e($ALAMAT_TAMPIL ?: '-') ?></td>
                <td>
                  <span class="badge <?= $ROW['tipe_user'] === 'Admin' ? 'badge-success' : 'badge-secondary' ?>">
                    <?= e($ROW['tipe_user'] ?: '-') ?>
                  </span>
                </td>
                <td class="text-center text-nowrap">
                  <?php if ($BOLEH_KELOLA): ?>
                    <!-- 1. Ganti Password -->
                    <button type="button"
                            class="btn btn-sm btn-warning btn-ganti-password"
                            data-toggle="modal"
                            data-target="#ModalGantiPassword"
                            data-id="<?= e($ROW['id_user']) ?>"
                            data-nama="<?= e($NAMA_TAMPIL) ?>">
                      <i class="mdi mdi-key"></i> Ganti Password
                    </button>

                    <!-- 2. Edit (Halaman) -->
                    <?php if ($ROW['id_admin'] !== null): ?>
                      <a href="index.php?pages=akun_tambah&id=<?= urlencode($ROW['id_user']) ?>"
                         class="btn btn-sm btn-primary">Edit</a>
                    <?php else: ?>
                      <a href="index.php?pages=akun_edit&id=<?= urlencode($ROW['id_user']) ?>"
                         class="btn btn-sm btn-primary">Edit</a>
                    <?php endif; ?>

                    <!-- 3. Edit Modal -->
                    <button type="button"
                            class="btn btn-sm btn-primary btn-edit"
                            data-id="<?= htmlspecialchars($ROW['id_user'], ENT_QUOTES, 'UTF-8') ?>"
                            data-nama="<?= htmlspecialchars($ROW['nama_admin'] ?: ($ROW['nama_profil'] ?: ''), ENT_QUOTES, 'UTF-8') ?>"
                            data-alamat="<?= htmlspecialchars($ROW['alamat_admin'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-telepon="<?= htmlspecialchars($TELEPON_TAMPIL, ENT_QUOTES, 'UTF-8') ?>"
                            data-jenkel="<?= htmlspecialchars($ROW['jenis_kelamin'] ?? 'Laki-laki', ENT_QUOTES, 'UTF-8') ?>"
                            data-email="<?= htmlspecialchars($ROW['email'], ENT_QUOTES, 'UTF-8') ?>"
                            data-foto="<?= htmlspecialchars($ROW['path_photo_admin'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-toggle="modal"
                            data-target="#ModalEditAdmin">
                      <i class="mdi mdi-square-edit-outline"></i> Edit Modal
                    </button>

                    <!-- 4. Delete -->
                    <?php if ($ROW['id_user'] !== $ID_LOGIN): ?>
                      <form action="pages/user_admin/proses_hapus.php" method="POST" class="d-inline"
                            onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($ROW['id_user']) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    <?php else: ?>
                      <button type="button" class="btn btn-sm btn-secondary" disabled title="Tidak bisa menghapus akun sendiri">Delete</button>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($BOLEH_KELOLA): ?>
<!-- MODAL TAMBAH ADMIN -->
<div class="modal fade" id="modalTambahAkun" tabindex="-1" role="dialog" aria-labelledby="modalTambahAkunLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahAkunLabel">Tambah Akun Pengguna</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <form id="FormTambahAdmin" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-grup">
                <label>ID Admin</label>
                <input type="text" class="form-control" name="IdAdmin"
                       value="<?php echo autonumber('tbl_users','id_user', 7,'ADM'); ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-grup">
                <label>Nama Admin</label>
                <input type="text" class="form-control" name="NamaAdmin" required placeholder="Nama Admin">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Alamat Admin</label>
                <textarea class="form-control" name="AlamatAdmin" rows="2" placeholder="Alamat Admin" required></textarea>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" class="form-control" name="TeleponAdmin" placeholder="Nomor Telepon" required>
              </div>
            </div>
            <div class="col-md-6">
              <label>Jenis Kelamin</label>
              <div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="Jengkel" value="Laki-laki" checked>
                  <label class="form-check-label">Laki-laki</label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="Jengkel" value="perempuan">
                  <label class="form-check-label">Perempuan</label>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Email (Username) <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="Email" required placeholder="Email">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="Password" required placeholder="Password">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="Password2" required placeholder="Confirm Password">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Photo Admin</label>
                <input type="file" class="form-control" name="Photo" accept="image/*">
                <small class="text-muted">Format: JPG, JPEG, PNG, BMP. Maksimal 2MB</small>
              </div>
            </div>
          </div>

          <input type="hidden" name="Role" value="Admin">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" name="TambahModal" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL GANTI PASSWORD -->
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
          <input type="hidden" name="IdUserPassword" id="GantiPasswordId">

          <div class="alert alert-info">
            <strong>Admin:</strong> <span id="GantiPasswordNama"></span>
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

<!-- MODAL EDIT ADMIN -->
<div class="modal fade" id="ModalEditAdmin" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title">
          <i class="fas fa-user-edit"></i> Edit Data Pengguna Admin
        </h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <form id="FormEditAdmin" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="IdUser" id="EditIdUser">
          <input type="hidden" name="FotoLama" id="EditFotoLama">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>ID Admin</label>
                <input type="text" class="form-control" name="IdAdmin" id="EditIdAdmin" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama Admin <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="NamaAdmin" id="EditNamaAdmin" required placeholder="Nama Admin">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Alamat Admin</label>
                <textarea class="form-control" name="AlamatAdmin" id="EditAlamatAdmin" rows="2" placeholder="Alamat Admin" required></textarea>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" class="form-control" name="TeleponAdmin" id="EditTeleponAdmin" required>
              </div>
            </div>
            <div class="col-md-6">
              <label>Jenis Kelamin</label>
              <div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="Jengkel" id="EditJenkelL" value="Laki-laki" required>
                  <label class="form-check-label">Laki-laki</label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="Jengkel" id="EditJenkelP" value="perempuan">
                  <label class="form-check-label">Perempuan</label>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Email (Username)</label>
                <input type="email" class="form-control" name="Email" id="EditEmail" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Photo Saat Ini</label>
                <div>
                  <img id="EditCurrentPhoto" src="" alt="Current Photo"
                       style="max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 50%;">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Ganti Photo</label>
                <input type="file" class="form-control" name="Photo" accept="image/*">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti photo (max 2MB)</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Untuk mengganti password, silakan klik tombol
                <button type="button" class="btn btn-sm btn-warning" onclick="$('#ModalGantiPassword').modal('show')">
                  Ganti Password
                </button>
              </div>
            </div>
          </div>

          <input type="hidden" name="Role" value="Admin">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" name="EditModal" class="btn btn-primary">
            <i class="fas fa-save"></i> Update
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
$(document).ready(function() {
  // ALUR GANTI PASSWORD
  $(document).on('click', '.btn-ganti-password', function() {
    $('#GantiPasswordId').val($(this).data('id'));
    $('#GantiPasswordNama').text($(this).data('nama'));
  });

  // ALUR TAMBAH ADMIN
  $('#FormTambahAdmin').on('submit', function(e) {
    e.preventDefault();

    var password = $('#FormTambahAdmin input[name="Password"]').val();
    var password2 = $('#FormTambahAdmin input[name="Password2"]').val();

    if (password !== password2) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Password dan Confirm Password tidak sama!'
      });
      return false;
    }

    if (password.length < 6) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Password minimal 6 karakter!'
      });
      return false;
    }

    Swal.fire({
      title: 'Menyimpan data...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    var formData = new FormData(this);
    formData.append('tambah_modal', '1');

    $.ajax({
      url: 'pages/user_admin/proses_modal_admin.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
        Swal.close();
        console.log('Response:', response);
        if (response.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: response.message,
            showConfirmButton: false,
            timer: 1500
          }).then(function() {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: response.message
          });
        }
      },
      error: function(xhr, status, error) {
        Swal.close();
        console.log('AJAX Error:', xhr.responseText);
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Terjadi kesalahan: ' + error
        });
      }
    });
  });

  // ALUR GANTI PASSWORD DENGAN AJAX
  $('#FormGantiPassword').on('submit', function(e) {
    e.preventDefault();

    var pass_baru = $('input[name="PassBaru"]').val();
    var pass_baru_confirm = $('input[name="PassBaruConfirm"]').val();

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
      success: function(response) {
        Swal.close();
        if (response.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: response.message,
            showConfirmButton: false,
            timer: 1500
          }).then(function() {
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
      error: function(xhr, status, error) {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Terjadi kesalahan: ' + error
        });
      }
    });
  });

  // MODAL EDIT: ketika tombol Edit Modal diklik
  $(document).on('click', '.btn-edit', function() {
    var id = $(this).data('id');
    var nama = $(this).data('nama');
    var alamat = $(this).data('alamat');
    var telepon = $(this).data('telepon');
    var jenkel = $(this).data('jenkel');
    var email = $(this).data('email');
    var foto = $(this).data('foto');

    $('#EditIdUser').val(id);
    $('#EditIdAdmin').val(id);
    $('#EditNamaAdmin').val(nama);
    $('#EditAlamatAdmin').val(alamat);
    $('#EditTeleponAdmin').val(telepon);
    $('#EditEmail').val(email);
    $('#EditFotoLama').val(foto);

    // Set jenis kelamin
    $('#EditJenkelL').prop('checked', false);
    $('#EditJenkelP').prop('checked', false);
    if (jenkel === 'L' || jenkel === 'Laki-laki') {
      $('#EditJenkelL').prop('checked', true);
    } else if (jenkel === 'P' || jenkel === 'perempuan') {
      $('#EditJenkelP').prop('checked', true);
    }

    // Set photo
    if (foto && foto !== '' && foto !== 'null') {
      var fotoPath = '../images/user_admin/' + foto;
      var img = new Image();
      img.onload = function() {
        $('#EditCurrentPhoto').attr('src', fotoPath);
      };
      img.onerror = function() {
        $('#EditCurrentPhoto').attr('src', '../images/default-avatar.png');
      };
      img.src = fotoPath;
    } else {
      $('#EditCurrentPhoto').attr('src', '../images/default-avatar.png');
    }

    // Simpan data untuk ganti password
    $('#GantiPasswordId').val(id);
    $('#GantiPasswordNama').text(nama);
  });

  // EDIT ADMIN dengan AJAX
  $('#FormEditAdmin').on('submit', function(e) {
    e.preventDefault();

    Swal.fire({
      title: 'Mengupdate data...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    var formData = new FormData(this);
    formData.append('edit_modal', '1');

    $.ajax({
      url: 'pages/user_admin/proses_modal_admin.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
        Swal.close();
        console.log('Response:', response);
        if (response.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: response.message,
            showConfirmButton: false,
            timer: 1500
          }).then(function() {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: response.message
          });
        }
      },
      error: function(xhr, status, error) {
        Swal.close();
        console.log('AJAX Error:', xhr.responseText);
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Terjadi kesalahan: ' + error
        });
      }
    });
  });
});
</script>
