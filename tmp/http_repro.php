<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category, App\Models\Location, App\Models\Warehouse, App\Models\Department, App\Models\Supplier, App\Models\Item;

// scalar IDs for payloads (schema/models confirmed by probe2)
$catId = Category::value('id');
$locId = Location::value('id');
$whId = Warehouse::value('id');
$depId = Department::value('id');
echo "prereqs: cat=$catId loc=$locId wh=$whId dep=$depId\n";

function csrf($html) {
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/i', $html, $m)) {
        return $m[1];
    }
    return null;
}

$base = 'http://localhost:8000';
$jar = __DIR__ . '/jar.txt';
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
    if ($csrf !== null) {
        $headers[] = 'X-CSRF-TOKEN: ' . $csrf;
    }
    if ($fields !== null) {
        $body = http_build_query($fields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $r = curl_exec($ch);
    if ($r === false) {
        return ['code' => 0, 'head' => '', 'body' => 'CURL_ERR: ' . curl_error($ch), 'loc' => null];
    }
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hlen = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $head = substr($r, 0, $hlen);
    $body = substr($r, $hlen);
    $loc = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    return ['code' => $code, 'head' => $head, 'body' => $body, 'loc' => $loc];
}

echo "1) GET /login\n";
$r = call($ch, $base, '/login');
$csrf = csrf($r['body']);
echo "   => code={$r['code']} csrf=" . ($csrf ? 'ok' : 'MISSING') . "\n";

echo "2) POST /login (admin)\n";
$r = call($ch, $base, '/login', 'POST', ['email' => 'admin@materialtrack.com', 'password' => 'password', '_token' => $csrf]);
$loc = $r['loc'];
echo "   => code={$r['code']} loc=" . ($loc ?: 'none') . "\n";
$snippet = substr(preg_replace('/\s+/', ' ', $r['body']), 0, 200);
if (!$loc) {
    echo "   body: $snippet\n";
}

// follow to authenticated landing to refresh session + csrf
if ($loc) {
    $path = preg_replace('#^' . preg_quote($base, '#') . '#', '', $loc);
    if ($path === '') { $path = '/apps/dashboard'; }
    $r = call($ch, $base, $path);
    echo "   GET {$path} => code={$r['code']}\n";
    $csrf = csrf($r['body']) ?: $csrf;
}

function tryCreateDelete($ch, $base, $label, $createPath, $payload, $csrf, $finder, $deletePrefix) {
    echo "\n== $label ==\n";
    $r = call($ch, $base, $createPath, 'POST', array_merge(['_token' => $csrf], $payload), $csrf);
    $loc = $r['loc'] ?: '';
    $toIndex = $createPath;
    echo "   POST $createPath => code={$r['code']} loc=" . ($loc ?: 'none') . "\n";
    if ($r['code'] != 302 && $r['code'] != 303) {
        echo "   snippet: " . substr(preg_replace('/\s+/', ' ', $r['body']), 0, 240) . "\n";
    }
    $model = $finder();
    if ($model) {
        $r = call($ch, $base, $deletePrefix . $model->id, 'DELETE', ['_token' => $csrf], $csrf);
        echo "   DELETE {$deletePrefix}{$model->id} => code={$r['code']} loc=" . ($r['loc'] ?: 'none') . "\n";
        if ($r['code'] != 302 && $r['code'] != 303) {
            echo "   snippet: " . substr(preg_replace('/\s+/', ' ', $r['body']), 0, 240) . "\n";
        }
    } else {
        echo "   (no model found after create to delete)\n";
    }
}

tryCreateDelete($ch, $base, 'ITEMS', '/apps/items', [
        'category_id' => $catId, 'location_id' => $locId, 'warehouse_id' => $whId,
    'name' => 'rep_item', 'unit' => 'PCS', 'min_stock' => 1, 'current_stock' => 5, 'price' => 1000, 'description' => 'x',
], $csrf, function () { return Item::where('name', 'rep_item')->first(); }, '/apps/items/');

tryCreateDelete($ch, $base, 'CATEGORIES', '/apps/categories', [
    'name' => 'rep_cat', 'code_prefix' => 'Z' . uniqid(), 'description' => 'x',
], $csrf, function () { return Category::where('name', 'rep_cat')->first(); }, '/apps/categories/');

tryCreateDelete($ch, $base, 'SUPPLIERS', '/apps/suppliers', [
    'name' => 'rep_sup', 'code' => 'RP-SUP',
], $csrf, function () { return Supplier::where('name', 'rep_sup')->first(); }, '/apps/suppliers/');

tryCreateDelete($ch, $base, 'LOCATIONS', '/apps/locations', [
    'name' => 'rep_loc', 'code' => 'RP-LOC2', 'address' => 'x',
], $csrf, function () { return Location::where('name', 'rep_loc')->first(); }, '/apps/locations/');

tryCreateDelete($ch, $base, 'WAREHOUSES', '/apps/warehouses', [
    'name' => 'rep_wh', 'code' => 'RP-' . uniqid(), 'address' => 'x', 'is_active' => 1,
], $csrf, function () { return Warehouse::where('name', 'rep_wh')->first(); }, '/apps/warehouses/');

echo "\n9) GET /apps/locations/map (warehouse map)\n";
$r = call($ch, $base, '/apps/locations/map', 'GET', null, $csrf);
echo "   => code={$r['code']}\n";
if ($r['code'] != 200) {
    echo "   snippet: " . substr(preg_replace('/\s+/', ' ', $r['body']), 0, 240) . "\n";
}

// cleanup diag rows
Item::where('name', 'rep_item')->delete();
Category::where('name', 'rep_cat')->delete();
Supplier::where('name', 'rep_sup')->delete();
Location::where('name', 'rep_loc')->delete();
Warehouse::where('name', 'rep_wh')->delete();
Category::where('name', 'repro_cat')->delete();
Location::where('name', 'repro_loc')->delete();
Warehouse::where('name', 'repro_wh')->delete();
@unlink($jar);
echo "\ncleanup done\n";
