# AUDIT MODUL ERP BARU (BELUM DI-COMMIT) - MATERIALTRACK
**Tanggal**: 19 Agustus 2026
**Scope**: Modul baru di branch `claude-full-audit` yang belum di-commit:
- Procurement: Customer, PurchaseRequest, PurchaseOrder, GoodsReceipt, PurchaseInvoice
- Sales: Quotation, SalesOrder, Delivery, SalesInvoice
- Finance: ChartOfAccount, JournalEntry, CompanyProfile
- HRM: Employee, Attendance, Leave, Payroll
- ApprovalWorkflow (config saja)
**Metode**: Baca seluruh controller (17), model (36), migration (11), seeder, routes, Vue pages; syntax check (`php -l`) semua file PHP baru = LULUS; `route:list`, `migrate:status` (semua migration sudah Ran), test smoke (2 ExampleTest PASS).

---

## RINGKASAN EKSEKUTIF

Modul baru mengulang mayoritas kesalahan audit lama (AUDIT_REPORT.md) dan menambah bug
runtime baru yang membuat beberapa fitur CRASH total. Route permission middleware sudah
dipasang konsisten (perbaikan dari audit sebelumnya) — ini bagian yang benar.

| Severity | Jumlah | Ringkasan |
|----------|--------|-----------|
| CRITICAL | 8 | Employee/Payroll/Attendance crash (mismatch schema vs controller), over-receive/over-ship stok, double-invoice per PO/SO, cascadeDelete menghapus history, race nomor dokumen |
| HIGH | 7 | Approve tanpa status guard, item/PO mismatch tak divalidasi, permission seeder tidak lengkap, route resource tanpa method controller, payroll create kehilangan data |
| MEDIUM | 6 | ApprovalWorkflow tidak terhubung engine, frontend tanpa permission check & loading, select field non-existent, XSS aman (tidak ada v-html) |
| ENV | 2 | composer.lock tambah octane tapi autoloader vendor stale (app boot crash), opcache CLI hanging |

---

## STATUS PERBAIKAN (update 20 Agustus 2026)

Semua fix prioritas 1–3 + ENV selesai & terverifikasi. Migration `2026_08_20_000012_fix_audit_issues`
telah dijalankan (efek DDL sudah terpasang manual sebelumnya; direkam di tabel `migrations`, batch 6 →
`migrate:status` semua **Ran**).

| Item | Status | Bukti |
|---|---|---|
| CRITICAL #1 Employee crash | ✅ | Controller pakai `employee_id/position/hire_date/employment_status`, enum `intern`; `php -l` + test suite lulus |
| CRITICAL #2 Attendance `on_leave` | ✅ | Controller pakai `leave`; enum DB `['present','late','absent','sick','leave','holiday','on_leave']` |
| CRITICAL #3 Payroll crash/data hilang | ✅ | Migration + filter guard status `draft→approved→paid`; enum DB memuat `approved`,`paid`; fillable `pay_date/total_amount/notes` |
| CRITICAL #4 GR over-receive | ✅ | `lockForUpdate` PO + cek kepemilikan baris/kesesuaian item/sisa qty; throw `ValidationException` |
| CRITICAL #5 Delivery over-ship | ✅ | `lockForUpdate` SO + item lock + cek sisa qty + cek stok (kini `ValidationException`, bukan `\Exception` → 500) |
| CRITICAL #6 Double-invoice | ✅ | Guard 1 PO = 1 faktur, 1 SO = 1 faktur + **unique index** `purchase_invoices.purchase_order_id`, `sales_invoices.sales_order_id` |
| CRITICAL #7 cascade delete | ✅ | Migration `restrict` seluruh FK transaksi/master; `delete_rule` RESTRICT terverifikasi via `information_schema` |
| CRITICAL #8 race nomor dokumen | ✅ | `DocumentNumber::next()` pakai `lockForUpdate()` pada `id` terakhir (harus dipanggil DI DALAM transaksi) |
| HIGH #9 status guard | ✅ | PR/PO/SO approve, Leave approve/reject, Payroll approve/process semua guard status |
| HIGH #10 seeder permission | ✅ | Semua nama permission route middleware ada di `PermissionsTableSeeder` (verify manual) |
| HIGH #11 route resource | ✅ | Resource ERB pakai `->except(...)`; `AttendanceController`/`ApprovalWorkflowController` tanpa route tak terpakai |
| HIGH #12 overlap periode | ✅ | `PayrollController::store()` cek overlap `start_date/end_date` |
| HIGH #13 quotation SO ganda | ✅ | Unique `sales_orders.quotation_id` |
| HIGH #14 CompanyProfile | ✅ | `lockForUpdate()` single-row dalam transaksi |
| MEDIUM #2 baris jurnal kosong | ✅ | Tolak baris debit=kredit=0 |
| MEDIUM #3 unique attendance | ✅ | Unique `attendances(employee_id,date)` + `updateOrCreate` |
| MEDIUM #4 rejection_reason & jatah cuti | ✅ | Kolom+fillable + guard `total_days <= default_days`; **+fix bug `days_allowed` → `default_days` (halaman Create cuti sempat CRASH)** |
| ENV #1 autoloader octane | ✅ | `vendor/laravel/octane` terpasang, autoloader di-regenerate (`autoload_psr4.php` fresh); `artisan --version` jalan |
| Verifikasi | ✅ | `php -l` seluruh controller/model/migration/seeder/routes bersih; `vendor/bin/phpunit` **OK (5 tests, 11 assertions)**; `npm run build` sukses; enum/unique/FK diverifikasi langsung ke DB |

