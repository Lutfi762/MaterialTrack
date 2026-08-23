<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category, App\Models\Location, App\Models\Warehouse, App\Models\Department, App\Models\Item;
use Illuminate\Support\Facades\Schema;

echo "== categories columns ==\n";
foreach (Schema::getColumnListing('categories') as $col) echo "  $col\n";
echo "has code_prefix? " . (Schema::hasColumn('categories', 'code_prefix') ? 'yes' : 'NO') . "\n";

echo "\n== Category firstOrCreate probe ==\n";
$cat = Category::firstOrCreate(['name' => 'repro_cat'], ['code_prefix' => 'RP', 'description' => 'x']);
echo "type=" . gettype($cat) . " class=" . (is_object($cat) ? get_class($cat) : 'n/a') . "\n";
echo "cat=" . var_export($cat, true) . "\n";
if (is_object($cat)) echo "cat->id=" . var_export($cat->id, true) . " code_prefix=" . var_export($cat->code_prefix, true) . "\n";

echo "\n== counts ==\n";
echo "categories=" . Category::count() . "\n";
echo "locations=" . Location::count() . "\n";
echo "warehouses=" . Warehouse::count() . "\n";
echo "departments=" . Department::count() . "\n";
echo "items=" . Item::count() . "\n";

echo "\n== first ids ==\n";
echo "cat1=" . var_export(Category::value('id'), true) . "\n";
echo "loc1=" . var_export(Location::value('id'), true) . "\n";
echo "wh1=" . var_export(Warehouse::value('id'), true) . "\n";
echo "dep1=" . var_export(Department::value('id'), true) . "\n";
