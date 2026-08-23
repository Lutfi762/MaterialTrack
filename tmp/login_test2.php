<?php
$base = 'http://MaterialTrack.test';
$jar = __DIR__ . '/cookies2.txt';
@unlink($jar);

function req($url, $jar, $headers = [], $post = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $out = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return [$code, $out, $final];
}

[$code] = req($base . '/login', $jar, ['Accept: text/html, application/xhtml+xml']);
echo "GET login: $code\n";
$xsrf = null;
foreach (file($jar) as $line) {
    if (str_starts_with(trim($line), '#') || trim($line) === '') continue;
    $p = explode("\t", trim($line));
    if (trim($p[5] ?? '') === 'XSRF-TOKEN') $xsrf = urldecode(trim($p[6] ?? ''));
}
echo 'xsrf: ' . ($xsrf ? substr($xsrf, 0, 25) . '...' : 'NOT FOUND') . "\n";
$post = 'email=' . urlencode('admin@materialtrack.com') . '&password=' . urlencode('password');
[$code2, $out2, $final2] = req($base . '/login', $jar, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-XSRF-TOKEN: ' . ($xsrf ?? ''),
    'Accept: text/html, application/xhtml+xml',
], $post);
echo "POST login: $code2 final=$final2\n";
if ($code2 == 302 || $code2 == 200) {
    [$c3, $o3] = req($base . '/dashboard', $jar, ['Accept: text/html, application/xhtml+xml']);
    echo "GET dashboard: $c3 size=" . strlen($o3) . "\n\n";
    foreach (['/apps/stock-reservations', '/apps/suppliers', '/apps/warehouses', '/apps/items', '/apps/categories', '/apps/locations', '/apps/departments'] as $p) {
        [$c, $o] = req($base . $p, $jar, ['Accept: text/html, application/xhtml+xml']);
        echo "GET $p : $c size=" . strlen($o) . "\n";
    }
} else {
    echo "--- snippet ---\n" . substr(preg_replace('/\s+/', ' ', $out2), 0, 900) . "\n";
}