**Catatan pengerjaan**: `php artisan test` hang di mesin ini karena subprocess phpunit tak ikut flag
`-d opcache.enable_cli=0` → jalankan `php -d opcache.enable_cli=0 vendor\bin\phpunit` langsung.

**Belum dikerjakan (sprint berikut, prioritas #4 di bawah)**: HIGH #15 (permission check + loading
state di frontend), MEDIUM #1 (wire ApprovalWorkflow ke approve PR/PO/SO/Leave), MEDIUM #5 (index
non-unique kolom filter), MEDIUM #6 (loading state umum).

## RE-AUDIT MENYELURUH (20 Agustus 2026, sweep otomatis)

Script audit `/_sweep.php` (gitignored): lint semua PHP, `route:list` action→controller method,
`Inertia::render`→file Vue, model fillable vs kolom DB, migration→tabel, permission route vs DB,
`route()` Vue vs rute terdaftar, kolom mencurigakan, FK yatim, props controller vs `defineProps`.

| Temuan | Aksi | Status |
|---|---|---|
| `stock_ins.po_number/supplier_id`, `stock_movements.hash`, `stock_opnames.status/verified_by/verified_at`, `departments.status` **tidak ada di DB** walau migration `2026_08_18_100010`/`2026_08_19_100001`/`100003` tercatat Ran (DDL tak pernah terpasang — file diedit setelah running) → StockIn `with('supplier')` & `StockMovement::create` (isi `hash`) **SQL error** | Migration baru `2026_08_20_000013_add_missing_legacy_inventory_columns` (guarded `hasColumn`), backfill rantai hash & status opname lama, trigger append-only deny UPDATE/DELETE; dijalankan | ✅ |
| Trigger `stock_movements_deny_update/delete` belum ada | Dibuat di 000013; verifikasi: INSERT dummy ber-hash → UPDATE & DELETE **ditolak** (SQLSTATE 45000), rollback bersih | ✅ |
| Permission route baru belum ada di DB (`notifications.index`, `financial_reports.index`, `cash_bank.index/create`, `sales_returns.index/create/approve`) | `PermissionsTableSeeder` (sudah memuatnya) dijalankan → 95 permission | ✅ |
| Migration `2026_08_21_000001/000002` (cash_bank_transactions, sales_returns, sales_return_items) pending | `php artisan migrate` → Ran; model/controller fillable ↔ kolom cocok | ✅ |
| `FinancialReports/Index.vue` v-model di prop `from/to` → **build gagal** | ganti `:value` + `@change` | ✅ |
| `route:list` gagal-semu utk controller Fortify (short name tanpa namespace) | sweep resolve via `Laravel\Fortify\Http\Controllers\` | tooling |
| Verifikasi akhir | Sweep **SEMUA OK** (sisa 1 WIP: `SalesReturns/Show.vue` belum ditulis); `phpunit` **5/5 (11 assertions)**; `npm run build` **EXIT 0** | ✅ |

**Catatan**: Modul Cash Bank / Sales Returns / Financial Reports / Notifications = WIP belum commit
(controller+model+migration+seeder sudah ada; halaman Vue sedang dilengkapi). Tabel & permission
sudah siap di DB; `Apps/SalesReturns/Show.vue` sudah dibuat.

## FIX 22 Agustus 2026
- Bug test `test_cash_bank_crud_dan_jurnal_otomatis`: payload `description` 500 char > kolom DB
  `string()` (255) & rule `max:255` → validasi gagal, redirect-back (masih lolos `assertRedirect()`),
  insert tidak jalan → assertion "table is empty". Bug di test, bukan app (controller/migration
  sudah benar). Fix: kurangi payload test jadi 200 char. Verifikasi: `vendor\bin\phpunit`
  **23/23 tests, 142 assertions, OK**.

---

## BAGIAN 1: CRITICAL

### CRITICAL #1: Employee create/update CRASH (schema vs controller vs frontend mismatch)
**Lokasi**:
- `database/migrations/2026_08_20_000010_create_hrm_tables.php:12-31` — kolom: `employee_id` (NOT NULL unique), `position`, `hire_date` (NOT NULL), `employment_status` enum `[permanent, contract, probation, intern, resigned]`
- `app/Models/Employee.php:11-16` — fillable: `employee_id, user_id, department_id, ..., position, hire_date, employment_status, ...`
- `app/Http/Controllers/Apps/EmployeeController.php:41-54` — validasi & create pakai: `employee_code`, `designation`, `join_date`, `employment_status`, `status`
- `resources/js/Pages/Apps/Employees/Create.vue:91-102` — form: `employee_code, designation, join_date, employment_status`

**Dampak**: `Employee::create($validated)` hanya mengisi field fillable yang cocok.
`employee_code`/`designation`/`join_date` ikut validasi tapi DI-DROP (tidak fillable).
`employee_id` & `hire_date` (NOT NULL, tanpa default) TIDAK terisi → **SQL error di MySQL**.
`employment_status='internship'` tidak ada di enum (`intern`) → **SQL error**. Flow tambah/edit karyawan 100% mati.

**Perbaikan**: samakan satu sumber kebenaran. Rekomendasi: ubah controller + frontend ke
kolom schema (`employee_id`, `position`, `hire_date`, `employment_status` enum `intern`) ATAU
tambah migration ubah kolom ke `employee_code`/`designation`/`join_date` + tambah `status` column.
Jangan serasi-kan lewat fillable model.
### CRITICAL #2: Attendance status `on_leave` tidak ada di enum DB
**Lokasi**: `AttendanceController.php:41` (`in:present,late,absent,on_leave`) vs
`2026_08_20_000010...hrm`, line 40 enum `['present','late','absent','sick','leave','holiday']`.
**Dampak**: Pilih "Cuti / Izin" → `'on_leave'` → **SQL error** (MySQL strict enum).
**Perbaikan**: ganti nilai jadi `leave` (back+front) atau tambah `on_leave` via migration.

### CRITICAL #3: Payroll approve/process CRASH + data hilang saat create
**Lokasi**: `PayrollController.php:38-66`, `79-89`; `2026_08_20_000010...hrm` line 68-75.
- Migration `payroll_periods` cuma punya `name, start_date, end_date, status` dengan enum
  `['draft','processing','completed','closed']`.
- Controller `store()` tulis `pay_date`, `notes` (kolom TIDAK ADA → di-drop diam-diam),
  lalu `update(['total_amount' => ...])` (kolom tidak ada, model tidak fillable → **total gaji selamanya 0**).
- `approve()` set `status='approved'`, `process()` set `status='paid'` — **keduanya tidak ada di enum** → **SQL error**.
**Perbaikan**: tambah migration (`pay_date`, `total_amount`, `notes`), samakan enum status
(`draft|approved|processing|paid`), masukkan kolom ke fillable.

### CRITICAL #4: GoodsReceipt over-receive & item/PO mismatch tanpa validasi server
**Lokasi**: `GoodsReceiptController.php:42-106`.
- Validasi `qty_received min:0` saja, TANPA cek `qty_received <= qty - qty_received_sebelumnya`.
  Frontend membatasi (`Create.vue:52 :max`), tapi request bisa dimanipulasi → **over-receive tanpa batas**,
  stok bertambah melebihi pesanan, uang dibayar ke supplier atas qty penuh.
- `purchase_order_item_id` divalidasi `exists:purchase_order_items,id` TAPI tidak dicek milik
  `purchase_order_id` yang dikirim; `item_id` juga tidak dicek cocok dengan PO item → stok bisa
  masuk ke barang salah.
- Tidak ada lock (`Item::where('id',...)->lockForUpdate()` belum dipakai) → race saat 2 GR
  bersamaan untuk PO sama = **double receive**.
**Perbaikan**: validasi `qty_received` terhadap sisa; cek `purchase_order_item` milik PO dan
`item_id` konsisten; wrap item stock update dengan `lockForUpdate()`; RESTRICT duplicate GR per PO item.

### CRITICAL #5: Delivery over-ship sama persis dengan #4
**Lokasi**: `DeliveryController.php:44-112`.
- `qty_shipped min:1` tanpa batas sisa `qty - qty_delivered` → over-ship (stok berkurang lebih
  dari pesanan), multiple delivery bisa kirim item sama berkali-kali.
- `sales_order_item_id`/`item_id` tidak divalidasi kecocokan dengan SO.
- Cek stok `if ($item->current_stock < qty)` tanpa `lockForUpdate()` → race → **stok bisa minus**.
**Perbaikan**: sama seperti #4, plus constraint `qty_shipped <= qty - qty_delivered`.

### CRITICAL #6: Double-invoice per PO / per SO (duplikasi billing)
**Lokasi**: `PurchaseInvoiceController.php:48-77`, `SalesInvoiceController.php:47-75`.
Tidak ada cek invoice sudah ada untuk PO/SO tersebut dan kolom `purchase_order_id` /
`sales_order_id` tidak unique. PO/SO `delivered/received` bisa di-invoice berkali-kali →
**pembayaran ganda**. Juga besar invoice diambil dari SUBTOTAL PO/SO (qty penuh), padahal
penerimaan/penjualan bisa parsial → **invoice tidak sesuai barang nyata**.
**Perbaikan**: `unique` pada `purchase_invoices.purchase_order_id` & `sales_invoices.sales_order_id` +
guard `already_invoiced`; hitung dari qty yang sudah diterima/dikirim.

### CRITICAL #7: cascadeOnDelete menghapus history keuangan/operasional
**Lokasi**: migration 000003-000010, hampir semua FK pakai `->constrained()->cascadeOnDelete()`:
- Hapus `users` → menghapus semua `purchase_requests`, `purchase_orders`, `goods_receipts`,
  `purchase_invoices`, `quotations`, `sales_orders`, `deliveries`, `sales_invoices`, `journal_entries`
  yang `created_by`/`requested_by`/`received_by`/`dispatched_by` user tsb. **Hapus 1 user = history hilang.**
- Hapus `customers` → menghapus quotations/orders/deliveries/invoices.
- Hapus `suppliers` → menghapus POs & invoices.
- Hapus `items` → menghapus semua PR/PO/GR/invoice line history.
**Perbaikan**: FK ke user pemicu transaksi pakai `restrictOnDelete()` (atau `nullOnDelete` utk
---

## BAGIAN 2: HIGH

### HIGH #9: Approve/reject tanpa status guard
`PurchaseRequestController::approve/reject`, `PurchaseOrderController::approve`,
`SalesOrderController::approve`, `LeaveController::approve/reject` — bisa approve dokumen yang
sudah `rejected/cancelled/paid/delivered`, reject yang sudah approved, dsb. Tambah validasi status
transisi (state machine) sebelum update.

### HIGH #10: Permission seeder tidak lengkap vs route middleware
**Lokasi**: `PermissionsTableSeeder.php:55-76` vs `routes/web.php:353-412`.
Route pakai `goods_receipts.edit|delete`, `purchase_invoices.edit|delete`,
`quotations.edit|delete`, `sales_orders.edit|delete`, `deliveries.edit|delete`,
`sales_invoices.edit|delete`, `journal_entries.edit|delete`, `attendances.edit|delete`,
`payroll.edit|delete`, `approval_workflows.edit|delete` — **semua permission ini TIDAK di-seed**.
Karena `admin` di-`syncPermissions(Permission::all())` dari yang ada, admin pun kena 403.
**Butuh**: seeder tambah permission tsb (atau hapus guard tsb dari routes).

### HIGH #11: Route resource mendaftarkan method yang tidak ada di controller
Resource `Route::resource(...)` register `show/edit/destroy`; banyak controller tak punya method-nya:
- `destroy` hilang: PurchaseRequest, GoodsReceipt, PurchaseInvoice, Quotation, SalesOrder,
  Delivery, SalesInvoice, JournalEntry, Attendance, Payroll, ApprovalWorkflow
- `show` hilang: Employee (route `apps.employees.show` ada → 500), Attendance, ApprovalWorkflow
Akses URL tsb → PHP Error 500. Tambah method atau `->except(['destroy', ...])`.

### HIGH #12: Payroll period meng-generate untuk semua karyawan aktif tanpa cek periode overlap
`PayrollController::store()` tidak cek apakah sudah ada periode yang tumpang-tindih tanggal →
gaji ganda period. Tambah guard overlap (start_date/end_date).

### HIGH #13: Quotation-ish data integrity kecil
- `Quotation` dibuat `'draft'` tanpa mekanisme `sent/accepted`; SO boleh dibuat dari quotation
  yang sama berkali-kali → SO ganda dari 1 quotation (opsional unique `sales_orders.quotation_id`).
- `quotation_id` validated `exists` tapi tidak dicek status/milik customer yang sama dengan SO.

### HIGH #14: CompanyProfile last-write-wins tanpa kunci & tanpa authorisasi level
`CompanyProfileController` — single-row pattern OK, tapi tidak ada jaminan single-row (dua
request bersamaan → 2 baris dan `first()` ambigu). Tambah constraint single-row (id=1) atau
`CompanyProfile::firstOrFail`. (Route permission sudah ada — OK.)

### HIGH #15: Frontend gak ada permission check & error handling
Frontend tidak pakai `hasAnyPermission` sama sekali (semua page baru) → tombol "Create"/"Delete"
muncul ke semua user; akses route-nya 403. Minimal bungkus dengan `hasAnyPermission([...])`.
Juga tidak ada `onError` di semua `form.post` → error validasi tampil mentah.

---

## BAGIAN 3: MEDIUM & LAINNYA

### MEDIUM
1. **ApprovalWorkflow = config-only**: `approval_workflows/approval_steps/approval_requests` tidak
   pernah di-wire ke PR/PO/SO/Leave approve. Tidak ada aksi approve request, engine resolve approver,
   atau transisi step. Fitur non-fungsional (scaffold).
2. **JournalEntry**: line dengan debit=0 & credit=0 (atau keduanya >0) lolos validasi — hanya cek
   total debit=credit. Tambah rule: minimal 1 line nonzero per sisi, atau reject kedua-duanya 0.
3. **Attendance updateOrCreate** tanpa unique index `(employee_id, date)` → dupes saat concurrent.
4. **Leaves**: `rejection_reason` tidak ada di schema/fillable → alasan ditolak silent drop.
   Juga total_days > jatah `leave_types.default_days` tidak dicek.
5. **Index**: tidak ada index non-unique pada kolom filter umum (`status`); `number` kolom sudah

---

## BAGIAN 5: LAPISAN KECEPATAN & KEAMANAN (ditambahkan 2026-08-20)

### Kecepatan
1. **OPcache** — belum aktif di php.ini Laragon (`;zend_extension=opcache` dikomentari).
   Blok siap-pakai: `deploy/opcache-php.ini` (tempel ke php.ini). Verifikasi `php --ri opcache`.
2. **Config/route/view/event cache** — `deploy/optimize.ps1` (ditolak bila `APP_ENV=local`).
   Closure route `/` dan `/api/user/locale` sudah dikonversi ke `WelcomeController` &
   `UserLocaleController` supaya `route:cache` bisa jalan.
3. **Browser cache** — `public/.htaccess`: gzip (mod_deflate) + `Expires 1 tahun, immutable`
   untuk aset build Vite (ber-hash). `deploy/nginx-materialtrack.conf` versi nginx (gzip,
   http2, caching, blokir file sensitif).
4. **Vendor chunk split** — `vite.config.js` `build.rolldownOptions.output.advancedChunks`:
   `vue-vendor` + `charts-vendor` dipisah dari bundle app → tidak ke-download ulang saat
   source berubah.
5. **Indeks DB baru** — migration `2026_08_20_000014_add_performance_indexes`:
   `stock_movements(item_id,type,created_at)`, `stock_ins(supplier_id,date)`,
   `activity_logs(user_id,created_at)`, `journal_entries(status,entry_date)`,
   `journal_entry_lines(chart_of_account_id)`, `cash_bank_transactions(transaction_date,type)`.
6. **Dashboard & item list sudah di-cache** (`CacheService`).

### Keamanan
1. **Security headers** (`app/Http/Middleware/SecurityHeaders.php`, global):
   CSP (self + CDN allowlist), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
   `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`,
   `Strict-Transport-Security` (prod), dan `X-Powered-By` dihapus.
2. **HTTPS paksa** (`app/Http/Middleware/HttpsRedirect.php`) — aktif bila `APP_ENV=production`
   DAN `APP_URL=https://…` (skipped di unit test / CLI). `TRUSTED_PROXIES` di `.env` dipakai
   untuk proxy (nginx/Cloudflare).
