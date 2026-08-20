<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Surat Jalan: {{ delivery.delivery_number }}</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><span class="text-xs text-gray-500 block">No SO:</span> <strong>{{ delivery.sales_order?.so_number }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Pelanggan:</span> <strong>{{ delivery.sales_order?.customer?.name }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Tanggal Pengiriman:</span> <strong>{{ delivery.delivery_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Pengemudi / Plat:</span> <strong>{{ delivery.driver_name || '-' }} ({{ delivery.vehicle_number || '-' }})</strong></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Item Barang Dikirim</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Dikirim</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in delivery.items" :key="item.id">
                <td class="px-6 py-4 text-sm font-medium">{{ item.item?.name }}</td>
                <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-700">{{ item.qty_shipped }} {{ item.item?.unit }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/App.vue';

export default {
  components: { AppLayout },
  props: { delivery: Object },
};
</script>
