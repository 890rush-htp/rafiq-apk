# Bank Mini - Panduan Menjalankan

1. Salin folder ini ke `htdocs` (XAMPP) atau `www` (Laragon/WAMP). Nyalakan **Apache** dan **MySQL**.
2. (Kalau database BELUM ada) Buka phpMyAdmin -> tab **Import** -> pilih `database/bankmini_rafiq2526_baru.sql` -> Go.
   (Otomatis membuat database `bankmini_rafiq2526`, semua tabel, dan 1 akun admin.)
3. Cek `inc/functions.php` bagian "Koneksi Database" kalau user/password MySQL kamu bukan `root` / kosong.
4. Buka `http://localhost/<nama-folder>/` -> masuk ke halaman login.

Akun admin awal: **admin@bankmini.com** / **admin123**
(Segera ganti lewat menu *akun pengguna -> edit*.)

Catatan:
- Folder `assets/images/uploads/admin/` harus bisa ditulis (untuk upload foto).
- Menu yang modulnya belum dibuat (tabungan, transaksi, dll.) menampilkan info "belum dibuat",
  bukan error. Isi dengan membuat file yang disebut di pesan tersebut di `admin/pages/`.
- Panel Petugas/Pengawas belum ada; akun level itu belum bisa login sampai foldernya dibuat.

## Database yang sudah ada
Kode menyesuaikan diri dengan struktur tabel yang ada (kolom yang tidak ada dilewati, kolom wajib diisi otomatis),
jadi database lama TIDAK perlu diubah. Untuk mengubah ID dari angka (15, 16, ...) menjadi ADM0001/USR0001:
backup database -> login Admin -> buka `http://localhost/migrasi_id_user.php` -> jalankan -> hapus file tersebut.

## Admin mengedit Petugas / Pengawas / Nasabah
Menu *akun pengguna* -> tombol pensil di baris akun non-Admin. Bisa mengubah tipe user, email, password (opsional),
nama, telepon, alamat, jenis kelamin. Kode-nya ada di `inc/functions.php` (bagian 11) dan
`admin/pages/user_admin/edit_user.php` (fungsi lama di `inc/functions.php` tidak diubah).
Tabel `tbl_profil_user` dibuat otomatis saat halaman daftar akun pertama kali dibuka.
Akun Admin tetap diedit lewat form lama (ada foto profil).
