<?php
//+=+=========================================+=+
//Aplikasi Bank_Mini untuk siswa SMK MVP
//File ini berguna untuk menampung semua fungsi. Dan dapat digunakan untuk berbagai keperluan
//+=+=========================================+=+

//+=+=========================================+=+
//1.Fungsi Pengaturan Waktu
//+=+=========================================+=+

// Mengatur zona waktu standar aplikasi ke WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');
// Menyimpan format tanggal dan waktu saat ini (YYYY-MM-DD HH:MM:SS) ke variabel global
$WAKTU_SEKARANG = date('Y-m-d H:i:s');

//+=+=========================================+=+
//2.Fungsi mengambil data dari database
//+=+=========================================+=+

$SERVERNAME = "localhost";
$USERNAME = "root";
$PASSWORD = "";
$DBNAME = "bankmini_rafiq2526";
// Membuka koneksi baru ke server database MySQL menggunakan driver mysqli
$KONEKSI = mysqli_connect($SERVERNAME, $USERNAME, $PASSWORD, $DBNAME);

// Memeriksa apakah koneksi database gagal terhubung
if (!$KONEKSI) {
    // Menghentikan eksekusi script dan menampilkan pesan kesalahan koneksi
    die("koneksi gagal: " . mysqli_connect_error());
}

//+=+=========================================+=+
//3. fungsi autonumber untuk id user
//+=+=========================================+=+
function autonumber($TABEL, $KOLOM, $LEBAR=0, $AWALAN='') {
    // Mengakses variabel koneksi global di luar scope fungsi
    global $KONEKSI; 

    // Mengeksekusi query untuk mengambil ID terakhir berdasarkan awalan (misal USR atau ADM) secara urutan menurun
    $QUERY = mysqli_query($KONEKSI, "SELECT $KOLOM FROM $TABEL WHERE $KOLOM LIKE '$AWALAN%' ORDER BY $KOLOM DESC LIMIT 1") or die ('error:  ' . mysqli_error($KONEKSI));

    // Menghitung jumlah baris hasil query pencarian
    $JUMLAH_RECORD = mysqli_num_rows($QUERY);
    // Jika tidak ada record awal, nomor baru dimulai dari angka 1
    if ($JUMLAH_RECORD == 0) {
        $NOMOR_BARU = 1;
    } else {
        // Mengambil baris data hasil query dalam bentuk array terurut
        $ROW = mysqli_fetch_array($QUERY);
        // Memotong string awalan dan merubah sisa karakter angka menjadi integer lalu ditambah 1
        $NOMOR_BARU = intval(substr($ROW[0], strlen($AWALAN))) + 1;
    }

    // Jika parameter lebar diisi, format nomor baru dengan penambahan angka nol di sebelah kiri
    if ($LEBAR > 0) {
        $ID_BARU = $AWALAN . str_pad($NOMOR_BARU, $LEBAR, "0", STR_PAD_LEFT);
    } else {
        // Jika tidak ada lebar khusus, langsung gabungkan awalan dan angka
        $ID_BARU = $AWALAN . $NOMOR_BARU;
    }
    // Mengembalikan string ID otomatis yang telah dibentuk
    return $ID_BARU;
}

//+=+=========================================+=+
// 4. fungsi registrasi
//+=+=========================================+=+

function registrasi($DATA) {
    // Mengambil variabel global koneksi dan timestamp
    global $KONEKSI, $WAKTU_SEKARANG;

    // 1. Mengambil dan membersihkan karakter backslash dari input ID User (Form Name PascalCase)
    $ID_USER = stripslashes($DATA["IdUser"]);
    // Mengambil email, mengubah huruf menjadi kecil, dan membersihkan backslash
    $EMAIL = strtolower(stripslashes($DATA["Email"]));

    // Mengamankan input password dari karakter berbahaya SQL Injection
    $PASSWORD = mysqli_real_escape_string($KONEKSI, $DATA["Password"]);
    // Mengamankan input konfirmasi password dari karakter berbahaya SQL Injection
    $CONFIRM_PASSWORD = mysqli_real_escape_string($KONEKSI, $DATA["ConfirmPassword"]);

    // 2. Memeriksa keberadaan email pada database untuk menghindari duplikasi
    $RESULT = mysqli_query($KONEKSI, "SELECT email FROM tbl_users WHERE email = '$EMAIL'") or die('Error: ' . mysqli_error($KONEKSI));

    // Jika email sudah ada di database, tampilkan peringatan dan batalkan registrasi
    if (mysqli_fetch_assoc($RESULT)) {
        echo "<script>
            alert('Email sudah terdaftar!');
            </script>";
        return false;
    }

    // 3. Memvalidasi kecocokan antara password dan konfirmasinya
    if ($PASSWORD !== $CONFIRM_PASSWORD) {
        echo "<script>
            alert('Password dan Confirm Password tidak sama! cek lagi gih');
            </script>";
        return false;
    }

    // 4. Mengenkripsi kata sandi pengguna dengan algoritma hashing default PHP
    $PASSWORD_HASH = password_hash($PASSWORD, PASSWORD_DEFAULT);

    // 5. Menetapkan level pengguna secara langsung (Default Level 4 = Nasabah)
    $LEVEL = isset($DATA['Role']) ? (int)$DATA['Role'] : 4; 

    // 6. Menyusun dan mengeksekusi query insert ke tabel pengguna utama (tbl_users)
    $QUERY = "INSERT INTO tbl_users (id_user, email, password, role, created_at, updated_at) 
              VALUES ('$ID_USER', '$EMAIL', '$PASSWORD_HASH', '$LEVEL', '$WAKTU_SEKARANG', '$WAKTU_SEKARANG')";
    mysqli_query($KONEKSI, $QUERY) or die ('Gagal simpan data di tbl_users : ' . mysqli_error($KONEKSI));

    // Memeriksa apakah pengguna mendaftar sebagai Admin (Level 1)
    if ($LEVEL === 1) {
        // Menyimpan data awal ke tabel admin jika pengguna adalah Admin
        $QUERY2 = "INSERT INTO tbl_admin (id_user, created_at, updated_at) 
                   VALUES ('$ID_USER', '$WAKTU_SEKARANG', '$WAKTU_SEKARANG')";
        mysqli_query($KONEKSI, $QUERY2) or die ('Gagal simpan data di tbl_admin : ' . mysqli_error($KONEKSI));
    } 

    // 7. Memberikan notifikasi sukses dan mengarahkan pengguna ke halaman login
    echo "<script>
        alert('Selamat bung anda berhasil regis,Silahkan login untuk lanjut!!');
        document.location.href = 'login.php';
        </script>";

    // Mengembalikan jumlah baris data yang terpengaruh pada database
    return mysqli_affected_rows($KONEKSI);
}

