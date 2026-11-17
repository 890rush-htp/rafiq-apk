<?php
// Proteksi tambahan: cegah orang browsing isi folder upload langsung,
// jaga-jaga kalau .htaccess tidak aktif (mis. AllowOverride di server mati).
http_response_code(403);
exit('Forbidden');
