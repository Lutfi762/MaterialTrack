<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function csrf($html) {
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/i', $html, $m)) {
        return $m[1];
    }
    return null;
}

$base = 'http://localhost:8000';
$jar = __DIR__ . '/jar2.txt';
@unlink($jar);
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar,
    CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 25,
]);

function call($ch, $base, $path, $method = 'GET', $fields = null, $csrf = null) {
    curl_setopt($ch, CURLOPT_URL, $base . $path);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $headers = ['Accept: text/html,application/json'];
    if ($csrf !== null) { $headers[] = 'X-CSRF-TOKEN: ' . $csrf; }
    if ($fields !== null) {
        $body = http_build_query($fields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $r = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hlen = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $head = substr($r, 0, $hlen);
    $body = substr($r, $hlen);
    $loc = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    return ['code' => $code, 'inertia' => (bool) preg_match('/id=.app.|<div id="app"/i', $body), 'loc' => $loc, 'body' => $body];
}

// login
$r = call($ch, $base, '/login');
$csrf = csrf($r['body']);
call($ch, $base, '/login', 'POST', ['email' => 'admin@materialtrack.com', 'password' => 'password', '_token' => $csrf]);
$r = call($ch, $base, $r['loc'] ? preg_replace('#^' . preg_quote($base, '#') . '#', '', $r['loc']) : '/apps/dashboard');
$csrf = csrf($r['body']) ?: $csrf;

echo "GET pages (logged-in admin):\n";
$pages = ['/apps/items','/apps/items/create','/apps/categories','/apps/categories/create','/apps/suppliers','/apps/suppliers/create','/apps/locations','/apps/locations/create','/apps/locations/map','/apps/warehouses','/apps/warehouses/create'];
foreach ($pages as $p) {
    $r = call($ch, $base, $p, 'GET', null, $csrf);
    echo "  $p => {$r['code']} inertia=" . ($r['inertia'] ? 'yes' : 'no') . "\n";
}

// re-test category create with a VALID alpha code_prefix
$cp = 'RP' . strtoupper(\Illuminate\Support\Str::random(3));
// ensure alpha only
$cp = 'RP' . preg_replace('/[^A-Z]/', '', strtoupper(\Illuminate\Support\Str::random(6)));
if (strlen($cp) < 3) { $cp = 'RPABC'; }
$r = call($ch, $base, '/apps/categories', 'POST', ['_token' => $csrf, 'name' => 'probe_cat', 'code_prefix' => $cp, 'description' => 'x'], $csrf);
echo "\nPOST /apps/categories (valid alpha code_prefix=$cp) => {$r['code']} loc=" . ($r['loc'] ?: 'none') . "\n";
\Category::where('name','probe_cat')->delete();
@unlink($jar);
echo "done\n";
