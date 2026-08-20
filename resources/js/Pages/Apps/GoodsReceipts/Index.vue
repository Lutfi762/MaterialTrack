<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penerimaan Barang (Goods Receipts)</h2>
        <Link :href="route('apps.goods-receipts.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Penerimaan Baru
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. GR</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. PO</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemasok</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Terima</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="gr in goodsReceipts.data" :key="gr.id">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold">{{ gr.gr_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ gr.purchase_order?.po_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ gr.purchase_order?.supplier?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ gr.receipt_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ gr.status }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.goods-receipts.show', gr.id)" class="text-indigo-600 hover:text-indigo-900">Detail</Link>
                </td>
              </tr>
              <tr v-if="goodsReceipts.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada penerimaan barang.</td>
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
  props: { goodsReceipts: Object },
};
</script>
