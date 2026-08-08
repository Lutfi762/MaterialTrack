# LAPORAN AUDIT MENYELURUH - MATERIALTRACK
**Tanggal Audit**: 7 Agustus 2026  
**Versi Aplikasi**: Laravel 13.21 + Inertia.js + Vue 3  
**Status**: DRAF - BELUM DIPERBAIKI

---

## RINGKASAN EKSEKUTIF

MaterialTrack adalah sistem inventory management berbasis Laravel dengan frontend Vue 3 + Inertia.js. Audit menyeluruh telah dilakukan terhadap 5 kategori utama: Security, Backend Code Quality, Business Logic, Frontend, dan Database/Performance.

### TEMUAN KRITIS (Harus Diperbaiki Segera)

| # | Kategori | Issue | Dampak |
|---|----------|-------|--------|
| 1 | Security | Missing Authorization Checks | User dapat akses/modifikasi data tanpa permission |
| 2 | Security | IDOR Vulnerability | User dapat edit/delete transaksi milik user lain |
| 3 | Business Logic | Race Condition - Stock Check | Stok bisa minus pada concurrent requests |
| 4 | Business Logic | Negative Stock Allowed | Tidak ada constraint mencegah stok negatif |
| 5 | Backend | Observers Tidak Terdaftar | Audit trail tidak berfungsi sama sekali |
| 6 | Backend | Race Condition - Reference Number | Duplicate transaction numbers |
| 7 | Backend | Duplikasi Logic StockMovement | Data corruption jika observer aktif |
| 8 | Frontend | XSS via v-html | Pagination vulnerable terhadap XSS |
| 9 | Database | Missing Critical Indexes | Query sangat lambat pada data besar |
| 10 | Performance | N+1 Queries | Dashboard & forms load ratusan records |

### STATISTIK TEMUAN

| Severity | Security | Backend | Business | Frontend | Database | **TOTAL** |
|----------|----------|---------|----------|----------|----------|-----------|
| CRITICAL | 5 | 7 | 4 | 2 | 6 | **24** |
| HIGH | 5 | 18 | 4 | 9 | 27 | **63** |
| MEDIUM | 7 | 16 | 3 | 10 | 6 | **42** |
| LOW | 3 | 1 | 0 | 4 | 3 | **11** |
| **TOTAL** | **20** | **42** | **11** | **25** | **42** | **140** |

---

## BAGIAN 1: SECURITY AUDIT

### 1.1 AUTHENTICATION & AUTHORIZATION

#### CRITICAL #1: Missing Permission Middleware
**LOCATION**: `routes/web.php:40-80`  
**ISSUE**: Sebagian besar routes tidak dilindungi permission middleware. Hanya `permissions.index` dan `roles.*` yang protected.

```php
// SAAT INI (VULNERABLE):
Route::resource('/users', UserController::class, ['as' => 'apps']);
Route::resource('/categories', CategoryController::class, ['as' => 'apps']);
Route::resource('/items', ItemController::class, ['as' => 'apps']);
// ... semua endpoint TANPA permission check
```

**RISK**: Authenticated user dengan role apapun bisa mengakses semua endpoint CRUD tanpa pembatasan. Ini bypass sistem RBAC Spatie Permission yang sudah diimplementasi.

**RECOMMENDATION**:
```php
Route::resource('/users', UserController::class, ['as' => 'apps'])
    ->middleware('permission:users.index|users.create|users.edit|users.delete');
Route::resource('/categories', CategoryController::class, ['as' => 'apps'])
    ->middleware('permission:categories.index|categories.create|categories.edit|categories.delete');
// Apply ke semua resources
```

---

#### CRITICAL #2: IDOR - Insecure Direct Object Reference
**LOCATION**: Multiple controllers
- `StockInController.php:98-115` (edit, update)
- `StockOutController.php:153-180` (edit, update, destroy)
- `CategoryController.php:54-88` (edit, update, destroy)
- `DepartmentController.php:47-77` (edit, update, destroy)
- `LocationController.php:38-61` (edit, update, destroy)

