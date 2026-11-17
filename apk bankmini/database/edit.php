<?php
// Form edit sudah digabung di tambah.php (mode edit lewat ?id=...).
// File ini hanya mengalihkan supaya link lama tetap berfungsi.
$id = isset($_GET['id']) ? '&id=' . urlencode($_GET['id']) : '';
header('Location: ../../index.php?pages=akun_tambah' . $id);
exit;
