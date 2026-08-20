<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Faktur Pembelian: {{ invoice.invoice_number }}</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><span class="text-xs text-gray-500 block">Pemasok:</span> <strong>{{ invoice.supplier?.name }}</strong></div>
          <div><span class="text-xs text-gray-500 block">No PO Terkait:</span> <strong>{{ invoice.purchase_order?.po_number }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Tanggal Faktur:</span> <strong>{{ invoice.invoice_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Jatuh Tempo:</span> <strong>{{ invoice.due_date }}</strong></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Rincian Faktur</h3>
          <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in invoice.items" :key="item.id">
                <td class="px-6 py-4 text-sm">{{ item.item?.name }}</td>
                <td class="px-6 py-4 text-sm font-semibold">{{ item.qty }}</td>
                <td class="px-6 py-4 text-sm font-mono">Rp {{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 text-sm text-right font-mono">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</td>
              </tr>
            </tbody>
          </table>

          <div class="border-t pt-4 flex flex-col items-end space-y-1 text-sm font-medium">
            <div>Subtotal: <span class="font-mono">Rp {{ Number(invoice.subtotal).toLocaleString('id-ID') }}</span></div>
            <div>PPN (11%): <span class="font-mono">Rp {{ Number(invoice.tax_amount).toLocaleString('id-ID') }}</span></div>
            <div class="text-base font-bold text-indigo-700">Total Tagihan: <span class="font-mono">Rp {{ Number(invoice.total).toLocaleString('id-ID') }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/App.vue';

export default {
  components: { AppLayout },
  props: { invoice: Object },
};
</script>