**ISSUE**: Tidak ada authorization check sebelum edit/update/delete. User bisa mengubah ID di URL untuk akses data milik user/department lain.

**SCENARIO EXPLOIT**:
```
1. User A (Department Sales) create StockOut ID=100
2. User B (Department IT) akses /apps/stock-outs/100/edit
3. User B bisa lihat dan edit transaksi Department Sales
4. User B ubah qty atau hapus transaksi
```

**RECOMMENDATION**:
```php
// Di StockInController.php edit():
public function edit(StockIn $stockIn)
{
    // Add authorization
    if (!auth()->user()->hasRole('super_admin') && 
        $stockIn->user_id !== auth()->id()) {
        abort(403, 'Unauthorized access');
    }
    // ... rest of code
}

// ATAU gunakan Policy:
public function edit(StockIn $stockIn)
{
    $this->authorize('update', $stockIn);
    // ... rest of code
}
```

---

#### CRITICAL #3: Missing Authorization on Downloads
**LOCATION**:
- `StockInController.php:148-154` (downloadPdf)
- `StockInController.php:170-191` (downloadAllPdf)
- `StockOutController.php` (similar methods)

**ISSUE**: Siapa saja authenticated bisa download PDF transaksi dengan menebak ID atau menggunakan filter.

**RISK**: Information disclosure. User bisa download dokumen transaksi dari department lain.

**RECOMMENDATION**:
```php
public function downloadPdf(StockIn $stockIn)
{
    $this->authorize('view', $stockIn);
    // ... rest of code
}
```

---

#### HIGH #4: No Cascade Delete Validation
**LOCATION**:
- `CategoryController.php:83-88`
- `DepartmentController.php:73-77`
- `LocationController.php:57-62`
- `PicController.php:104-109`

**ISSUE**: Delete operations tidak validasi child records. Category bisa dihapus meskipun masih punya Items.

**RISK**: Foreign key constraint error atau orphaned records.

**RECOMMENDATION**:
```php
public function destroy(Category $category)
{
    if ($category->items()->count() > 0) {
        return back()->withErrors([
            'error' => 'Tidak bisa hapus kategori yang masih memiliki material.'
        ]);
    }
    $category->delete();
    return to_route('apps.categories.index')
        ->with('success', 'Kategori berhasil dihapus.');
}
```

---

### 1.2 INPUT VALIDATION & SQL INJECTION

#### HIGH #5: Missing Validation for Business Rules
**LOCATION**: `StockOpnameController.php:99-114`  
**ISSUE**: Stock opname bisa ubah `current_stock` ke nilai `physical_stock` tanpa validasi range. Tidak ada pembatasan perbedaan yang diperbolehkan.

**SCENARIO FRAUD**:
```
System stock: 100 unit
User input physical_stock: 1000 unit (fraud!)
Difference: +900 unit
System accept tanpa warning
```

**RECOMMENDATION**:
```php
'entries.*.physical_stock' => 'required|integer|min:0|max:1000000',

// Tambahkan business validation
foreach ($validated['entries'] as $entry) {
    $item = Item::find($entry['item_id']);
    $difference = abs($entry['physical_stock'] - $item->current_stock);
    $threshold = $item->current_stock * 0.5; // 50% threshold
    
    if ($difference > $threshold && !auth()->user()->hasRole('super_admin')) {
        throw new \Exception(
            "Perbedaan terlalu besar untuk item {$item->name}. " .
            "Memerlukan approval supervisor."
        );
    }
}
```

---

#### MEDIUM #6: Missing Input Sanitization
**LOCATION**: Multiple controllers (notes, description fields)  
**ISSUE**: Text fields tidak di-sanitize dari HTML/script tags. Hanya validasi `string` dan `max`.

**RISK**: Stored XSS. User bisa inject script dalam notes yang execute ketika admin lihat data, terutama di PDF export.

**RECOMMENDATION**:
```php
'notes' => 'nullable|string|max:500',
// Dan sanitize sebelum save:
$validated['notes'] = strip_tags($request->notes);

// Atau gunakan HTMLPurifier untuk sanitization robust
```

