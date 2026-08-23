<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category, App\Models\Item, App\Models\Supplier, App\Models\Location, App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;

echo "locale=" . app()->getLocale() . " required_msg=" . trans('validation.required') 
   . " attributes.name=" . trans('validation.attributes.name') . "\n";

$admin = App\Models\User::where('email', 'admin@materialtrack.com')->first();
auth()->login($admin);
$pass = 0; $fail = 0;
function chk($label, $cond) { global $pass, $fail; echo ($cond ? "PASS" : "FAIL") . " $label\n"; $cond ? $pass++ : $fail++; }

// bersihkan sisa run sebelumnya (idempotent)
foreach ([App\Models\Item::class, App\Models\Supplier::class, App\Models\Location::class, App\Models\Warehouse::class, App\Models\Category::class] as $cls) {
    try {
        $cls::where('code', 'like', 'DG%')->orWhere('name', 'like', '__diag%')->delete();
    } catch (\Throwable $e) { // kolom 'code' tidak ada di semua tabel
        try { $cls::where('name', 'like', '__diag%')->delete(); } catch (\Throwable $e2) {}
    }
}

$cat = Category::firstOrCreate(['name' => '__diag_cat'], ['code_prefix' => 'DG', 'description' => 'diag']);
$loc = Location::create(['name' => '__diag_loc', 'code' => 'DG-LOC']);
$wh  = Warehouse::create(['name' => '__diag_wh', 'code' => 'DG-WH', 'is_active' => true]);
$sup = Supplier::create(['name' => '__diag_sup', 'code' => 'DG-SUP']);
$item = Item::create(['category_id' => $cat->id, 'location_id' => $loc->id, 'warehouse_id' => $wh->id,
    'code' => 'DG-ITM', 'name' => '__diag_item', 'unit' => 'PCS', 'min_stock' => 1, 'current_stock' => 5]);

// policy checks
chk('ItemPolicy delete permits admin', Gate::forUser($admin)->allows('delete', $item));
chk('ItemPolicy edit permits admin', Gate::forUser($admin)->allows('update', $item));
chk('LocationPolicy delete permits admin', Gate::forUser($admin)->allows('delete', $loc));

// warehouse destroy: detach then delete
$wh2 = $item->warehouse; $wh2->items()->update(['warehouse_id' => null]); $wh2->delete();
$item->refresh();
chk('warehouse delete detaches item (warehouse_id null)', $item->warehouse_id === null && Warehouse::find($wh2->id) === null);

// location destroy: detach then delete
$loc2 = $item->location; $loc2->items()->update(['location_id' => null]); $loc2->delete();
$item->refresh();
chk('location delete detaches item (location_id null)', $item->location_id === null && Location::find($loc2->id) === null);

// supplier destroy + item destroy
$sup->delete();
chk('supplier delete works', Supplier::find($sup->id) === null);
$item->delete();
chk('item delete works', Item::find($item->id) === null);

// cleanup
$cat->delete();
echo "== $pass pass, $fail fail ==\n";
exit($fail > 0 ? 1 : 0);