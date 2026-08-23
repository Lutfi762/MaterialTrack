<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "customers: " . \App\Models\Customer::count() . PHP_EOL;
$c = \App\Models\Customer::latest()->first();
echo "last customer id=" . ($c?->id) . " status=" . ($c?->status) . PHP_EOL;

echo "purchase_orders: " . \App\Models\PurchaseOrder::count() . PHP_EOL;
$po = \App\Models\PurchaseOrder::latest()->first();
echo "last PO id=" . ($po?->id) . " status=" . ($po?->status) . " items=" . ($po?->items()->count()) . PHP_EOL;

echo "omit: " . \App\Models\Item::where('name', 'like', '%Omit%')->count() . PHP_EOL;
try { echo "suppliers: " . \App\Models\Supplier::count() . PHP_EOL; } catch (\Throwable $e) { echo "supplier err: " . $e->getMessage() . PHP_EOL; }
echo "OK\n";