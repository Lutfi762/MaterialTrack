<?php
$base = 'http://MaterialTrack.test';
$jar = __DIR__ . '/cookies.txt';
@unlink($jar);

function req($url, $jar, $headers = [], $post = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $h = array_merge($headers, ['Accept: text/html', 'User-Agent: diag']);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($post) ? $post : http_build_query($post));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
    $out = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return [$code, $out, $final];
}

[$code, $out] = req($base . '/login', $jar);
echo "GET login: $code\n";
$xsrf = null;
preg_match('/XSRF-TOKEN[^;]*=([^;]+)/', implode("\n", array_slice(explode("\n", $out), 0, 40)), $m);
if (!$m) {
    preg_match('/XSRF-TOKEN[^;]*=([^;]+)/', implode("\n", explode("\r\n", $out)), $m);
}
if ($m) $xsrf = urldecode($m[1]);
echo 'xsrf: ' . ($xsrf ? substr($xsrf, 0, 30) . '...' : 'NOT FOUND') . "\n";
$post = json_encode(['email' => 'admin@materialtrack.com', 'password' => 'password']);
$jwt = implode('', array_map(fn($l) => trim($l) ? $l : '', explode("\n", $out)));
[$code2, $out2, $final2] = req($base . '/login', $jar, [
    'Content-Type: application/json',
    'X-XSRF-TOKEN: ' . ($xsrf ?? ''),
    'X-Inertia: true',
], $post);
echo "POST login: $code2 final=$final2\n";
if ($code2 >= 200 && $code2 < 400 && $final2 !== $base . '/login') {
    foreach (['/apps/stock-reservations', '/apps/suppliers', '/apps/warehouses', '/apps/items', '/apps/categories', '/apps/locations', '/apps/departments'] as $p) {
        [$c, $o] = req($base . $p, $jar, ['X-Inertia: true', 'X-Inertia-Version: x']);
        $ctx = $c == 200 && (strpos($o, '<title>') !== false || strpos($o, 'reservation') !== false);
        echo "GET $p : $c size=" . strlen($o) . "\n";
    }
} else {
    echo "LOGIN FAILED\n";
}