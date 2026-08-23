<?php
/**
 * ERP Pengadaan & Penjualan smoke test via HTTP (port 8899).
 */
$base = 'http://127.0.0.1:8899';
@unlink(__DIR__ . '/cookie.txt');
$jar = __DIR__ . '/cookie.txt';
$xsrf = null;

function http_request($url, $method = 'GET', $data = null, $jar, $headers = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
    ]);
    $h = ['Accept: application/json', 'X-Requested-With: XMLHttpRequest'];
    if ($method !== 'GET') { curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); }
    if ($data !== null) {
        $h[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    foreach ($headers as $hh) { $h[] = $hh; }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['code' => $info['http_code'], 'body' => $resp, 'headers' => substr($resp, 0, $info['header_size'])];
}

$pass = 0; $fail = 0;
function check($label, $ok, $detail = '') {
    global $pass, $fail;
    echo ($ok ? 'PASS' : 'FAIL') . " | $label" . ($detail ? " | $detail" : '') . PHP_EOL;
    $ok ? $pass++ : $fail++;
}

// Login
$r = http_request("$base/login", 'GET', null, $jar);
if (preg_match('/XSRF-TOKEN=([^;]+)/', $r['headers'], $m)) { $xsrf = urldecode($m[1]); }
check('GET /login', $r['code'] == 200 && $xsrf, 'code=' . $r['code'] . ' xsrf=' . ($xsrf ? 'ok' : 'MISSING'));

$r = http_request("$base/login", 'POST', ['email' => 'admin@materialtrack.com', 'password' => 'password'], $jar, ['X-XSRF-TOKEN: ' . $xsrf]);
check('POST /login', in_array($r['code'], [200, 302, 303]), 'code=' . $r['code']);

// Page loads
foreach ([
    'customers' => ['index', 'create'],
    'purchase-requests' => ['index', 'create'],
    'purchase-orders' => ['index', 'create'],
    'goods-receipts' => ['index', 'create'],
    'purchase-invoices' => ['index', 'create'],
    'quotations' => ['index', 'create'],
    'sales-orders' => ['index', 'create'],
    'deliveries' => ['index', 'create'],
    'sales-invoices' => ['index', 'create'],
    'sales-returns' => ['index', 'create'],
] as $mod => $pages) {
    foreach ($pages as $page) {
        $url = "$base/apps/$mod" . ($page === 'create' ? '/create' : '');
        $r = http_request($url, 'GET', null, $jar);
        check("GET $mod/$page", $r['code'] === 200, 'code=' . $r['code']);
    }
}

// Create customer
$r = http_request("$base/apps/customers", 'POST', [
    'name' => 'Test Pelanggan ' . date('His'),
    'code' => 'CUS-' . date('His'),
    'phone' => '08123456', 'email' => '', 'address' => 'Jl. Test',
    'pic_name' => '', 'pic_phone' => '', 'type' => 'retail', 'credit_limit' => 1000000,
], $jar, ['X-XSRF-TOKEN: ' . $xsrf]);
check('POST customers (store)', in_array($r['code'], [200, 302, 303]), 'code=' . $r['code']);

// Create purchase order
$r = http_request("$base/apps/purchase-orders", 'POST', [
    'supplier_id' => 1, 'purchase_request_id' => null, 'expected_delivery_date' => date('Y-m-d'),
    'payment_term' => 'Net 30', 'notes' => 'test smoke',
    'items' => [['item_id' => 1, 'qty' => 2, 'unit_price' => 50000]],
], $jar, ['X-XSRF-TOKEN: ' . $xsrf]);
check('POST purchase-orders (store)', in_array($r['code'], [200, 302, 303]), 'code=' . $r['code']);

echo PHP_EOL . "RESULT: $pass pass, $fail fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);