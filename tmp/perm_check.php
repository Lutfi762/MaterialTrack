<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "--- Permission in DB (ERP-related) ---\n";
$all = Permission::pluck('name')->toArray();
$erp = preg_grep('/^(customers|purchase_|goods_receipts|purchase_invoices|quotations|sales_orders|deliveries|sales_invoices|sales_returns|suppliers|warehouses|stock_reservations)\./', $all);
foreach ($erp as $p) { echo "  $p\n"; }

echo "\n--- Admin role permissions ---\n";
$role = Role::whereName('admin')->first();
$rp = $role->permissions->pluck('name')->toArray();
$missing = array_diff($erp, $rp);
echo 'Total ERP permissions: ' . count($erp) . "\n";
echo 'Admin has: ' . count(array_intersect($erp, $rp)) . "\n";
echo "MISSING from admin role:\n";
foreach ($missing as $m) { echo "  ✗ $m\n"; }
if (empty($missing)) { echo "  (none)\n"; }

echo "\n--- Direct user permission check (admin) ---\n";
$u = User::whereEmail('admin@materialtrack.com')->first();
foreach (['purchase_orders.index', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete', 'sales_orders.edit', 'customers.delete', 'goods_receipts.edit'] as $perm) {
    echo "  {$u->email} {$perm}: " . ($u->can($perm) ? 'YES' : 'NO') . "\n";
}

echo "\nOK\n";