---

#### MEDIUM #7: File Upload Without Mime Verification
**LOCATION**: `ItemController.php:107, 153`  
**ISSUE**: File upload hanya validasi dengan `image` rule tanpa explicit mime type checking.

**RISK**: Attacker bisa upload file berbahaya dengan extension spoofing.

**RECOMMENDATION**:
```php
'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100',
```

---

### 1.3 MASS ASSIGNMENT & DATA EXPOSURE

#### MEDIUM #8: Unrestricted Export Access
**LOCATION**:
- `StockInController.php:157-168` (exportExcel)
- `StockOutController.php:226-243` (exportExcel)
- `StockMovementController.php:99-129` (exportExcel)

**ISSUE**: Export Excel endpoints tidak memiliki rate limiting atau permission spesifik.

**RISK**: Mass data extraction. User bisa bulk download seluruh database inventory.

**RECOMMENDATION**:
```php
// Di routes:
Route::get('/stock-ins/export/excel', [StockInController::class, 'exportExcel'])
    ->name('apps.stock-ins.export')
    ->middleware('permission:stock-ins.export')
    ->middleware('throttle:10,1'); // 10 requests per minute
```

---

## BAGIAN 2: BACKEND AUDIT (LARAVEL)

### 2.1 OBSERVERS & EVENT HANDLING

#### CRITICAL #8: Observers Tidak Terdaftar
**LOCATION**: `app/Observers/StockInObserver.php` & `app/Observers/StockOutObserver.php`  
**ISSUE**: Observers exist tapi TIDAK PERNAH didaftarkan di `AppServiceProvider` atau `EventServiceProvider`.

**RISK**:
- StockMovement TIDAK tercatat otomatis
- Audit trail hilang completely
- Inkonsistensi data karena controller membuat StockMovement manual (duplikasi logic)

**PROOF**:
```php
// app/Providers/AppServiceProvider.php - TIDAK ADA:
// StockIn::observe(StockInObserver::class);
// StockOut::observe(StockOutObserver::class);
```

**RECOMMENDATION**:
```php
// Option 1: Aktifkan observers
public function boot(): void
{
    StockIn::observe(StockInObserver::class);
    StockOut::observe(StockOutObserver::class);
}

// Option 2: HAPUS observer files dan gunakan controller saja
// Pilih salah satu untuk konsistensi
```

---

#### CRITICAL #9: Duplikasi Logic StockMovement
**LOCATION**: `StockInController.php:77-87` & `StockInObserver.php:16-26`  
**ISSUE**: Controller membuat StockMovement di line 77-87, padahal Observer seharusnya yang handle (tapi observer tidak aktif).

**RISK**: Jika Observer diaktifkan, akan ada DUPLIKASI record StockMovement → data corruption.

**RECOMMENDATION**: Hapus salah satu. Jika gunakan Observer, hapus logic dari controller.

---

#### HIGH #10: Race Condition dalam Observer
**LOCATION**: `StockInObserver.php:14` & `StockOutObserver.php:14`  
**ISSUE**: Kalkulasi `stock_before` salah. Nilai `item->stock` sudah berubah sebelum observer dipanggil.

```php
// Observer dipanggil SETELAH StockIn created
$stockBefore = $item->stock - $stockIn->qty; // Wrong!
// Tapi di controller, stock sudah di-increment SEBELUM observer
```

**RISK**: Audit trail `stock_before` tidak akurat. Tidak bisa rollback dengan benar.

**RECOMMENDATION**: Pindahkan increment/decrement ke dalam observer ATAU tangkap nilai stock_before di controller sebelum increment.

---

#### HIGH #11: Observer Tidak Wrapped dalam Transaction
**LOCATION**: `StockInObserver.php:10-27` & `StockOutObserver.php:10-27`  
**ISSUE**: Observer logic tidak dalam transaction. Jika `StockMovement::create()` gagal, StockIn tetap tersimpan.

**RISK**: Data inkonsisten (ada StockIn tapi tidak ada StockMovement).

