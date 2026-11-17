<?php

session_start();
require_once 'functions.php';

//Cek session
//untuk mengecek session
//Jika session email sudah ada,kalau sudah ada maka user akan diarahkan ke halaman sesuai dengan role user
if (@$_SESSION['Sess_Email']) {
	if (@$_SESSION['Sess_Level'] == "Admin") {
	header("location:../admin/index.php");
} elseif (@$_SESSION['Sess_Level'] == "Petugas") {
	header("location:../petugas/index.php");
} elseif (@$_SESSION['Sess_Level'] == "Pengawas") {
	header("location:../pengawas/index.php");
}
}

//Cek login
//Jika tombol sign in (name=login) ditekan, maka akan mengirim variabel yang ada form login yaitu username (Email) dan Password dengan form name PascalCase
if (isset($_POST['Login'])) { 
//mengambil email dan password yang diinput user
	$EMAIL = strtolower(stripslashes($_POST['Email']));//name=Email di input oleh user
	$USERPASS = $_POST['Password']; //name=Password yang di input oleh user

	//Lalu kita cek apakah email dan password ada di database, kita lakukan query ke database (mencari email di database)
	$SQL = mysqli_query($KONEKSI, "SELECT  password, role FROM tbl_users WHERE email = '$EMAIL' "); // email=field,field dengan nama email yang ada di database, dimana email disamakan dengan email yang di form login 
	list($PASWD, $ROLE) = mysqli_fetch_array($SQL);//php gabisa baca sql jadi diubah menjadi array agar bisa diambil datanya, lalu kita ambil password dan role dari database dan disimpan ke variabel $PASWD dan $ROLE

	//ambil level user dari role yang ada
	//mengambil data dari tabel tipe user berdasarkan role yang ada di tabel users
	$TYPE_USER = "SELECT * FROM tbl_tipe_user WHERE id_tipe_user='$ROLE' "; //disini variabel $TYPE USER mengambil role dari tabel tbl_tipe_users berdasarkan role yang di ambil dari tbl_tipe_users dan disimpan kedalam variabel
	$HASIL = mysqli_query($KONEKSI, $TYPE_USER); //menjalankan query sql untuk mengambil data dari tbl_tipe_users berdasarkan role yang di ambil dari tbl_tipe_users
	$ROW = mysqli_fetch_assoc($HASIL);//mengambil hasil query dan menyimpannya ke dalam array asosiatif $ROW
	$LEVEL = $ROW['tipe_user'];//mengambil nilai tipe_user dari array $ROW dan menyimpannya kedalam variable $LEVEL

	// Jika data ditemukan dalam database, maka akan melakukan proses validasi dengan menggunakan password_verify
	// Cek apakah email ditemukan 

	if ($SQL && mysqli_num_rows($SQL) > 0) { //jika ada role maka masuk sesuai role, jika tidak ada role maka hasilnya 0
		//Jika data (>0) maka kita akan melakukan validasi
		//$userpass ==. diambil dari form input yang dilakukan oleh user
		//password ==> password yang ada di database dalam bentuk HASH
		//Jika password yang diinputkan user sama dengan password yang ada di database, maka login berhasil
		
		//cek password
		if (password_verify($USERPASS, $PASWD)) { //kita cocokan password yang di encripsi= menutup kode password hash, decribe=(membandingkan ) membuka kode password hash

			//Akan kita buat session agar mempunyai hak akses sesuai dengan role user yang login
			$_SESSION['Sess_Email'] = $EMAIL; //agar bisa mengakses dasboard
			$_SESSION['Sess_Level'] = $LEVEL; //agar bisa mengakses dasboard

			//Jika berhasil login, maka user akan diarahkan ke admin sesuai dengan role user
			//Jika dia admin --> index.php
			//Jika dia petugas --> petugas/index.php
			//Jika dia pengawas --> pengawas.php

			//untuk menjalankan
			if ($_SESSION['Sess_Level'] == "Admin") {
				header("location:../admin/index.php");
			} elseif ($_SESSION['Sess_Level'] == "Petugas") {
				header("location:../petugas/index.php");
			} elseif ($_SESSION['Sess_Level'] == "Pengawas") {
				header("location:../pengawas/index.php");
			}
			die();
		} else {
			echo '<script language="JavaScript">
            window.alert("LOGIN GAGAL...! Email/Password Salah");
            window.document.location.href = "login.php";
            </script>';
		}
	} else {
		echo '<script language="JavaScript">
        window.alert("LOGIN GAGAL...! Email tidak ditemukan");
        window.document.location.href = "login.php";
        </script>';
	}
}
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Bank Mini</title>
    <link rel="stylesheet" href="../assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/iconfonts/ionicons/dist/css/ionicons.css">
    <link rel="stylesheet" href="../assets/vendors/iconfonts/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.addons.css">
    <link rel="stylesheet" href="../assets/css/shared/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" />
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
          <div class="row w-100">
            <div class="col-lg-4 mx-auto">
              <h2 class="text-center mb-4">Login Bank Mini</h2>
              <div class="auto-form-wrapper">

                <form action="" method="POST">

                  <div class="form-group">
                    <label class="label">Email</label>
                    <div class="input-group">
                      <input type="email" name="Email" class="form-control" placeholder="Email" required autocomplete="off"
                             value="<?= htmlspecialchars($_POST['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <div class="input-group-append">
                        <span class="input-group-text"><i class="mdi mdi-check-circle-outline"></i></span>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="label">Password</label>
                    <div class="input-group">
                      <input type="password" name="Password" class="form-control" placeholder="*********" required>
                      <div class="input-group-append">
                        <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <button type="submit" name="Login" class="btn btn-primary submit-btn btn-block">Login</button>
                  </div>

                  <div class="form-group d-flex justify-content-between">
                    <div class="form-check form-check-flat mt-0">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" checked> Keep me signed in </label>
                    </div>
                    <a href="#" class="text-small forgot-password text-black">Forgot Password</a>
                  </div>

                  <div class="text-block text-center my-3">
                    <span class="text-small font-weight-semibold">Not a member ?</span>
                    <a href="register.php" class="text-black text-small">Create new account</a>
                  </div>
                </form>

              </div>
              <ul class="auth-footer">
                <li><a href="#">Conditions</a></li>
                <li><a href="#">Help</a></li>
                <li><a href="#">Terms</a></li>
              </ul>
              <p class="footer-text text-center">copyright © 2026 SMK MVP ARS. All rights reserved.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/js/vendor.bundle.addons.js"></script>
  </body>
</html>