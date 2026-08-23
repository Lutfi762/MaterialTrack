# Audit MaterialTrack — Konsolidasi

Menggabungkan dua laporan audit yang sebelumnya terpisah di root project:
- `AUDIT_REPORT.md` (7 Agustus 2026) — audit menyeluruh sistem inti (stock in/out/opname, security, backend, frontend, database).
- `AUDIT_ERP_MODULES.md` (19–22 Agustus 2026) — audit modul ERP baru yang belum di-commit (Procurement, Sales, Finance, HRM, ApprovalWorkflow).

## Status Ringkas

| Laporan | Status per dokumen asli | Cakupan |
|---|---|---|
| Audit sistem inti (7 Agu) | **DRAF — belum ada bukti perbaikan tercatat** di dokumen ini | Stock in/out/opname, items, categories, departments, locations, security & RBAC, frontend lama |
| Audit modul ERP (19–22 Agu) | **Sebagian besar sudah diperbaiki & diverifikasi** (lihat tabel status di bawah) | Procurement, Sales, Finance, HRM, ApprovalWorkflow |

⚠️ Karena kedua audit beda tanggal dan beda scope, **cek ulang manual** apakah temuan di audit sistem inti (7 Agu) masih relevan sebelum menindaklanjuti — mungkin sudah diperbaiki di luar dokumen ini tapi belum diperbarui statusnya.

## Temuan Terbuka Prioritas Tinggi (Sistem Inti — Belum Ada Bukti Fix)

1. **CRITICAL** — Routes tanpa permission middleware (`routes/web.php:40-80`) — bypass RBAC total.
2. **CRITICAL** — IDOR di StockIn/StockOut/Category/Department/Location controllers — user bisa edit/hapus data user/department lain.
3. **CRITICAL** — Observer StockIn/StockOut tidak terdaftar di `AppServiceProvider` → audit trail StockMovement tidak jalan otomatis.
4. **CRITICAL** — Race condition stok (check-then-decrement tanpa `lockForUpdate`) → stok bisa minus.
5. **CRITICAL** — `current_stock` kolom signed integer, tanpa constraint → stok negatif mungkin tersimpan.
6. **CRITICAL** — Race condition nomor referensi (`count()+1`) → duplikat/gagal unique constraint.
7. **CRITICAL** — XSS via `v-html` di `Pagination.vue` & `StockMovements/Index.vue`.
8. **HIGH** — Beberapa transaksi multi-step tidak dibungkus `DB::transaction`.
9. **HIGH** — Field `date` hilang dari `$fillable` di `StockIn`/`StockOut`.

*(Detail lengkap 140 temuan asli ada di lampiran di bawah.)*

## Status Modul ERP Baru (per re-audit 22 Agustus)

Semua temuan CRITICAL (8) dan HIGH (7) sudah diperbaiki & diverifikasi (php -l bersih, phpunit 23/23 tests 142 assertions OK, npm run build EXIT 0). Yang **belum dikerjakan**:
- HIGH #15 — permission check + loading state di frontend modul ERP baru.
- MEDIUM #1 — ApprovalWorkflow belum di-wire ke approve PR/PO/SO/Leave (masih scaffold).
- MEDIUM #5 — index non-unique pada kolom filter.
- MEDIUM #6 — loading state umum.

Modul Cash Bank / Sales Returns / Financial Reports / Notifications masih WIP (tabel & permission sudah siap, halaman Vue sedang dilengkapi; `SalesReturns/Show.vue` masih perlu direview).

## Rekomendasi Urutan Kerja

1. Verifikasi ulang temuan CRITICAL sistem inti (7 Agu) — pastikan mana yang sudah/belum diperbaiki, karena dokumen itu tidak pernah diperbarui statusnya.
2. Selesaikan sisa pekerjaan modul ERP (permission check frontend, wiring ApprovalWorkflow).
3. Rapikan index database & loading state (item MEDIUM di kedua laporan).

---

## Lampiran A — Audit Sistem Inti (7 Agustus 2026, lengkap)

> Isi asli `AUDIT_REPORT.md` — lihat file terpisah di `docs/archive/AUDIT_REPORT_2026-08-07.md` untuk detail penuh (security, backend, business logic, frontend, database — 140 temuan).

## Lampiran B — Audit Modul ERP (19–22 Agustus 2026, lengkap)

> Isi asli `AUDIT_ERP_MODULES.md` — lihat file terpisah di `docs/archive/AUDIT_ERP_MODULES_2026-08-19.md` untuk detail penuh per-item CRITICAL/HIGH/MEDIUM beserta lokasi kode dan rekomendasi fix.