3. **Ziggy eksternal** — `@routes` inline script dihapus dari `app.blade.php`; routes di-generate
   ke `resources/js/ziggy.js` (`php artisan ziggy:generate`) + `ziggy-js` npm dep. **Wajib
   regenerate ziggy.js setiap tambah/ubah route** (diperiksa otomatis oleh `_sweep.php` section C2).
4. **Password policy** — `Password::defaults(min 8 + huruf + angka)` di `AppServiceProvider`
   (register/reset/update password via Fortify).
5. **Rate limit login** sudah ada (Fortify 5/menit per email+IP); PDF/export sudah
   `throttle:10,1`.
6. **`.htaccess` lapisan ganda** — `public/.htaccess`: gzip + cache + blokir `.env/log/sql`;
   `.htaccess` root: `Options -Indexes` + deny file sensitif.
7. **.env produksi** — blok di `.env.example`: `APP_DEBUG=false`, `LOG_LEVEL=warning`,
   `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES`.

### Catatan deployment
- Set `APP_URL=https://domain`, `APP_ENV=production`, `TRUSTED_PROXIES`, aktifkan OPcache.
- Jalankan `deploy/optimize.ps1` setelah setiap rilis.
- Arahkan vhost ke folder `public` (bukan root) — `.htaccess` root hanya pertahanan ekstra.

   unique (bagus). `pr_number/po_number/...` LIKE search `%..%` → full scan (konsisten dgn pola lama).