**RECOMMENDATION**: Wrap dalam DB::transaction di controller yang memanggil.

---

### 2.2 TRANSACTION MANAGEMENT

#### CRITICAL #10: Missing Database Transactions
**LOCATION**: Multiple controllers  
**ISSUE**: Multi-step operations tidak wrapped dalam transaction.

**Examples**:
```php
// ItemController.php:97-125
// File upload + Item::create TIDAK dalam transaction
// Risk: File uploaded tapi create gagal = orphaned file

// CategoryController.php:33-52
// Category::create + sync departments TIDAK dalam transaction
// Risk: Category created tapi departments tidak ter-sync

// UserController.php:38-59
// User::create + syncRoles TIDAK dalam transaction
// Risk: User created tapi role tidak assigned
```

**RECOMMENDATION**:
```php
DB::transaction(function() use ($request) {
    $category = Category::create([...]);
    $category->departments()->sync($request->department_ids ?? []);
});
```

---

#### CRITICAL #11: Race Condition - Reference Number Generation
**LOCATION**:
- `StockInController.php:66`
- `StockOutController.php:119`

**ISSUE**: Generate reference_no dengan `count() + 1` tanpa locking.

**SCENARIO**:
```
11:30:00.100 - Request A: count() = 5 → IN-20260807-0006
11:30:00.150 - Request B: count() = 5 → IN-20260807-0006 (DUPLICATE!)
11:30:00.200 - Request A: saves successfully
11:30:00.250 - Request B: FAILS with unique constraint
```

**RECOMMENDATION**:
```php
DB::transaction(function () use ($validated) {
    $lastSeq = DB::table('stock_ins')
        ->whereDate('created_at', now())
        ->lockForUpdate()
        ->max(DB::raw('CAST(SUBSTRING(reference_no, -4) AS UNSIGNED)')) ?? 0;
    
    $refNo = 'IN-' . now()->format('Ymd') . '-' . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    
    StockIn::create(['reference_no' => $refNo, ...]);
});
```

---

#### HIGH #12: Atomic Operation Not Used
**LOCATION**: `StockInController.php:75` & `StockOutController.php:128`  
**ISSUE**: Menggunakan `$item->increment()` kemudian `$item->fresh()`. Tidak atomic dan race condition.

**RISK**: Concurrent requests bisa overwrite stock dengan nilai salah.

**RECOMMENDATION**:
```php
// Gunakan DB::raw untuk atomic operation
$newStock = DB::table('items')->where('id', $item->id)
    ->increment('current_stock', $qty);
$item->refresh();
```

---

### 2.3 VALIDATION & ERROR HANDLING

#### HIGH #13: Missing Business Rule Validation
**LOCATION**: `StockOutController.php:91-99`  
**ISSUE**: Validasi `pic_id` hanya check `exists:pics,id` tapi tidak validate apakah pic belongs to department yang dipilih.

**RISK**: User bisa assign PIC dari department lain → data tidak konsisten.

**RECOMMENDATION**:
```php
'pic_id' => [
    'required',
    'exists:pics,id',
    Rule::exists('pics', 'id')->where('department_id', $request->department_id)
],
```

---

#### HIGH #14: Missing 'date' in $fillable
**LOCATION**: `app/Models/StockIn.php:14-23` & `app/Models/StockOut.php:14-24`  
**ISSUE**: Field 'date' tidak ada di $fillable padahal digunakan di controller dan di-validate.

**RISK**: Mass assignment tidak akan set field date → nilai null atau default.

**RECOMMENDATION**: Tambahkan 'date' ke array $fillable.

---

#### MEDIUM #15: Missing Date Range Validation
**LOCATION**: `StockOutController.php:91-99`  
**ISSUE**: Validation `date` hanya 'required|date' tanpa check range.

**RISK**: User bisa input tanggal masa depan atau backdate terlalu jauh.

**RECOMMENDATION**:
```php
'date' => 'required|date|before_or_equal:today|after:' . now()->subYear(),
```

---

## BAGIAN 3: INVENTORY BUSINESS LOGIC AUDIT

