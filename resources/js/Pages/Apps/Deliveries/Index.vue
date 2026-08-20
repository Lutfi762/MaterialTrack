<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengiriman / Surat Jalan (Deliveries)</h2>
        <Link :href="route('apps.deliveries.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat Surat Jalan
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Surat Jalan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. SO</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Pengiriman</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengemudi / Kendaraan</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="d in deliveries.data" :key="d.id">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold">{{ d.delivery_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ d.sales_order?.so_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ d.sales_order?.customer?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ d.delivery_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ d.driver_name || '-' }} ({{ d.vehicle_number || '-' }})</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.deliveries.show', d.id)" class="text-indigo-600 hover:text-indigo-900">Detail</Link>
                </td>
              </tr>
              <tr v-if="deliveries.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada dokumen Surat Jalan.</td>
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
import { Link } from '@inertiajs/vue3';

export default {
  components: { AppLayout, Link },
  props: { deliveries: Object },
};
</script>