6. **Frontend**: tidak ada loading state `form.processing` di mayoritas halaman, tidak ada
   `v-html` (XSS aman), tidak ada client-side file validation.

### ENV / Build (penting!)
1. **vendor autoloader stale**: `composer.json`/`composer.lock` menambah `laravel/octane` tapi
   `vendor/composer/autoload_*` belum regenerate → `Laravel\Octane\OctaneServiceProvider` not found,
   app boot crash (`php artisan test` error). Sudah aku perbaiki manual di vendor (tidak di-commit);
   fix beneran: `composer install`/`dump-autoload`.
2. **PHP CLI hang**: `php` (default ini) hang >30s di mesin ini karena opcache CLI; semua perintah
   artisan perlu `-d opcache.enable_cli=0`.
3. `config/database.php` default `sqlite` → `mysql`, `queue`/`session` → `redis` — kalau Redis mati,
   session/queue mati total.

---

## BAGIAN 4: YANG SUDAH BAIK
- Routes semua modul baru punya `middleware('permission:...')` — konsisten.
- Observer StockIn/StockOut hanya log `StockMovement`, TIDAK mengubah stok → GR/Delivery tidak
  double-count (beda dgn laporan lama yang benar).
- GR/Delivery bungkus `DB::transaction`.
- Semua create-form pakai `required/string/numeric` + `exists` validation.
- Tidak ada `v-html`, tidak ada XSS di page baru.
- Policies Category/Department/Location/StockIn/StockOut terpasang via auto-discovery.

## PRIORITAS PERBAIKAN
1. Fix #1, #2, #3 (crash) — blokir total.
2. Fix #4, #5, #6, #7 (integritas stok & pembayaran) — sebelum dipakai produksi.
3. Fix #8, #9, #10, #11, #12 — wajib sebelum release.
4. #13-#15 + MEDIUM — sprint berikutnya.
5. Env: jalankan `composer install` untuk regenerasi autoloader octane.
optional). Master data (customer/supplier/item/department) juga `restrict`; tambah soft deletes
atau guard di controller `destroy` (pola `CategoryController` yang sudah dibenahi).

### CRITICAL #8: Nomor dokumen race + tidak unik antar-transaksi
**Lokasi**: semua store() pakai `[Table]::count() + 1` → `'PR-' . date('Ym') . '-' . str_pad(...)`.
- Race condition di concurrent request → **duplikat nomor** (kolom `unique` → 500).
- Setelah hapus record, count menurun → nomor dipakai ulang / bentrok.
**Perbaikan**: DB sequence / `str_pad($id)` setelah insert / `DB::select('SELECT ... FOR UPDATE')` /
sequence table. (Ini mengulang temuan lama #11 di AUDIT_REPORT.md.)