//+=+=========================================+=+
//5. Fungsi Tampil User
//+=+=========================================+=+

function tampil($QUERY_STRING)
{
    // Memanggil variabel koneksi global
    global $KONEKSI; 

    // Mengeksekusi perintah SQL query pencarian data
    $QUERY = mysqli_query($KONEKSI, $QUERY_STRING) or die('Error: ' . mysqli_error($KONEKSI));

    // Inisialisasi variabel array kosong untuk menampung kumpulan data
    $HASIL_DATA = []; 
    // Melakukan perulangan untuk mengambil setiap baris hasil query sebagai array asosiatif
    while ($ROW = mysqli_fetch_assoc($QUERY)) {
        // Menambahkan baris data ke dalam array penampung
        $HASIL_DATA[] = $ROW; 
    }

    // Mengembalikan seluruh sekumpulan data berbentuk array asosiatif
    return $HASIL_DATA; 
}

//+=+=========================================+=+
//6. Fungsi Upload File
//+=+=========================================+=+ 

function upload_files($FILE_INPUT, $IDENTIFIER, $TARGET_FOLDER = '', $OPTION = [])
{

    // a. Konfigurasi bawaan default untuk validasi upload berkas
    $DEFAULT = [
        'max_size' => 2 * 1024 * 1024, // Menetapkan batas ukuran maksimal 50 MB
        'allowed_types' => ['jpg', 'png', 'gif', 'bmp'], // Ekstensi berkas yang diizinkan
        'create_folder' => true, // Pengaturan pembuat folder otomatis jika belum ada
        'prefix' => ''           // Awalan string nama file
    ];

    // Menggabungkan konfigurasi kustom pengirim dengan opsi default
    $OPTIONS = array_merge($DEFAULT, $OPTION);

    // b. Validasi struktur array input file dari $_FILES
    if (!isset($FILE_INPUT['error']) || !isset($FILE_INPUT['tmp_name'])) {
        return [
            'success' => false,
            'message' => 'File yang di upload tidak valid. Pastikan form sudah diisi dengan benar (menggunakan enctype="multipart/form-data").'
        ];
    }

    // Menguraikan komponen metadata dari file yang dikirimkan
    $ERRORS = $FILE_INPUT['error']; // Kode status error bawaan PHP
    $TMP_NAME = $FILE_INPUT['tmp_name']; // Path file sementara di server
    $NAMA_ASLI = $FILE_INPUT['name']; // Nama asli file di komputer client
    $UKURAN_FILE = $FILE_INPUT['size']; // Ukuran file dalam satuan byte

    // c. Mengecek apakah pengguna tidak memilih berkas apapun untuk diunggah
    if ($ERRORS === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => false,
            'message' => 'Tidak ada file yang diupload. Silahkan pilih file terlebih dahulu.'
        ];
    }

    // d. Mengecek apakah terjadi kesalahan saat proses pengunggahan file oleh PHP
    if ($ERRORS !== UPLOAD_ERR_OK) {
        // Pemetaan pesan kesalahan berdasarkan kode error PHP
        $ERROR_MESSAGES = [
            UPLOAD_ERR_INI_SIZE => 'File yang diupload melebihi batas ukuran yang diizinkan oleh server (Upload_max_filesize). Silahkan hubungi administrator.',
            UPLOAD_ERR_FORM_SIZE => 'File yang diupload melebihi batas ukuran yang diizinkan oleh Form (MAX_FILE_SIZE). Silahkan hubungi administrator.',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian. Silahkan coba lagi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara untuk upload tidak ditemukan. Silahkan hubungi administrator.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk. Silahkan hubungi administrator.',
            UPLOAD_ERR_EXTENSION => 'Upload file dihentikan oleh ekstensi PHP. Silahkan hubungi administrator.'
        ];

        // Memilih pesan kesalahan yang sesuai dari array
        $MESSAGE = isset($ERROR_MESSAGES[$ERRORS]) ? $ERROR_MESSAGES[$ERRORS] : 'Terjadi kesalahan saat mengupload file. Silahkan coba lagi.';
        return ['success' => false, 'message' => $MESSAGE];

    }

    // e. Mengambil ekstensi file asli dan mengonversinya ke huruf kecil
    $EKSTENSI_FILE = strtolower(pathinfo($NAMA_ASLI, PATHINFO_EXTENSION)); 

    // f. Memvalidasi apakah ekstensi file sesuai dengan daftar yang diizinkan
    if (!in_array($EKSTENSI_FILE, $OPTIONS['allowed_types'])) {
        // Menggabungkan daftar tipe ekstensi yang diperbolehkan menjadi string
        $EXT_STR = implode(', ', $OPTIONS['allowed_types']);
        return [
            'success' => false,
            'message' => "Ekstensi file tidak diizinkan. Hanya file dengan ekstensi berikut yang diperbolehkan: $EXT_STR."
        ];
    }
    // g. Memvalidasi ukuran berkas agar tidak melebihi batas maksimal yang dikonfigurasi
    if ($UKURAN_FILE > $OPTIONS['max_size']) {
        // Menghitung konversi byte ke Megabyte untuk tampilan pesan
        $MAX_SIZE_MB = number_format($OPTIONS['max_size'] / 1048576, 2); 
        return [
            'success' => false,
            'message' => "Ukuran file melebihi batas maksimal yang diizinkan. Maksimal ukuran file adalah $MAX_SIZE_MB MB."
        ];
    }

    // h. Membentuk awalan string nama file jika diset
    $PREFIX = $OPTIONS['prefix'] ? $OPTIONS['prefix'] . '_' : ''; 

    // Membuat identitas string acak berbasis mikrodetik
    $UNIQUE_ID = uniqid(); 

    // Mengambil timestamp tanggal dan waktu saat ini
    $TIMESTAMP = date('Ymd_His'); 

    // Menggabungkan elemen penyusun menjadi nama file yang baru dan unik
    $NAMA_FILE_BARU = $PREFIX . $IDENTIFIER . '' . $TIMESTAMP . '' . $UNIQUE_ID . '.' . $EKSTENSI_FILE; 

    // Buat folder otomatis jika opsi create_folder = true dan folder belum ada
    if ($OPTIONS['create_folder'] && !empty($TARGET_FOLDER) && !is_dir($TARGET_FOLDER)) {
        if (!mkdir($TARGET_FOLDER, 0777, true)) {
            return [
                'success' => false,
                'message' => 'Gagal membuat folder tujuan upload.'
            ];
        }
    }

    // i. Menentukan letak path tujuan penyimpanan file secara lengkap
    $FILE_PATH = rtrim($TARGET_FOLDER, '/') . '/' . $NAMA_FILE_BARU;

    // j. Memindahkan berkas dari direktori sementara ke folder target di server
    if (move_uploaded_file($TMP_NAME, $FILE_PATH)) {
        // Mengembalikan statistik informasi upload yang sukses dalam bentuk array
        return [
            'success' => true,                    
            'message' => 'File berhasil upload.', 
            'Filename_asli' => $NAMA_ASLI,         
            'Filename_tersimpan' => $NAMA_FILE_BARU, 
            'path' => $FILE_PATH,                   
            'size' => $UKURAN_FILE,                  
            'size_mb' => round($UKURAN_FILE / 1048576, 2), 
            'extension' => $EKSTENSI_FILE,                
            'identifier' => $IDENTIFIER,                 
            'prefix' => $OPTIONS['prefix']               
        ];
    } else {
        // Mengembalikan tanggapan gagal jika file gagal dipindahkan
        return [
            'success' => false,
            'message' => 'Gagal memindahkan file ke folder tujuan. silahkan coba lagi. pastikan folder tujuan memiliki izin tulis (write permission) yang cukup.',
        ];
    }
}

