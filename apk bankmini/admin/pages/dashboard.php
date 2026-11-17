  <?php
  // Dashboard: ringkasan jumlah akun per tipe user.
  $ringkasan = tampil(
      "SELECT t.tipe_user, COUNT(u.id_user) AS jumlah
      FROM tbl_tipe_user t
      LEFT JOIN tbl_users u ON u.role = t.id_tipe_user
      GROUP BY t.id_tipe_user, t.tipe_user
      ORDER BY t.id_tipe_user"
  );
  $total = array_sum(array_column($ringkasan, 'jumlah'));
  ?>
  <div class="mb-4">
    <h3 class="mb-0">Dashboard</h3>
    <p class="text-muted mb-0">Selamat datang di Bank Mini, <?= e($nama_user ?? 'Admin') ?>.</p>
  </div>

  <div class="row">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm"><div class="card-body">
        <p class="text-muted mb-1">Total Akun</p>
        <h2 class="mb-0"><?= (int) $total ?></h2>
      </div></div>
    </div>
    <?php foreach ($ringkasan as $r): ?>
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm"><div class="card-body">
          <p class="text-muted mb-1"><?= e($r['tipe_user']) ?></p>
          <h2 class="mb-0"><?= (int) $r['jumlah'] ?></h2>
        </div></div>
      </div>
    <?php endforeach; ?>
  </div>
