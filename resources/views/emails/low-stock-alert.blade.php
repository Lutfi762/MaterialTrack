<x-mail::message>
# Alert Stok Menipis

Berikut material yang stoknya di bawah batas minimum:

@if(count($data['low_stock_items']) > 0)
## Material Stok Menipis
<x-mail::table>
| Kode | Nama | Stok Saat Ini | Min. Stok | Status |
|------|------|:------------:|:---------:|:------:|
@foreach($data['low_stock_items'] as $item)
| {{ $item->code }} | {{ $item->name }} | {{ $item->current_stock }} {{ $item->unit }} | {{ $item->min_stock }} | Menipis |
@endforeach
</x-mail::table>
@endif

@if(count($data['out_of_stock_items']) > 0)
## Material Stok Habis
<x-mail::table>
| Kode | Nama | Stok | Status |
|------|------|:----:|:------:|
@foreach($data['out_of_stock_items'] as $item)
| {{ $item->code }} | {{ $item->name }} | 0 {{ $item->unit }} | Habis |
@endforeach
</x-mail::table>
@endif

<x-mail::button url="{{ url('/apps/items') }}">
Lihat Material
</x-mail::button>

<small>Laporan ini dikirim otomatis oleh MaterialTrack.</small>
</x-mail::message>
