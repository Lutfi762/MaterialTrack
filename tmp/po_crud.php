<?php
$jar = __DIR__ . '/ck.txt';
$token = null;
foreach (file($jar) as $line) {
    if (str_contains($line, 'XSRF-TOKEN')) {
        $parts = preg_split('/\t+/', trim($line));
        $token = urldecode(end($parts));
        break;
    }
}
function req($url, $method = 'GET', $data = null, $jar, $token) {
    $ch = curl_init($url);
    $o = [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar,
          CURLOPT_HTTPHEADER => ['Accept: application/json', 'X-XSRF-TOKEN: ' . $token],
          CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 25];
    if ($method !== 'GET') {
        $o[CURLOPT_CUSTOMREQUEST] = $method;
        if ($data !== null) {
            $o[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $o[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    }
    curl_setopt_array($ch, $o);
    $b = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $loc = '';
    if (preg_match('/url=\x27([^\x27]+)\x27/', $b, $m)) { $loc = $m[1]; }
    curl_close($ch);
    return [$code, $loc, substr($b, 0, 300)];
}

$id = (int)($argv[1] ?? 7);
[$c1] = req("http://127.0.0.1:8899/apps/purchase-orders/$id/edit", 'GET', null, $jar, $token);
echo "GET edit: $c1\n";

[$c2] = req("http://127.0.0.1:8899/apps/purchase-orders/$id", 'PUT', [
    'supplier_id' => 20, 'purchase_request_id' => null, 'expected_delivery_date' => date('Y-m-d'),
    'payment_term' => 'Net 30', 'notes' => 'smoke EDIT', 'items' => [['item_id' => 22, 'qty' => 5, 'unit_price' => 250]],
], $jar, $token);
echo "PUT update: $c2\n";

// GET show page
[$c3] = req("http://127.0.0.1:8899/apps/purchase-orders/$id", 'GET', null, $jar, $token);
echo "GET show: $c3\n";

[$c4] = req("http://127.0.0.1:8899/apps/purchase-orders/$id", 'DELETE', null, $jar, $token);
echo "DELETE destroy: $c4\n";

[$c5] = req("http://127.0.0.1:8899/apps/purchase-orders/$id/edit", 'GET', null, $jar, $token);
echo "GET edit after delete: $c5 (expect 404)\n";