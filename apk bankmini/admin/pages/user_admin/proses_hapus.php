<?php
// Proses hapus akun. Diakses langsung (bukan lewat include admin/index.php),
// jadi start session & require fungsi sendiri.
session_start();
require_once __DIR__ . '/../../../inc/functions.php';

$KEMBALI = '../../index.php?pages=akun_pengguna';

if (!isAdmin()) {
    header('Location: ../../../inc/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    $_SESSION['Sess_FormErrors'] = ['Permintaan tidak valid. Silakan coba lagi.'];
    header('Location: ' . $KEMBALI);
    exit;
}

if (hapus_user($_POST['id'] ?? '')) {
    $_SESSION['Sess_SuccessMessage'] = 'Akun berhasil dihapus.';
} elseif (empty($_SESSION['Sess_FormErrors'])) {
    $_SESSION['Sess_FormErrors'] = ['Akun tidak ditemukan atau gagal dihapus.'];
}

header('Location: ' . $KEMBALI);
exit;
