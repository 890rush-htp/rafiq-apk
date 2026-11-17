<?php
// =====================================================================
// MIGRASI SEKALI JALAN: ubah id_user dari ANGKA (15, 16, ...) menjadi
// KODE (ADM0001, USR0001, ...). Buka lewat browser setelah login Admin:
//     http://localhost/migrasi_id_user.php
// !! BACKUP database dulu (phpMyAdmin -> Export). HAPUS file ini setelah selesai.
// =====================================================================
session_start();
require_once __DIR__ . '/inc/functions.php';

$CLI = (PHP_SAPI === 'cli');
if (!$CLI && !isAdmin()) {
    header('Location: inc/login.php');
    exit;
}

$AWALAN = ['Admin' => 'ADM', 'Petugas' => 'PTG', 'Pengawas' => 'PGW', 'Nasabah' => 'USR'];

function sudah_teks()
{
    $K = kolom_tabel('tbl_users');
    return !preg_match('/int/i', $K['id_user']['Type']);
}

// Daftar foreign key yang menunjuk ke tbl_users.id_user
function daftar_fk()
{
    return tampil(
        "SELECT k.TABLE_NAME AS tabel, k.COLUMN_NAME AS kolom, k.CONSTRAINT_NAME AS nama,
                r.UPDATE_RULE AS upd, r.DELETE_RULE AS del
         FROM information_schema.KEY_COLUMN_USAGE k
         JOIN information_schema.REFERENTIAL_CONSTRAINTS r
              ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
             AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
             AND r.TABLE_NAME = k.TABLE_NAME
         WHERE k.TABLE_SCHEMA = DATABASE()
           AND k.REFERENCED_TABLE_NAME = 'tbl_users'
           AND k.REFERENCED_COLUMN_NAME = 'id_user'"
    );
}

// Rencana ID baru: urut berdasarkan id lama, nomor per tipe user
function rencana_id($AWALAN)
{
    $ROWS = tampil(
        "SELECT u.id_user, t.tipe_user
         FROM tbl_users u LEFT JOIN tbl_tipe_user t ON t.id_tipe_user = u.role
         ORDER BY u.id_user ASC"
    );
    $URUT = [];
    $HASIL = [];
    foreach ($ROWS as $R) {
        $A = $AWALAN[$R['tipe_user'] ?? ''] ?? 'USR';
        $URUT[$A] = ($URUT[$A] ?? 0) + 1;
        $HASIL[(string)$R['id_user']] = $A . str_pad($URUT[$A], 4, '0', STR_PAD_LEFT);
    }
    return $HASIL;
}

function migrasi($AWALAN)
{
    global $KONEKSI;
    $RENCANA = rencana_id($AWALAN);
    $FKS = daftar_fk();

    mysqli_query($KONEKSI, 'SET FOREIGN_KEY_CHECKS = 0');
    try {
        // 1. lepas semua foreign key yang menunjuk ke tbl_users.id_user
        foreach ($FKS as $F) {
            mysqli_query($KONEKSI, "ALTER TABLE `{$F['tabel']}` DROP FOREIGN KEY `{$F['nama']}`");
        }
        // 2. ubah tipe kolom jadi teks (tbl_users dan semua tabel yang menunjuknya)
        mysqli_query($KONEKSI, "ALTER TABLE tbl_users MODIFY id_user VARCHAR(20) NOT NULL");
        foreach ($FKS as $F) {
            $NULL = kolom_tabel($F['tabel'])[$F['kolom']]['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            mysqli_query($KONEKSI, "ALTER TABLE `{$F['tabel']}` MODIFY `{$F['kolom']}` VARCHAR(20) $NULL");
        }
        // 3. ganti isi: ID baru berbentuk huruf+angka, jadi tidak bisa bentrok dengan ID lama
        foreach ($RENCANA as $LAMA => $BARU) {
            jalankan("UPDATE tbl_users SET id_user = ? WHERE id_user = ?", 'ss', [$BARU, (string)$LAMA]);
            foreach ($FKS as $F) {
                jalankan("UPDATE `{$F['tabel']}` SET `{$F['kolom']}` = ? WHERE `{$F['kolom']}` = ?", 'ss', [$BARU, (string)$LAMA]);
            }
        }
        // 4. pasang kembali foreign key dengan aturan yang sama
        foreach ($FKS as $F) {
            mysqli_query($KONEKSI, "ALTER TABLE `{$F['tabel']}` ADD CONSTRAINT `{$F['nama']}`
                FOREIGN KEY (`{$F['kolom']}`) REFERENCES tbl_users (id_user)
                ON DELETE {$F['del']} ON UPDATE {$F['upd']}");
        }
    } finally {
        mysqli_query($KONEKSI, 'SET FOREIGN_KEY_CHECKS = 1');
    }
    return $RENCANA;
}

$PESAN = '';
$ERROR = '';
if ($CLI || ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid())) {
    if (!$CLI && !isset($_POST['jalankan'])) {
        $ERROR = 'Permintaan tidak valid.';
    } elseif (sudah_teks()) {
        $PESAN = 'id_user sudah bertipe teks. Tidak ada yang perlu dimigrasi.';
    } else {
        try {
            $HASIL = migrasi($AWALAN);
            $PESAN = 'Selesai! ' . count($HASIL) . ' akun diubah. HAPUS file migrasi_id_user.php sekarang.';
        } catch (Throwable $E) {
            $ERROR = 'Migrasi gagal: ' . $E->getMessage();
        }
    }
}
if ($CLI) {
    echo ($ERROR ?: $PESAN), "\n";
    exit;
}

$TEKS = sudah_teks();
$RENCANA = $TEKS ? [] : rencana_id($AWALAN);
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Migrasi ID User</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
</head><body class="p-4" style="max-width:720px;margin:auto">
<h3>Migrasi ID User</h3>
<?php if ($PESAN): ?><div class="alert alert-success"><?= e($PESAN) ?></div><?php endif; ?>
<?php if ($ERROR): ?><div class="alert alert-danger"><?= e($ERROR) ?></div><?php endif; ?>

<?php if ($TEKS): ?>
  <p>id_user sudah bertipe teks. <a href="admin/index.php?pages=akun_pengguna">Kembali ke Akun Pengguna</a></p>
<?php else: ?>
  <div class="alert alert-warning">
    <strong>Backup database dulu</strong> (phpMyAdmin &rarr; Export). Proses ini mengubah tipe kolom dan semua ID.
  </div>
  <table class="table table-sm">
    <thead><tr><th>ID lama</th><th>ID baru</th></tr></thead>
    <tbody>
    <?php foreach ($RENCANA as $L => $B): ?><tr><td><?= e($L) ?></td><td><?= e($B) ?></td></tr><?php endforeach; ?>
    </tbody>
  </table>
  <p class="text-muted small">Tabel lain yang memakai id_user sebagai foreign key ikut diubah otomatis:
    <?= e(implode(', ', array_unique(array_column(daftar_fk(), 'tabel'))) ?: '-') ?>.</p>
  <form method="POST" onsubmit="return confirm('Sudah backup database? Lanjutkan migrasi?');">
    <?= csrf_field() ?>
    <button type="submit" name="jalankan" value="1" class="btn btn-danger">Jalankan Migrasi</button>
    <a href="admin/index.php?pages=akun_pengguna" class="btn btn-light">Batal</a>
  </form>
<?php endif; ?>
</body></html>
