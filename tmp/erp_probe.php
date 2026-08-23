<?php
/**
 * ERP probe — PHP curl, batch mode. argv[1] = batch:
 * 1: customers (index+create+store)
 * 2: purchase-requests, purchase-orders (index+create)
 * 3: purchase-orders store, goods-receipts, purchase-invoices, quotations
 * 4: sales-orders, deliveries, sales-invoices, sales-returns
 */
$jar = __DIR__ . '/ck.txt';
$token = null;
foreach (file($jar) as $line) {
    if (str_contains($line, 'XSRF-TOKEN')) {
        $parts = preg_split('/\t+/', trim($line));
        $token = urldecode(end($parts));
        break;
    }
}
if (!$token) { fwrite(STDERR, "NO XSRF TOKEN\n"); exit(2); }

function hreq($url, $jar, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_NOBODY => false,
    ]);
    $h = [];
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data !== null) {
            $h[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    if ($token) { $h[] = 'X-XSRF-TOKEN: ' . $token; }
    if ($h) { curl_setopt($ch, CURLOPT_HTTPHEADER, $h); }
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return $err ? 0 : $code;
}
function ck($label, $code, $expect = 200) {
    $ok = $code === $expect;
    echo ($ok ? 'PASS' : 'FAIL') . " | $label | got=$code expect=$expect\n";
}

$batch = (int)($argv[1] ?? 1);
switch ($batch) {
    case 1:
        ck('GET customers', hreq("http://127.0.0.1:8899/apps/customers", $jar));
        ck('GET customers/create', hreq("http://127.0.0.1:8899/apps/customers/create", $jar));
        ck('POST customers', hreq("http://127.0.0.1:8899/apps/customers", $jar, 'POST', [
            'name' => 'Test Cus ' . date('His'), 'code' => 'CUS' . date('His'),
            'phone' => '081', 'email' => '', 'address' => 'x', 'pic_name' => '', 'pic_phone' => '',
            'type' => 'retail', 'credit_limit' => 1000,
        ], $token), 302);
        break;
    case 2:
        foreach (['purchase-requests', 'purchase-orders'] as $m) {
            ck("GET $m", hreq("http://127.0.0.1:8899/apps/$m", $jar));
            ck("GET $m/create", hreq("http://127.0.0.1:8899/apps/$m/create", $jar));
        }
        break;
    case 3:
        ck('POST purchase-orders', hreq("http://127.0.0.1:8899/apps/purchase-orders", $jar, 'POST', [
            'supplier_id' => 1, 'purchase_request_id' => null, 'expected_delivery_date' => date('Y-m-d'),
            'payment_term' => 'Net 30', 'notes' => 'smoke', 'items' => [['item_id' => 1, 'qty' => 2, 'unit_price' => 100]],
        ], $token), 302);
        foreach (['goods-receipts', 'purchase-invoices', 'quotations'] as $m) {
            ck("GET $m", hreq("http://127.0.0.1:8899/apps/$m", $jar));
            ck("GET $m/create", hreq("http://127.0.0.1:8899/apps/$m/create", $jar));
        }
        break;
    case 4:
        foreach (['sales-orders', 'deliveries', 'sales-invoices', 'sales-returns'] as $m) {
            ck("GET $m", hreq("http://127.0.0.1:8899/apps/$m", $jar));
            ck("GET $m/create", hreq("http://127.0.0.1:8899/apps/$m/create", $jar));
        }
        break;
}
echo "BATCH $batch done\n";