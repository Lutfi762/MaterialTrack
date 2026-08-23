<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('email', 'admin@materialtrack.com')->first();
echo "user_found=" . ($u ? "yes" : "no") . "\n";
$roles = $u ? $u->getRoleNames()->toArray() : [];
echo "roles=" . json_encode($roles) . "\n";
$perms = $u ? $u->getPermissionArray() : [];
echo "perm_count=" . (is_array($perms) ? count($perms) : "non-array") . "\n";
foreach (['items.create','items.delete','items.edit','suppliers.create','suppliers.delete','categories.create','categories.delete','locations.create','locations.delete','warehouses.create','warehouses.delete'] as $p) {
    echo "has $p? " . (is_array($perms) && in_array($p, $perms) ? "yes" : "no") . "\n";
}
