<?php
session_start();
ob_start(); // supaya halaman di admin/pages/ masih bisa memakai header('Location: ...')

// Koneksi database ($KONEKSI) + kumpulan fungsi (isAdmin, tampil, e, dll)
// tersedia di sini DAN di semua file yang di-include dari admin/pages/.
require_once __DIR__ . '/../inc/functions.php';

// Panel ini khusus Admin. Belum login / bukan admin -> ke halaman login.
if (!isAdmin()) {
    header('Location: ../inc/login.php');
    exit;
}

// 1. WHITELIST HALAMAN
// Kunci kiri = nilai ?pages= di URL & di href sidebar
// Nilai kanan = nama file fisik di dalam folder admin/pages/
$allowed_pages = [
    'dashboard'          => 'dashboard.php',
    'tabungan'           => 'tabungan.php',
    'transaksi'          => 'transaksi.php',
    'data_nasabah'       => 'data_nasabah.php',
    'tambah_nasabah'     => 'tambah_nasabah.php',
    'edit_nasabah'       => 'edit_nasabah.php',
    'tidak_menabung'     => 'tidak_menabung.php',
    'saldo_nasabah'      => 'saldo_nasabah.php',
    'rekap_tabungan'     => 'rekap_tabungan.php',
    'rekap_pembayaran'   => 'rekap_pembayaran.php',
    'laporan_peringkat'  => 'laporan_peringkat.php',
    'pengaturan_sistem'  => 'pengaturan_sistem.php',
    'pengaturan_nasabah' => 'pengaturan_nasabah.php',
    'akun_pengguna'      => 'user_admin/tampil.php',
    'akun_tambah'        => 'user_admin/tambah.php',
    'akun_edit'          => 'user_admin/edit_user.php',
    'profil'             => 'profil.php',
];

// 2. AMBIL PARAMETER ?pages= DARI URL
$page_key = isset($_GET['pages']) ? $_GET['pages'] : 'dashboard';

// 3. VALIDASI: kalau page_key tidak ada di whitelist -> fallback ke dashboard
//    (WAJIB seperti ini supaya index.php tidak bisa dipakai untuk
//     include sembarang file / Local File Inclusion)
if (!array_key_exists($page_key, $allowed_pages)) {
    $page_key = 'dashboard';
}

$page_title = ucwords(str_replace('_', ' ', $page_key));
$page_file  = __DIR__ . '/pages/' . $allowed_pages[$page_key];

