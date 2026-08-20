<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Purchase Order: {{ purchaseOrder.po_number }}</h2>
        <div class="flex gap-2" v-if="purchaseOrder.status === 'pending'">
          <button @click="approve" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 font-semibold">Setujui PO</button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><span class="text-xs text-gray-500 block">Supplier:</span> <strong>{{ purchaseOrder.supplier?.name }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Tanggal PO:</span> <strong>{{ purchaseOrder.order_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Termin Pembayaran:</span> <strong>{{ purchaseOrder.payment_term }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Status:</span> <strong class="capitalize">{{ purchaseOrder.status }}</strong></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Item Barang</h3>
          <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Dipesan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Diterima</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in purchaseOrder.items" :key="item.id">
                <td class="px-6 py-4 text-sm">{{ item.item?.name }} ({{ item.item?.code }})</td>
                <td class="px-6 py-4 text-sm font-semibold">{{ item.qty }}</td>
                <td class="px-6 py-4 text-sm text-green-600 font-semibold">{{ item.qty_received }}</td>
                <td class="px-6 py-4 text-sm font-mono">Rp {{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 text-sm text-right font-mono">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</td>
              </tr>
            </tbody>
          </table>

          <div class="border-t pt-4 flex flex-col items-end space-y-1 text-sm font-medium">
            <div>Subtotal: <span class="font-mono">Rp {{ Number(purchaseOrder.subtotal).toLocaleString('id-ID') }}</span></div>
            <div>PPN (11%): <span class="font-mono">Rp {{ Number(purchaseOrder.tax_amount).toLocaleString('id-ID') }}</span></div>
            <div class="text-base font-bold text-indigo-700">Total: <span class="font-mono">Rp {{ Number(purchaseOrder.total).toLocaleString('id-ID') }}</span></div>
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
  props: { purchaseOrder: Object },
  methods: {
    approve() {
      if (confirm('Setujui PO ini?')) {
        this.$inertia.post(route('apps.purchase-orders.approve', this.purchaseOrder.id));
      }
    },
  },
};
</script>
