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
$ch = curl_init("http://127.0.0.1:8899/apps/purchase-orders");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEJAR => $jar,
    CURLOPT_COOKIEFILE => $jar,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', 'X-XSRF-TOKEN: ' . $token],
    CURLOPT_POSTFIELDS => json_encode([
        'supplier_id' => 20, 'purchase_request_id' => null, 'expected_delivery_date' => date('Y-m-d'),
        'payment_term' => 'Net 30', 'notes' => 'smoke', 'items' => [['item_id' => 22, 'qty' => 2, 'unit_price' => 100]],
    ]),
]);
$b = curl_exec($ch);
echo 'HTTP ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n" . substr($b, 0, 800) . "\n";