$nama_user = $_SESSION['Sess_Email'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>BANK MINI - <?= htmlspecialchars($page_title) ?></title>

    <link rel="stylesheet" href="../assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/css/shared/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" />

    <style>
      body { background-color: #f8f9fc; }

      .topbar {
        height: 4.375rem;
        background-color: #fff;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        z-index: 1030;
        overflow: visible !important;
      }
      .topbar .nav-item.dropdown { position: relative !important; }
      .topbar .dropdown-menu {
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        left: auto !important;
        z-index: 9999 !important;
        display: none;
        min-width: 12rem;
      }
      .topbar .dropdown-menu.show { display: block !important; }

      .navbar-search { width: 25rem; }
      .navbar-search .form-control {
        background-color: #f8f9fc;
        border: 1px solid #d1d3e2;
        font-size: 0.85rem;
      }
      .navbar-search .btn {
        background-color: #4e73df;
        border-color: #4e73df;
        color: white;
      }
      .topbar-divider {
        width: 0;
        border-right: 1px solid #e3e6f0;
        height: 2rem;
        margin: 0 1rem;
      }
      .sidebar-brand-top {
        width: 224px;
        height: 4.375rem;
        background-color: #4e73df;
        font-weight: 800;
        font-size: 1.1rem;
        color: #fff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
      }
      .sidebar-sb {
        width: 224px;
        top: 4.375rem;
        left: 0;
        height: calc(100vh - 4.375rem);
        position: fixed;
        background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        overflow-y: auto;
        z-index: 1020;
        padding-bottom: 2rem;
      }
      .sidebar-sb::-webkit-scrollbar { width: 5px; }
      .sidebar-sb::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
      }
      .sidebar-sb .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        transition: all 0.2s ease;
      }
      .sidebar-sb .nav-link:hover {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.15);
      }
      .sidebar-sb .nav-link.active {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.2);
        font-weight: 700;
      }
      .sidebar-sb .nav-category {
        color: rgba(255, 255, 255, 0.5) !important;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 1rem 1.25rem 0.3rem;
        letter-spacing: 0.05rem;
      }
      .main-content {
        margin-left: 224px;
        padding-top: 5.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        padding-bottom: 2rem;
      }
      @media (max-width: 768px) {
        .sidebar-sb { display: none; }
        .main-content { margin-left: 0; }
        .navbar-search { width: 100%; }
        .sidebar-brand-top { width: auto; background-color: transparent; }
      }
    </style>
  </head>
  <body>

    <!-- 1. NAVBAR TOPBAR -->
    <nav class="navbar topbar fixed-top navbar-expand navbar-light p-0 px-3 d-flex align-items-center justify-content-between">
      <a class="sidebar-brand-top d-none d-md-flex" href="index.php">
        <i class="mdi mdi-bank mr-2"></i> BANK MINI
      </a>
      <a class="navbar-brand d-md-none font-weight-bold text-primary mr-2" href="index.php">BANK MINI</a>

      <form class="form-inline navbar-search d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0">
        <div class="input-group">
          <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-primary" type="button"><i class="mdi mdi-magnify"></i></button>
          </div>
        </div>
      </form>

      <ul class="navbar-nav ml-auto flex-row align-items-center" style="overflow: visible;">
        <li class="nav-item mx-2 position-relative">
          <a class="nav-link p-0" href="#"><i class="mdi mdi-bell mdi-20px text-gray-600"></i></a>
        </li>
        <li class="nav-item mx-2 position-relative">
          <a class="nav-link p-0" href="#"><i class="mdi mdi-email mdi-20px text-gray-600"></i></a>
        </li>
        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="javascript:void(0)" id="userDropdown" role="button">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold"><?= htmlspecialchars($nama_user) ?></span>
            <img class="rounded-circle mr-1" src="../assets/images/favicon.png" width="32" height="32" alt="Profile">
            <i class="mdi mdi-chevron-down text-gray-600"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-right shadow mt-2" id="userDropdownMenu" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="index.php?pages=profil"><i class="mdi mdi-account mr-2 text-gray-400"></i> Profile</a>
            <a class="dropdown-item" href="index.php?pages=pengaturan_sistem"><i class="mdi mdi-settings mr-2 text-gray-400"></i> Settings</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="../inc/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');"><i class="mdi mdi-logout mr-2"></i> Logout</a>
          </div>
        </li>
      </ul>
    </nav>

    <!-- 2. SIDEBAR BIRU -->
    <nav class="sidebar-sb d-none d-md-block">
      <ul class="nav flex-column mt-2">
        <li class="nav-category">TABUNGAN</li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'tabungan' ? 'active' : '' ?>" href="index.php?pages=tabungan"><i class="mdi mdi-wallet mr-2"></i> TABUNGAN NASABAH</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'transaksi' ? 'active' : '' ?>" href="index.php?pages=transaksi"><i class="mdi mdi-swap-horizontal mr-2"></i> TRANSAKSI BANK MINI</a></li>

        <li class="nav-category">NASABAH</li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'data_nasabah' ? 'active' : '' ?>" href="index.php?pages=data_nasabah"><i class="mdi mdi-account-card-details mr-2"></i> DATA NASABAH</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'tidak_menabung' ? 'active' : '' ?>" href="index.php?pages=tidak_menabung"><i class="mdi mdi-account-off mr-2"></i> TIDAK MENABUNG</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'saldo_nasabah' ? 'active' : '' ?>" href="index.php?pages=saldo_nasabah"><i class="mdi mdi-cash-multiple mr-2"></i> LIHAT SALDO NASABAH</a></li>

        <li class="nav-category">REKAP TRANSAKSI</li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'rekap_tabungan' ? 'active' : '' ?>" href="index.php?pages=rekap_tabungan"><i class="mdi mdi-file-document-box mr-2"></i> REKAP TABUNGAN</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'rekap_pembayaran' ? 'active' : '' ?>" href="index.php?pages=rekap_pembayaran"><i class="mdi mdi-receipt mr-2"></i> REKAP PEMBAYARAN</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'laporan_peringkat' ? 'active' : '' ?>" href="index.php?pages=laporan_peringkat"><i class="mdi mdi-chart-bar mr-2"></i> LAPORAN PERINGKAT</a></li>

        <li class="nav-category">PENGATURAN</li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'pengaturan_sistem' ? 'active' : '' ?>" href="index.php?pages=pengaturan_sistem"><i class="mdi mdi-settings mr-2"></i> pengaturan sistem</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'pengaturan_nasabah' ? 'active' : '' ?>" href="index.php?pages=pengaturan_nasabah"><i class="mdi mdi-account-settings mr-2"></i> pengaturan nasabah</a></li>

        <li class="nav-category">AKUN</li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'akun_pengguna' ? 'active' : '' ?>" href="index.php?pages=akun_pengguna"><i class="mdi mdi-account-group mr-2"></i> akun pengguna</a></li>
        <li class="nav-item"><a class="nav-link <?= $page_key === 'profil' ? 'active' : '' ?>" href="index.php?pages=profil"><i class="mdi mdi-account mr-2"></i> profil</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="../inc/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');"><i class="mdi mdi-logout mr-2"></i> keluar</a></li>
      </ul>
    </nav>

    <!-- 3. KONTEN UTAMA: include file dari admin/pages/ sesuai ?pages= -->
    <div class="main-content">
      <?php
        if (is_file($page_file)) {
            include $page_file;
        } else {
            // Modul yang belum dibuat: tampilkan info ramah, bukan error
            include __DIR__ . '/pages/belum_tersedia.php';
        }
      ?>
    </div>

    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var dropdownBtn = document.getElementById('userDropdown');
        var dropdownMenu = document.getElementById('userDropdownMenu');
        if (dropdownBtn && dropdownMenu) {
          dropdownBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
          });
          document.addEventListener('click', function (e) {
            if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
              dropdownMenu.classList.remove('show');
            }
          });
        }
      });
    </script>
  </body>
</html>