//+=+=========================================+=+
//7. Fungsi Tambah User Admin
//+=+=========================================+=+

function tambah_admin($DATA, $FILES, $TARGET_FOLDER = "../images/user_admin")
{
    // Memanggil variabel global koneksi dan waktu
    global $KONEKSI, $WAKTU_SEKARANG; 

    // a. Sanitasi dan konversi karakter entitas HTML pada seluruh masukan form
    $ID_ADMIN      = htmlspecialchars(trim($DATA["IdAdmin"] ?? ''));
    $NAMA_ADMIN    = htmlspecialchars(trim($DATA["NamaAdmin"] ?? ''));
    $ALAMAT_ADMIN  = htmlspecialchars(trim($DATA["AlamatAdmin"] ?? ''));
    $TELEPON_ADMIN = htmlspecialchars(trim($DATA["TeleponAdmin"] ?? ''));
    $JENGKEL_RAW   = htmlspecialchars(trim($DATA["Jengkel"] ?? ''));
    $EMAIL         = htmlspecialchars(trim($DATA["Email"] ?? ''));
    $PASSWORD      = htmlspecialchars(trim($DATA["Password"] ?? ''));
    $PASSWORD2     = htmlspecialchars(trim($DATA["Password2"] ?? ''));

    // Konversi nilai jenis kelamin ke simbol format ENUM database ('Laki-laki' / 'perempuan')
    $JENGKEL = ($JENGKEL_RAW === 'Laki-Laki' || $JENGKEL_RAW === 'Laki-laki' || $JENGKEL_RAW === 'L') ? 'Laki-laki' : 'perempuan';

    // b. Pengujian validasi kelengkapan kolom wajib
    $ERRORS = []; 
    if (empty($ID_ADMIN))      $ERRORS[] = "ID Admin tidak boleh kosong.";
    if (empty($NAMA_ADMIN))    $ERRORS[] = "Nama Admin tidak boleh kosong.";
    if (empty($ALAMAT_ADMIN))  $ERRORS[] = "Alamat Admin tidak boleh kosong.";
    if (empty($TELEPON_ADMIN)) $ERRORS[] = "Telepon Admin tidak boleh kosong.";
    if (empty($EMAIL))         $ERRORS[] = "Email Admin tidak boleh kosong.";
    if (empty($PASSWORD))      $ERRORS[] = "Password Admin tidak boleh kosong.";
    if (empty($PASSWORD2))     $ERRORS[] = "Konfirmasi Password Admin tidak boleh kosong.";

    // Jika ditemukan kolom kosong, simpan pesan kesalahan ke sesi dan hentikan eksekusi
    if (!empty($ERRORS)) {
        $_SESSION['Sess_FormErrors'] = $ERRORS; 
        return false; 
    }

    // c. Pengecekan adanya duplikasi alamat email
    $RESULT = mysqli_query($KONEKSI, "SELECT email FROM tbl_users WHERE email ='$EMAIL'") or die('Error: ' . mysqli_error($KONEKSI));
    if (mysqli_fetch_assoc($RESULT)) {
        $_SESSION['Sess_FormErrors'] = ["Email Admin yang diinput sudah terdaftar."]; 
        return false; 
    }

    // d. Validasi kepastian kecocokan pengetikan ulang password
    if ($PASSWORD !== $PASSWORD2) {
        $_SESSION['Sess_FormErrors'] = ["Password dan Konfirmasi Password Admin tidak sama."]; 
        return false; 
    }

    // e. Validasi kriteria panjang minimal password
    if (strlen($PASSWORD) < 6) {
        $_SESSION['Sess_FormErrors'] = ["Password Admin minimal 6 karakter."]; 
        return false; 
    }

    // f. Penanganan proses unggah berkas foto profil admin
    $GAMBAR_FOTO = ""; 
    if (isset($FILES['Photo']) && $FILES['Photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($FILES['Photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['Sess_FormErrors'] = ['Terjadi error saat mengunggah berkas foto.'];
            return false;
        }

        // Memanggil fungsi upload_files untuk pemrosesan file gambar
        $UPLOAD_RESULT = upload_files(
            $FILES['Photo'],
            $ID_ADMIN,
            $TARGET_FOLDER,
            [
                'max_size' => 52428800,
                'allowed_types' => ['jpg', 'jpeg', 'png', 'bmp'],
                'create_folder' => true,
                'prefix' => 'ADM'
            ]
        );

        // Memeriksa hasil pengunggahan file foto
        if ($UPLOAD_RESULT['success']) {
            $GAMBAR_FOTO = $UPLOAD_RESULT['Filename_tersimpan'];
        } else {
            $_SESSION['Sess_FormErrors'] = [$UPLOAD_RESULT['message']];
            return false;
        }
    }

    // g. Pengenkripsian password pengguna
    $PASSWORD_HASH = password_hash($PASSWORD, PASSWORD_DEFAULT);

    // h. Memulai mekanisme database transaction agar seluruh simpanan bersifat atomic
    mysqli_begin_transaction($KONEKSI);

    try {
        // Menetapkan angka Level 1 secara langsung untuk Admin
        $LEVEL_ADMIN = 1;

        // Query menyimpan data kredensial utama ke tbl_users
        $SQL_USER = "INSERT INTO tbl_users (id_user, email, password, role, created_at, updated_at) 
                     VALUES ('$ID_ADMIN', '$EMAIL', '$PASSWORD_HASH', '$LEVEL_ADMIN', '$WAKTU_SEKARANG', '$WAKTU_SEKARANG')";

        if (!mysqli_query($KONEKSI, $SQL_USER)) {
            throw new Exception("Gagal menambahkan user: " . mysqli_error($KONEKSI));
        }

        // Query menyimpan data biodata ke tbl_admin
        $SQL_ADMIN = "INSERT INTO tbl_admin (nama_admin, telepon_admin, alamat_admin, jenis_kelamin, path_photo_admin, id_user, created_at, updated_at) 
                      VALUES ('$NAMA_ADMIN', '$TELEPON_ADMIN', '$ALAMAT_ADMIN', '$JENGKEL', '$GAMBAR_FOTO', '$ID_ADMIN', '$WAKTU_SEKARANG', '$WAKTU_SEKARANG')";

        if (!mysqli_query($KONEKSI, $SQL_ADMIN)) {
            throw new Exception("Gagal menambahkan admin: " . mysqli_error($KONEKSI));
        }

        // Komit transaksi jika semua eksekusi query berhasil
        mysqli_commit($KONEKSI);
        $_SESSION['Sess_SuccessMessage'] = "Data berhasil ditambahkan!";
        return true;

    } catch (Exception $E) {
        // Batalkan transaksi database jika timbul kegagalan
        mysqli_rollback($KONEKSI);

        // Menghapus file gambar yang terlanjur terupload jika query database mengalami rollback
        if (!empty($GAMBAR_FOTO)) {
            $FILE_PATH = rtrim($TARGET_FOLDER, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $GAMBAR_FOTO;
            if (file_exists($FILE_PATH)) {
                unlink($FILE_PATH);
            }
        }

        $_SESSION['Sess_FormErrors'] = [$E->getMessage()];
        return false;
    }
}

//+=+=========================================+=+
//8. Fungsi Edit User Admin
//+=+=========================================+=+

function edit_admin($DATA, $FILES, $TARGET_FOLDER = "../images/user_admin")
{
    // Memanggil variabel global koneksi dan waktu
    global $KONEKSI, $WAKTU_SEKARANG; 

    // a. Pembersihan masukan data pembaharuan
    $ID_ADMIN      = htmlspecialchars(trim($DATA["IdAdmin"] ?? ''));
    $ID_USER       = htmlspecialchars(trim($DATA["IdUser"] ?? ''));
    $NAMA_ADMIN    = htmlspecialchars(trim($DATA["NamaAdmin"] ?? ''));
    $ALAMAT_ADMIN  = htmlspecialchars(trim($DATA["AlamatAdmin"] ?? ''));
    $TELEPON_ADMIN = htmlspecialchars(trim($DATA["TeleponAdmin"] ?? ''));
    $JENGKEL_RAW   = htmlspecialchars(trim($DATA["Jengkel"] ?? ''));
    $EMAIL         = htmlspecialchars(trim($DATA["Email"] ?? ''));
    $PASSWORD      = htmlspecialchars(trim($DATA["Password"] ?? ''));
    $PASSWORD2     = htmlspecialchars(trim($DATA["Password2"] ?? ''));
    $FOTO_LAMA     = htmlspecialchars(trim($DATA["FotoLama"] ?? ''));

    // Konversi format pilihan jenis kelamin
    $JENGKEL = ($JENGKEL_RAW === 'Laki-Laki' || $JENGKEL_RAW === 'Laki-laki' || $JENGKEL_RAW === 'L') ? 'Laki-laki' : 'perempuan';

    // b. Memvalidasi kolom wajib isi
    $ERRORS = []; 
    if (empty($ID_USER))       $ERRORS[] = "ID User tidak valid.";
    if (empty($NAMA_ADMIN))    $ERRORS[] = "Nama Admin tidak boleh kosong.";
    if (empty($ALAMAT_ADMIN))  $ERRORS[] = "Alamat Admin tidak boleh kosong.";
    if (empty($TELEPON_ADMIN)) $ERRORS[] = "Telepon Admin tidak boleh kosong.";
    if (empty($EMAIL))         $ERRORS[] = "Email Admin tidak boleh kosong.";

    // Pengecekan password Opsional (hanya diproses jika form password diisi)
    if (!empty($PASSWORD)) {
        if ($PASSWORD !== $PASSWORD2) {
            $ERRORS[] = "Password dan Konfirmasi Password tidak sama.";
        }
        if (strlen($PASSWORD) < 6) {
            $ERRORS[] = "Password minimal 6 karakter.";
        }
    }

    // Menyimpan error jika ditemukan kendala validasi
    if (!empty($ERRORS)) {
        $_SESSION['Sess_FormErrors'] = $ERRORS;
        return false; 
    }

    // c. Mengecek ketersediaan email (mengabaikan milik dirinya sendiri)
    $CEK_EMAIL = mysqli_query($KONEKSI, "SELECT email FROM tbl_users WHERE email = '$EMAIL' AND id_user != '$ID_USER'");
    if (mysqli_fetch_assoc($CEK_EMAIL)) {
        $_SESSION['Sess_FormErrors'] = ["Email sudah digunakan."]; 
        return false; 
    }

    // d. Penanganan upload foto baru
    $GAMBAR_FOTO = $FOTO_LAMA; // Menggunakan nama foto lama sebagai default
    $FOTO_BARU_UPLOADED = false;

    if (isset($FILES['Photo']) && $FILES['Photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($FILES['Photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['Sess_FormErrors'] = ['Terjadi error saat mengunggah berkas foto.'];
            return false;
        }

        // Mengeksekusi pengunggahan foto pengganti
        $UPLOAD_RESULT = upload_files(
            $FILES['Photo'],
            $ID_USER,
            $TARGET_FOLDER,
            [
                'max_size' => 52428800,
                'allowed_types' => ['jpg', 'jpeg', 'png', 'bmp'],
                'create_folder' => true,
                'prefix' => 'ADM'
            ]
        );

        if ($UPLOAD_RESULT['success']) {
            $GAMBAR_FOTO = $UPLOAD_RESULT['Filename_tersimpan'];
            $FOTO_BARU_UPLOADED = true;
        } else {
            $_SESSION['Sess_FormErrors'] = [$UPLOAD_RESULT['message']];
            return false;
        }
    }

    // e. Memulai transaksi pembaruan data
    mysqli_begin_transaction($KONEKSI);

    try {
        // Mengatur klausa password jika pengubahan kata sandi diisi
        $SQL_PASSWORD = "";
        if (!empty($PASSWORD)) {
            $PASSWORD_HASH = password_hash($PASSWORD, PASSWORD_DEFAULT);
            $SQL_PASSWORD = ", password = '$PASSWORD_HASH'";
        }

        // Query update pada tabel akun utama
        $SQL_USER = "UPDATE tbl_users SET 
                     email = '$EMAIL' 
                     $SQL_PASSWORD, 
                     updated_at = '$WAKTU_SEKARANG' 
                     WHERE id_user = '$ID_USER'";

        if (!mysqli_query($KONEKSI, $SQL_USER)) {
            throw new Exception("Gagal memperbarui data user: " . mysqli_error($KONEKSI));
        }

        // Query update pada tabel detail profil admin
        $SQL_ADMIN = "UPDATE tbl_admin SET 
                      nama_admin = '$NAMA_ADMIN',
                      telepon_admin = '$TELEPON_ADMIN',
                      alamat_admin = '$ALAMAT_ADMIN',
                      jenis_kelamin = '$JENGKEL',
                      path_photo_admin = '$GAMBAR_FOTO',
                      updated_at = '$WAKTU_SEKARANG' 
                      WHERE id_user = '$ID_USER'";

        if (!mysqli_query($KONEKSI, $SQL_ADMIN)) {
            throw new Exception("Gagal memperbarui data admin: " . mysqli_error($KONEKSI));
        }

        // Menyelesaikan transaksi
        mysqli_commit($KONEKSI);

        // Menghapus file fisik foto lama di server jika berhasil mengganti dengan foto baru
        if ($FOTO_BARU_UPLOADED && !empty($FOTO_LAMA)) {
            $OLD_PATH = rtrim($TARGET_FOLDER, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $FOTO_LAMA;
            if (file_exists($OLD_PATH)) {
                unlink($OLD_PATH);
            }
        }

        $_SESSION['Sess_SuccessMessage'] = "Data berhasil diperbarui!";
        return true;

    } catch (Exception $E) {
        // Pembatalan transaksi jika terjadi masalah
        mysqli_rollback($KONEKSI);

        // Menghapus foto baru yang baru terunggah jika pembaharuan database gagal
        if ($FOTO_BARU_UPLOADED && !empty($GAMBAR_FOTO)) {
            $NEW_PATH = rtrim($TARGET_FOLDER, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $GAMBAR_FOTO;
            if (file_exists($NEW_PATH)) {
                unlink($NEW_PATH);
            }
        }

        $_SESSION['Sess_FormErrors'] = [$E->getMessage()];
        return false;
    }
}

//+=+=========================================+=+
//9. Fungsi Hapus User Admin / Nasabah
//+=+=========================================+=+

function hapus_user($ID_USER)
{
    // Memanggil variabel koneksi global
    global $KONEKSI;

    // Memulai transaksi penghapusan bertahap
    mysqli_begin_transaction($KONEKSI);

    try {
        // 1. Mencari berkas foto terikat di tabel admin
        $CEK_FOTO = mysqli_query($KONEKSI, "SELECT path_photo_admin FROM tbl_admin WHERE id_user = '$ID_USER'");
        if ($CEK_FOTO && mysqli_num_rows($CEK_FOTO) > 0) {
            $ROW_FOTO = mysqli_fetch_assoc($CEK_FOTO);
            $NAMA_FOTO = $ROW_FOTO['path_photo_admin'];

            // Menentukan daftar file gambar default yang dilarang dihapus dari server
            $AVATAR_BAWAAN = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg'];
            if (!empty($NAMA_FOTO) && !in_array($NAMA_FOTO, $AVATAR_BAWAAN)) {
                $PATH_FOTO_ADMIN = __DIR__ . "/../images/user_admin/" . $NAMA_FOTO;
                
                // Menghapus berkas gambar secara fisik di server
                if (file_exists($PATH_FOTO_ADMIN)) {
                    unlink($PATH_FOTO_ADMIN);
                }
            }
        }

        // 2. Menghapus data relasi pada tabel admin dan nasabah
        mysqli_query($KONEKSI, "DELETE FROM tbl_admin WHERE id_user = '$ID_USER'");
        mysqli_query($KONEKSI, "DELETE FROM nasabah WHERE id_nasabah = '$ID_USER'");

        // 3. Menghapus data akun utama dari tbl_users
        $HAPUS_UTAMA = mysqli_query($KONEKSI, "DELETE FROM tbl_users WHERE id_user = '$ID_USER'");
        
        // Memastikan eksekusi hapus utama berhasil
        if (!$HAPUS_UTAMA) {
            throw new Exception("Gagal menghapus data dari tbl_users.");
        }

        // Menyimpan status transaksi penghapusan permanen
        mysqli_commit($KONEKSI);
        return true;

    } catch (Exception $E) {
        // Membatalkan transaksi jika terjadi eror
        mysqli_rollback($KONEKSI);
        return false;
    }
}
/**
 * isAdmin()
 * Mengecek apakah user yang sedang login berlevel Admin.
 * Dipakai di admin/index.php untuk membatasi akses panel admin.
 * @return bool true kalau sudah login dan levelnya Admin
 */
function isAdmin()
{
    // Key session sama dengan yang dibuat login.php (Sess_Email, Sess_Level)
    return !empty($_SESSION['Sess_Email']) && ($_SESSION['Sess_Level'] ?? '') === 'Admin';
}
/**
 * e()
 * Mengamankan teks sebelum ditampilkan di HTML (mencegah XSS).
 * Dipakai di halaman admin/pages/ untuk mencetak data dari database atau session.
 * @param mixed $TEKS Teks yang akan ditampilkan
 * @return string Teks yang sudah di-escape
 */
function e($TEKS)
{
    return htmlspecialchars((string)$TEKS, ENT_QUOTES, 'UTF-8');
}
/**
 * tampil_pesan_session()
 * Menampilkan pesan sukses / daftar error yang disimpan di session
 * (Sess_SuccessMessage dan Sess_FormErrors), lalu menghapusnya agar hanya tampil sekali.
 */
function tampil_pesan_session()
{
    if (!empty($_SESSION['Sess_SuccessMessage'])) {
        echo '<div class="alert alert-success">' . e($_SESSION['Sess_SuccessMessage']) . '</div>';
        unset($_SESSION['Sess_SuccessMessage']);
    }
    if (!empty($_SESSION['Sess_FormErrors'])) {
        echo '<div class="alert alert-danger"><ul class="mb-0">';
        foreach ((array)$_SESSION['Sess_FormErrors'] as $ERR) {
            echo '<li>' . e($ERR) . '</li>';
        }
        echo '</ul></div>';
        unset($_SESSION['Sess_FormErrors']);
    }
}

/**
 * pastikan_tabel_profil()
 * Membuat tabel tbl_profil_user kalau belum ada. Tabel ini menyimpan profil
 * (nama, telepon, alamat, jenis kelamin) akun non-Admin. Tipe kolom id_user
 * disamakan dengan tbl_users.id_user. Hanya berjalan sekali per request.
 */
function pastikan_tabel_profil()
{
    global $KONEKSI;
    static $SUDAH = false;
    if ($SUDAH) {
        return;
    }

    $CEK = mysqli_query($KONEKSI, "SHOW TABLES LIKE 'tbl_profil_user'");
    if (mysqli_num_rows($CEK) === 0) {
        $INFO = tampil(
            "SELECT COLUMN_TYPE AS tipe FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_users' AND COLUMN_NAME = 'id_user'"
        );
        $TIPE = $INFO[0]['tipe'] ?? 'VARCHAR(20)';

        mysqli_query($KONEKSI, "CREATE TABLE tbl_profil_user (
            id_user $TIPE NOT NULL,
            nama VARCHAR(100) NOT NULL DEFAULT '',
            telepon VARCHAR(30) NOT NULL DEFAULT '',
            alamat VARCHAR(255) NOT NULL DEFAULT '',
            jenis_kelamin CHAR(1) NOT NULL DEFAULT '',
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id_user)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die('Gagal membuat tbl_profil_user: ' . mysqli_error($KONEKSI));
    }
    $SUDAH = true;
}

/**
 * foto_admin_url()
 * Membuat URL foto admin untuk atribut <img src> dari halaman di folder admin/.
 * Mengembalikan string kosong kalau nama foto kosong atau filenya tidak ada.
 * @param string $NAMA_FOTO Nama file foto dari kolom path foto di tbl_admin
 * @return string URL foto, atau '' kalau tidak ada
 */
function foto_admin_url($NAMA_FOTO)
{
    $NAMA_FOTO = basename((string)$NAMA_FOTO); // cegah path traversal
    if ($NAMA_FOTO === '' || !is_file(__DIR__ . '/../images/user_admin/' . $NAMA_FOTO)) {
        return '';
    }
    return '../images/user_admin/' . rawurlencode($NAMA_FOTO);
}

/**
 * csrf_token()
 * Mengambil token CSRF milik sesi ini; dibuat otomatis kalau belum ada.
 * @return string Token CSRF
 */
function csrf_token()
{
    if (empty($_SESSION['Sess_Csrf'])) {
        $_SESSION['Sess_Csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['Sess_Csrf'];
}

/**
 * csrf_field()
 * Menghasilkan <input type="hidden"> berisi token CSRF untuk dipasang di setiap form POST.
 * @return string Tag input hidden
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * csrf_valid()
 * Memeriksa token CSRF yang dikirim form cocok dengan token di session.
 * Dipakai di file proses (misal proses_hapus.php) sebelum mengubah data.
 * @return bool true kalau token valid
 */
function csrf_valid()
{
    return !empty($_SESSION['Sess_Csrf']) && hash_equals($_SESSION['Sess_Csrf'], (string)($_POST['csrf'] ?? ''));
}
//+=+=========================================+=+
// 10. Fungsi Pendukung Edit User Non-Admin
//+=+=========================================+=+

/**
 * ambil_user_biasa()
 * Mengambil data user non-Admin dari tbl_users + tbl_profil_user.
 * @param string $ID_USER ID user yang dicari
 * @return array|null Data user (dengan key: id_user, email, role, tipe_user,
 *                    nama, telepon, alamat, jenis_kelamin) atau null kalau tidak ada
 */
function ambil_user_biasa($ID_USER)
{
    global $KONEKSI;
    pastikan_tabel_profil();

    $ID_USER = mysqli_real_escape_string($KONEKSI, $ID_USER);

    $SQL = "SELECT u.id_user, u.email, u.role, t.tipe_user,
                   COALESCE(p.nama, '')          AS nama,
                   COALESCE(p.telepon, '')       AS telepon,
                   COALESCE(p.alamat, '')        AS alamat,
                   COALESCE(p.jenis_kelamin, '') AS jenis_kelamin
            FROM tbl_users u
            LEFT JOIN tbl_tipe_user t ON t.id_tipe_user = u.role
            LEFT JOIN tbl_profil_user p ON p.id_user = u.id_user
            WHERE u.id_user = '$ID_USER'
            LIMIT 1";

    $Q = mysqli_query($KONEKSI, $SQL) or die('Error: ' . mysqli_error($KONEKSI));
    $ROW = mysqli_fetch_assoc($Q);
    return $ROW ?: null;
}

/**
 * user_adalah_admin()
 * Cek apakah user target berlevel Admin (role = 1).
 * @param array $USER Baris data user (dari ambil_user_biasa / tbl_users)
 * @return bool
 */
function user_adalah_admin($USER)
{
    return isset($USER['role']) && (int)$USER['role'] === 1;
}

/**
 * tipe_user_non_admin()
 * Mengambil daftar tipe user yang boleh dipilih di form edit (kecuali Admin).
 * @return array Array baris: ['id_tipe_user' => ..., 'tipe_user' => ...]
 */
function tipe_user_non_admin()
{
    global $KONEKSI;
    // Asumsi: id 1 = Admin, selain itu = Petugas/Pengawas/Nasabah
    $Q = mysqli_query(
        $KONEKSI,
        "SELECT id_tipe_user, tipe_user FROM tbl_tipe_user
         WHERE id_tipe_user <> 1 ORDER BY id_tipe_user ASC"
    ) or die('Error: ' . mysqli_error($KONEKSI));

    $OUT = [];
    while ($R = mysqli_fetch_assoc($Q)) {
        $OUT[] = $R;
    }
    return $OUT;
}

/**
 * kode_jk()
 * Normalisasi nilai jenis kelamin ke kode 'L' / 'P' / ''.
 * Menerima 'Laki-laki', 'L', 'Perempuan', 'P', 'l', 'p', dll.
 * @param string $NILAI
 * @return string 'L', 'P', atau ''
 */
function kode_jk($NILAI)
{
    $N = strtoupper(trim((string)$NILAI));
    if ($N === 'L' || $N === 'LAKI-LAKI' || $N === 'LAKI LAKI' || $N === 'MALE') {
        return 'L';
    }
    if ($N === 'P' || $N === 'PEREMPUAN' || $N === 'FEMALE') {
        return 'P';
    }
    return '';
}

/**
 * edit_user_biasa()
 * Memperbarui data user non-Admin: tbl_users (email, role, password opsional)
 * dan tbl_profil_user (nama, telepon, alamat, jenis_kelamin).
 * @param array $DATA Data POST form edit
 * @return bool true kalau berhasil, false kalau gagal (error disimpan di session)
 */
function edit_user_biasa($DATA)
{
    global $KONEKSI, $WAKTU_SEKARANG;
    pastikan_tabel_profil();

    // a. Ambil & sanitasi input
    $ID_USER   = trim($DATA['IdUser'] ?? '');
    $EMAIL     = strtolower(trim($DATA['Email'] ?? ''));
    $ROLE      = (int)($DATA['Role'] ?? 0);
    $NAMA      = trim($DATA['Nama'] ?? '');
    $TELEPON   = trim($DATA['Telepon'] ?? '');
    $ALAMAT    = trim($DATA['Alamat'] ?? '');
    $JENGKEL   = kode_jk($DATA['Jengkel'] ?? '');
    $PASSWORD  = $DATA['Password'] ?? '';
    $PASSWORD2 = $DATA['Password2'] ?? '';

    $ERRORS = [];

    // b. Validasi dasar
    if ($ID_USER === '')            $ERRORS[] = 'ID User tidak valid.';
    if ($EMAIL === '')              $ERRORS[] = 'Email tidak boleh kosong.';
    if (!filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) $ERRORS[] = 'Format email tidak valid.';
    if ($ROLE <= 0)                 $ERRORS[] = 'Tipe user tidak valid.';
    if ($ROLE === 1)                $ERRORS[] = 'Tidak boleh mengubah user menjadi Admin lewat form ini.';
    if ($NAMA === '')               $ERRORS[] = 'Nama tidak boleh kosong.';

    // c. Validasi password opsional
    if ($PASSWORD !== '') {
        if (strlen($PASSWORD) < 6)   $ERRORS[] = 'Password minimal 6 karakter.';
        if ($PASSWORD !== $PASSWORD2) $ERRORS[] = 'Konfirmasi password tidak cocok.';
    }

    // d. Cek duplikasi email (kecuali milik user sendiri)
    $ID_USER_ESC = mysqli_real_escape_string($KONEKSI, $ID_USER);
    $EMAIL_ESC   = mysqli_real_escape_string($KONEKSI, $EMAIL);
    $CEK = mysqli_query(
        $KONEKSI,
        "SELECT id_user FROM tbl_users WHERE email = '$EMAIL_ESC' AND id_user <> '$ID_USER_ESC' LIMIT 1"
    ) or die('Error: ' . mysqli_error($KONEKSI));
    if (mysqli_fetch_assoc($CEK)) {
        $ERRORS[] = 'Email sudah digunakan oleh akun lain.';
    }

    if (!empty($ERRORS)) {
        $_SESSION['Sess_FormErrors'] = $ERRORS;
        return false;
    }

    // e. Cek role valid ada di tbl_tipe_user dan bukan Admin
    $CEK_ROLE = mysqli_query(
        $KONEKSI,
        "SELECT id_tipe_user FROM tbl_tipe_user WHERE id_tipe_user = $ROLE AND id_tipe_user <> 1"
    ) or die('Error: ' . mysqli_error($KONEKSI));
    if (!mysqli_fetch_assoc($CEK_ROLE)) {
        $_SESSION['Sess_FormErrors'] = ['Tipe user yang dipilih tidak valid.'];
        return false;
    }

    // f. Persiapkan update password (kalau diisi)
    $SQL_PASSWORD = '';
    if ($PASSWORD !== '') {
        $HASH = password_hash($PASSWORD, PASSWORD_DEFAULT);
        $HASH_ESC = mysqli_real_escape_string($KONEKSI, $HASH);
        $SQL_PASSWORD = ", password = '$HASH_ESC'";
    }

    $NAMA_ESC    = mysqli_real_escape_string($KONEKSI, $NAMA);
    $TELEPON_ESC = mysqli_real_escape_string($KONEKSI, $TELEPON);
    $ALAMAT_ESC  = mysqli_real_escape_string($KONEKSI, $ALAMAT);
    $JK_ESC      = mysqli_real_escape_string($KONEKSI, $JENGKEL);
    $WAKTU_ESC   = mysqli_real_escape_string($KONEKSI, $WAKTU_SEKARANG);

    // g. Transaksi
    mysqli_begin_transaction($KONEKSI);
    try {
        $SQL_USER = "UPDATE tbl_users SET
                        email = '$EMAIL_ESC',
                        role  = $ROLE
                        $SQL_PASSWORD,
                        updated_at = '$WAKTU_ESC'
                     WHERE id_user = '$ID_USER_ESC'";
        if (!mysqli_query($KONEKSI, $SQL_USER)) {
            throw new Exception('Gagal update tbl_users: ' . mysqli_error($KONEKSI));
        }

        // Upsert profil (INSERT ... ON DUPLICATE KEY UPDATE)
        $SQL_PROFIL = "INSERT INTO tbl_profil_user
                         (id_user, nama, telepon, alamat, jenis_kelamin, updated_at)
                       VALUES
                         ('$ID_USER_ESC', '$NAMA_ESC', '$TELEPON_ESC', '$ALAMAT_ESC', '$JK_ESC', '$WAKTU_ESC')
                       ON DUPLICATE KEY UPDATE
                         nama = VALUES(nama),
                         telepon = VALUES(telepon),
                         alamat = VALUES(alamat),
                         jenis_kelamin = VALUES(jenis_kelamin),
                         updated_at = VALUES(updated_at)";
        if (!mysqli_query($KONEKSI, $SQL_PROFIL)) {
            throw new Exception('Gagal update tbl_profil_user: ' . mysqli_error($KONEKSI));
        }

        mysqli_commit($KONEKSI);
        $_SESSION['Sess_SuccessMessage'] = 'Data user berhasil diperbarui.';
        return true;

    } catch (Exception $E) {
        mysqli_rollback($KONEKSI);
        $_SESSION['Sess_FormErrors'] = [$E->getMessage()];
        return false;
    }
}
//+=+=========================================+=+
// 11. Fungsi Ganti Password Admin
//+=+=========================================+=+
function ganti_password($DATA)
{
    global $KONEKSI, $WAKTU_SEKARANG;

    $ID_USER          = mysqli_real_escape_string($KONEKSI, trim($DATA['IdUserPassword'] ?? ''));
    $PASS_LAMA        = $DATA['PassLama'] ?? '';
    $PASS_BARU        = $DATA['PassBaru'] ?? '';
    $PASS_BARU_CONFIRM = $DATA['PassBaruConfirm'] ?? '';

    $ERRORS = [];
    if ($ID_USER === '')   $ERRORS[] = 'ID User tidak valid.';
    if ($PASS_LAMA === '') $ERRORS[] = 'Password lama tidak boleh kosong.';
    if ($PASS_BARU === '') $ERRORS[] = 'Password baru tidak boleh kosong.';
    if (strlen($PASS_BARU) < 6) $ERRORS[] = 'Password baru minimal 6 karakter.';
    if ($PASS_BARU !== $PASS_BARU_CONFIRM) $ERRORS[] = 'Konfirmasi password baru tidak sama.';

    if (!empty($ERRORS)) {
        $_SESSION['Sess_FormErrors'] = $ERRORS;
        return false;
    }

    // Ambil password lama dari tbl_users
    $Q = mysqli_query($KONEKSI, "SELECT password FROM tbl_users WHERE id_user = '$ID_USER' LIMIT 1");
    $USER = mysqli_fetch_assoc($Q);

    if (!$USER) {
        $_SESSION['Sess_FormErrors'] = ['Data user tidak ditemukan.'];
        return false;
    }

    // Verifikasi kecocokan password lama
    if (!password_verify($PASS_LAMA, $USER['password'])) {
        $_SESSION['Sess_FormErrors'] = ['Password lama tidak sesuai.'];
        return false;
    }

    // Hash dan simpan password baru
    $HASH_BARU = password_hash($PASS_BARU, PASSWORD_DEFAULT);
    $HASH_ESC  = mysqli_real_escape_string($KONEKSI, $HASH_BARU);

    $SQL = "UPDATE tbl_users SET password = '$HASH_ESC', updated_at = '$WAKTU_SEKARANG' WHERE id_user = '$ID_USER'";
    if (mysqli_query($KONEKSI, $SQL)) {
        $_SESSION['Sess_SuccessMessage'] = 'Password berhasil diubah!';
        return true;
    } else {
        $_SESSION['Sess_FormErrors'] = ['Gagal mengubah password: ' . mysqli_error($KONEKSI)];
        return false;
    }
}
?>