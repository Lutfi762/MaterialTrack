<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Faktur Pembelian (Purchase Invoices)</h2>
        <Link :href="route('apps.purchase-invoices.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat Faktur Pembelian
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. PO</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemasok</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="inv in invoices.data" :key="inv.id">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold">{{ inv.invoice_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ inv.purchase_order?.po_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ inv.supplier?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ inv.due_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold">Rp {{ Number(inv.total).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ inv.status }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.purchase-invoices.show', inv.id)" class="text-indigo-600 hover:text-indigo-900">Detail</Link>
                </td>
              </tr>
              <tr v-if="invoices.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada faktur pembelian.</td>
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
  props: { invoices: Object },
};
</script>