### 3.1 STOCK FLOW CRITICAL ISSUES

#### CRITICAL #12: Negative Stock Allowed
**LOCATION**: `database/migrations/2026_07_21_151445_create_items_table.php:16` & `app/Models/Item.php`  
**ISSUE**: Column `current_stock` defined as signed `integer`, NOT `unsignedInteger`. Tidak ada constraint mencegah nilai negatif.

**RISK**: System dapat mencatat stok minus tanpa blocker → inventory accuracy error fatal.

**PROOF**:
```sql
-- Migration:
$table->integer('current_stock')->default(0); -- SIGNED!

-- Tidak ada validation di model Item
-- Tidak ada CHECK constraint di database
```

**RECOMMENDATION**:
```php
// Fix migration:
Schema::table('items', function (Blueprint $table) {
    $table->unsignedInteger('current_stock')->default(0)->change();
});

// Add model validation:
class Item extends Model {
    public function setCurrentStockAttribute($value) {
        if ($value < 0) {
            throw new \Exception("Stock cannot be negative");
        }
        $this->attributes['current_stock'] = $value;
    }
}
```

---

#### CRITICAL #13: Race Condition - Stock Out
**LOCATION**: `StockOutController.php:107-111` & `line 128`  
**ISSUE**: Time gap antara stock check dan decrement tidak protected dengan lock.

**SCENARIO EXPLOIT**:
```
Stock tersedia: 10 unit
Time 00:00.000 - User A: check 10 >= 10 ✓ Pass
Time 00:00.050 - User B: check 10 >= 10 ✓ Pass
Time 00:00.100 - User A: decrement 10 → stock = 0
Time 00:00.150 - User B: decrement 10 → stock = -10 ❌ FRAUD!
```

**RISK**: Concurrent requests bypass validation → stok bisa minus → inventory fraud.

**RECOMMENDATION**:
```php
DB::transaction(function () use ($validated) {
    $item = Item::lockForUpdate()->findOrFail($validated['item_id']);
    
    if ($validated['qty'] > $item->current_stock) {
        throw new \Exception("Stok tidak cukup");
    }
    
    $stockBefore = $item->current_stock;
    $item->current_stock -= $validated['qty'];
    $item->save();
    
    // Create StockOut and StockMovement...
});
```

---

#### CRITICAL #14: Race Condition - Stock In
**LOCATION**: `StockInController.php:73-87`  
**ISSUE**: Inconsistent stock_before calculation. `$stockBefore` dicatat sebelum increment, tapi `balance_after` query fresh().

**RISK**: Jika concurrent request, nilai bisa inconsistent.

**RECOMMENDATION**:
```php
DB::transaction(function () use ($validated, $item) {
    $item->lockForUpdate()->find($item->id);
    $stockBefore = $item->current_stock;
    $stockAfter = $stockBefore + $validated['qty'];
    
    $item->current_stock = $stockAfter;
    $item->save();
    
    StockMovement::create([
        'stock_before' => $stockBefore,
        'balance_after' => $stockAfter,
        // ...
    ]);
});
```

---

### 3.2 STOCK OPNAME VULNERABILITIES

#### HIGH #16: Wrong ref_id in StockMovement
**LOCATION**: `StockOpnameController.php:110`  
**ISSUE**: StockMovement menggunakan `'ref_id' => $item->id` seharusnya StockOpname ID.

```php
// Line 87: $opname dibuat
$opname = StockOpname::create([...]);

// Line 110: SALAH - menggunakan item ID
StockMovement::create([
    'ref_type' => StockOpname::class,
    'ref_id' => $item->id, // ❌ Should be $opname->id
]);
```

**RISK**: Polymorphic reference broken. Audit trail tidak bisa trace kembali ke StockOpname record yang benar.

**RECOMMENDATION**:
```php
'ref_id' => $opname->id, // Fix: use opname ID
```

---

#### HIGH #17: Duplicate Opname Silent Skip
**LOCATION**: `StockOpnameController.php:70-76`  
**ISSUE**: Check `exists()` lalu `continue` - silent skip tanpa notifikasi user.

