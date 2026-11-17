<?php
session_start();
require_once __DIR__ . '/functions.php';

// Sudah login? Kembali ke login.php yang akan mengarahkan sesuai level
if (isLogin()) {
    header('Location: login.php');
    exit;
}

$ERROR = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Registrasi'])) {
    if (!csrf_valid()) {
        $ERROR = 'Sesi tidak valid. Silakan muat ulang halaman.';
    } else {
        // registrasi() menampilkan alert + redirect sendiri kalau berhasil/gagal validasi
        registrasi($_POST);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register - Bank Mini</title>
    <link rel="stylesheet" href="../assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/iconfonts/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.addons.css">
    <link rel="stylesheet" href="../assets/css/shared/style.css">
    <link rel="stylesheet" href="../assets/css/demo_1/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth register-bg-1 theme-one">
                <div class="row w-100">
                    <div class="col-lg-4 mx-auto">
                        <h2 class="text-center mb-4">Register</h2>
                        <div class="auto-form-wrapper">

                            <?php if ($ERROR !== ''): ?>
                                <div class="alert alert-danger" role="alert"><?= e($ERROR) ?></div>
                            <?php endif; ?>

                            <form action="" method="POST">
                                <?= csrf_field() ?>

                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="email" name="Email" class="form-control" placeholder="Email" required autocomplete="off"
                                               value="<?= e($_POST['Email'] ?? '') ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="password" name="Password" class="form-control" placeholder="Password (min. 6 karakter)" required minlength="6">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="password" name="ConfirmPassword" class="form-control" placeholder="Confirm Password" required minlength="6">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group d-flex justify-content-center">
                                    <div class="form-check form-check-flat mt-0">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" checked required> I agree to the terms
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="Registrasi" class="btn btn-primary submit-btn btn-block">Register</button>
                                </div>
                                <div class="text-block text-center my-3">
                                    <span class="text-small font-weight-semibold">Already have an account ?</span>
                                    <a href="login.php" class="text-black text-small">Login</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/js/vendor.bundle.addons.js"></script>
</body>
</html>
