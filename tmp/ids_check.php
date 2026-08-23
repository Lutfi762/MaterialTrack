<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
echo "suppliers ids: " . json_encode(DB::table('suppliers')->pluck('id')) . PHP_EOL;
echo "items ids: " . json_encode(DB::table('items')->pluck('id')) . PHP_EOL;
echo "purchase_requests ids: " . json_encode(DB::table('purchase_requests')->pluck('id')) . PHP_EOL;
echo "customers ids: " . json_encode(DB::table('customers')->pluck('id')) . PHP_EOL;
echo "OK\n";