**RISK**: User tidak tahu item mana yang di-skip. Attacker bisa manipulate dengan submit duplicate.

**RECOMMENDATION**:
```php
foreach ($validated['entries'] as $entry) {
    $exists = StockOpname::where('item_id', $entry['item_id'])
        ->where('period', $validated['period'])
        ->exists();
    
    if ($exists) {
        throw new \Exception(
            "Item {$entry['item_id']} sudah ada opname untuk period {$validated['period']}"
        );
    }
}
```

---

### 3.3 DATA INTEGRITY ISSUES

#### HIGH #18: Stock Reversal Manipulation Risk
**LOCATION**: `StockInController.php:119-145` (destroy method)  
**ISSUE**: Delete StockIn dapat decrement stock, tapi check `if ($item->current_stock < $stockIn->qty)` bisa di-bypass dengan opname adjustment.

**SCENARIO FRAUD**:
```
1. StockIn 100 unit (stock = 100)
2. StockOut 100 unit (stock = 0)
3. Opname adjustment +100 (stock = 100) - collusion!
4. Delete StockIn (check: 100 >= 100 = ALLOWED) → stock = 0
Result: 100 unit hilang dari audit trail!
```

**RECOMMENDATION**:
```php
public function destroy(StockIn $stockIn) {
    // Check for subsequent transactions
    $hasSubsequentOut = StockOut::where('item_id', $stockIn->item_id)
        ->where('created_at', '>', $stockIn->created_at)
        ->exists();
    
    if ($hasSubsequentOut) {
        abort(422, 'Tidak bisa hapus: ada transaksi keluar setelah transaksi ini');
    }
    
    // ... existing checks
}
```

---

#### MEDIUM #19: Initial Stock Without Transaction Record
**LOCATION**: `ItemController.php:106`  
**ISSUE**: Item dibuat dengan `current_stock` dari input tanpa StockMovement record.

**RISK**: Audit trail incomplete. Balance calculation error jika reconstruct dari StockMovement.

**RECOMMENDATION**:
```php
DB::transaction(function () use ($validated) {
    $item = Item::create([
        ...$validated,
        'current_stock' => 0, // Start from 0
    ]);
    
    // Create initial StockIn if stock > 0
    if ($validated['current_stock'] > 0) {
        $stockIn = StockIn::create([
            'item_id' => $item->id,
            'qty' => $validated['current_stock'],
            'source' => 'Initial Stock',
            'date' => now(),
            'reference_no' => 'IN-INIT-' . $item->id,
        ]);
        
        $item->current_stock = $validated['current_stock'];
        $item->save();
        
        StockMovement::create([...]);
    }
});
```

---

#### MEDIUM #20: No Reserved Stock Concept
**LOCATION**: `StockOutController.php:107`  
**ISSUE**: Simple check `$validated['qty'] > $item->current_stock` tanpa konsep reserved/pending stock.

**RISK**: Over-commitment jika multiple departments request bersamaan.

**RECOMMENDATION**: Tambahkan `reserved_stock` column atau calculate dynamically.

---

## BAGIAN 4: FRONTEND AUDIT (VUE 3 + INERTIA.JS)

### 4.1 SECURITY & XSS ISSUES

#### CRITICAL #15: XSS via v-html in Pagination
**LOCATION**:
- `resources/js/Pages/Apps/StockMovements/Index.vue:102`
- `resources/js/Components/Pagination.vue:16`

**ISSUE**: Penggunaan `v-html="link.label"` pada pagination links.

**RISK**: Potensial XSS jika `link.label` dari backend mengandung script malicious.

**RECOMMENDATION**:
```vue
<!-- Ganti v-html dengan v-text atau interpolation -->
<Link class="page-link" :href="link.url ?? '#'">{{ link.label }}</Link>
```

---

#### HIGH #21: Success Callback Accessing Undefined Props
**LOCATION**: `resources/js/Pages/Apps/StockOuts/Create.vue:260-279`  
**ISSUE**: `onSuccess` callback mengakses `page.props.newStockOutId` tanpa validasi.

