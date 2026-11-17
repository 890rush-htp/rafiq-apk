<?php
session_start();
require_once __DIR__ . '/../../../inc/functions.php'; // sesuaikan dengan path yang benar

// set header untuk JSON response
header('Content-Type: application/json');

// untuk debugging - lihat error PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// debug: log yang diterima
error_log('=== PROSES MODAL ADMIN ===');
error_log('POST data: ' . print_r($_POST, true));
error_log('FILES data: ' . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // PROSES TAMBAH ADMIN
    if (isset($_POST['tambah_modal'])) {
        error_log('Proses TAMBAH admin');

        if (!function_exists('tambah_admin')) {
            error_log('ERROR: Fungsi tambah_admin tidak ditemukan!');
            echo json_encode([
                'status' => 'error',
                'message' => 'Fungsi tambah_admin tidak ditemukan di functions.php'
            ]);
            exit;
        }

        // Naik 3 tingkat ke folder root: images/user_admin
        $RESULT = tambah_admin($_POST, $_FILES, __DIR__ . '/../../../images/user_admin');
        error_log('Hasil tambah_admin: ' . ($RESULT === true ? 'TRUE' : 'FALSE'));

        if ($RESULT === true) {
            unset($_SESSION['Sess_SuccessMessage']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Data berhasil ditambahkan!'
            ]);
        } else {
            $ERRORMESSAGE = isset($_SESSION['Sess_FormErrors']) ? implode(', ', $_SESSION['Sess_FormErrors']) : 'Gagal menambahkan data.';
            unset($_SESSION['Sess_FormErrors']);
            echo json_encode([
                'status' => 'error',
                'message' => $ERRORMESSAGE
            ]);
        }
        exit;
    }

    // PROSES EDIT ADMIN
    if (isset($_POST['edit_modal'])) {
        error_log('Proses EDIT admin');

        if (!function_exists('edit_admin')) {
            error_log('ERROR: Fungsi edit_admin tidak ditemukan!');
            echo json_encode([
                'status' => 'error',
                'message' => 'Fungsi edit_admin tidak ditemukan di functions.php'
            ]);
            exit;
        }

        $RESULT = edit_admin($_POST, $_FILES, __DIR__ . '/../../../images/user_admin');
        error_log('Hasil edit_admin: ' . ($RESULT === true ? 'TRUE' : 'FALSE'));

        if ($RESULT === true) {
            unset($_SESSION['Sess_SuccessMessage']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui!'
            ]);
        } else {
            $ERRORMESSAGE = isset($_SESSION['Sess_FormErrors']) ? implode(', ', $_SESSION['Sess_FormErrors']) : 'Gagal memperbarui data.';
            unset($_SESSION['Sess_FormErrors']);
            echo json_encode([
                'status' => 'error',
                'message' => $ERRORMESSAGE
            ]);
        }
        exit;
    }

    // PROSES GANTI PASSWORD
    if (isset($_POST['ganti_password'])) {
        error_log('Proses GANTI PASSWORD');

        if (!function_exists('ganti_password')) {
            error_log('ERROR: Fungsi ganti_password tidak ditemukan!');
            echo json_encode([
                'status' => 'error',
                'message' => 'Fungsi ganti_password tidak ditemukan di functions.php'
            ]);
            exit;
        }

        $result = ganti_password($_POST);

        if ($result === true) {
            unset($_SESSION['Sess_SuccessMessage']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Password berhasil diubah!'
            ]);
        } else {
            $errorMessage = isset($_SESSION['Sess_FormErrors']) ? implode(', ', $_SESSION['Sess_FormErrors']) : 'Gagal mengubah password.';
            unset($_SESSION['Sess_FormErrors']);
            echo json_encode([
                'status' => 'error',
                'message' => $errorMessage
            ]);
        }
        exit;
    }
}
?>