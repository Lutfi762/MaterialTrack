<x-mail::message>
# Laporan Inventory Mingguan

**Periode:** {{ $data['period'] }}

---

## Ringkasan

| Metrik | Jumlah |
|---|---|
| Total Material | {{ $data['total_items'] }} |
| Stok Menipis | {{ $data['low_stock_items'] }} |
| Stok Habis | {{ $data['out_of_stock_items'] }} |
| Transaksi Masuk | {{ $data['stock_in_count'] }} ({{ $data['stock_in_qty'] }} unit) |
| Transaksi Keluar | {{ $data['stock_out_count'] }} ({{ $data['stock_out_qty'] }} unit) |
| Menunggu Approval | {{ $data['pending_approvals'] }} |

---

@if($data['top_out_items']->count() > 0)
## Top 5 Material Keluar

| # | Kode | Nama | Qty Keluar |
|---|---|---|---|
@foreach($data['top_out_items'] as $idx => $item)
| {{ $idx + 1 }} | {{ $item->item?->code ?? '-' }} | {{ $item->item?->name ?? '-' }} | {{ $item->total_qty }} |
@endforeach
@endif

@if($data['low_stock_list']->count() > 0)
## Material Stok Menipis

| Kode | Nama | Stok | Min. Stok |
|---|---|---|---|
@foreach($data['low_stock_list'] as $item)
| {{ $item->code }} | {{ $item->name }} | {{ $item->current_stock }} {{ $item->unit }} | {{ $item->min_stock }} {{ $item->unit }} |
@endforeach
@endif

---

<x-mail::button url="{{ url('/apps/dashboard') }}">
Lihat Dashboard
</x-mail::button>

<small>Laporan ini dikirim otomatis oleh MaterialTrack.</small>
</x-mail::message>