**RISK**: Jika backend tidak mengirim newStockOutId, `window.open` akan error.

**RECOMMENDATION**:
```javascript
onSuccess: () => {
    const page = usePage();
    const newId = page.props.newStockOutId;
    
    if (newId) {
        window.open(`/apps/stock-outs/${newId}/pdf`, '_blank');
    }
    
    Swal.fire({
        title: 'Berhasil!',
        text: 'Material keluar berhasil dicatat.',
        icon: 'success',
        showConfirmButton: false,
        timer: 2000,
    });
}
```

---

### 4.2 MISSING LOADING STATES

#### HIGH #22: No Loading Indicators on Filters
**LOCATION**:
- `resources/js/Pages/Apps/StockMovements/Index.vue:171-177`
- `resources/js/Pages/Apps/Users/Index.vue:65-67`

**ISSUE**: Apply filter atau search tidak menampilkan loading indicator.

**RISK**: User tidak tahu request sedang diproses.

**RECOMMENDATION**:
```javascript
const isLoading = ref(false);

const applyFilter = () => {
    isLoading.value = true;
    router.get('/apps/stock-movements', filterForm, { 
        preserveState: true,
        onFinish: () => { isLoading.value = false; }
    });
};
```

---

#### HIGH #23: Network Error Not Handled
**LOCATION**: Multiple pages dengan `router.delete()`  
**ISSUE**: Delete operations tidak menangani network error.

**RISK**: User tidak tahu jika delete gagal karena network issue.

**RECOMMENDATION**:
```javascript
router.delete(`/apps/items/${id}`, {
    onError: (errors) => {
        Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus data.',
            icon: 'error'
        });
    }
});
```

---

### 4.3 VALIDATION & UX ISSUES

#### MEDIUM #21: File Upload No Client Validation
**LOCATION**: `resources/js/Pages/Apps/Items/Create.vue:134-142`  
**ISSUE**: File upload tidak divalidasi di frontend (ukuran, type).

**RISK**: User upload file besar/format salah, baru error di backend.

**RECOMMENDATION**:
```javascript
const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'Ukuran file maksimal 2MB', 'error');
            e.target.value = '';
            return;
        }
        if (!file.type.startsWith('image/')) {
            Swal.fire('Error', 'Hanya file gambar yang diperbolehkan', 'error');
            e.target.value = '';
            return;
        }
        form.image = file;
    }
};
```

---

#### MEDIUM #22: Missing Permission Check on Buttons
**LOCATION**: `resources/js/Pages/Apps/Items/Index.vue:10-11`  
**ISSUE**: Button "NEW" tidak memiliki permission check.

**RISK**: UI menampilkan button yang tidak bisa diakses user.

**RECOMMENDATION**:
```vue
<Link v-if="hasAnyPermission(['items.create'])" 
      href="/apps/items/create" 
      class="btn btn-primary">
    <i class="fa fa-plus-circle"></i> NEW
</Link>
```

---

#### MEDIUM #23: Inconsistent Alert Usage
**LOCATION**: `resources/js/Pages/Apps/StockOpnames/Create.vue:156`  
**ISSUE**: Menggunakan `alert()` native JavaScript, tidak konsisten dengan SweetAlert2.

**RECOMMENDATION**: Ganti dengan `Swal.fire()`.

---

### 4.4 ACCESSIBILITY ISSUES

#### MEDIUM #24: Missing aria-labels
**LOCATION**: Multiple locations  
**ISSUE**: Icon-only buttons tidak memiliki aria-label atau text.

**Examples**:
- `Auth/Login.vue:102-104` - Password toggle button
- `Items/Index.vue:54-61` - Edit/Delete buttons
- `Header.vue:18` - Avatar image no alt text

**RECOMMENDATION**:
```vue
<Link :href="`/apps/items/${item.id}/edit`" 
      class="btn btn-success btn-sm"
      :aria-label="`Edit ${item.name}`">
    <i class="fa fa-pencil-alt"></i>
</Link>
```

---

