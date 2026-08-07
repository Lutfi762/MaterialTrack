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

