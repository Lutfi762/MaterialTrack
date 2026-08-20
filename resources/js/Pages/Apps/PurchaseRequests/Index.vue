<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Permintaan Pembelian (Purchase Requests)</h2>
        <Link :href="route('apps.purchase-requests.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat PR Baru
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. PR</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioritas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="pr in purchaseRequests.data" :key="pr.id">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold">{{ pr.pr_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ pr.department?.name || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ pr.requester?.name || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                  <span :class="{'text-red-600 font-bold': pr.priority === 'urgent', 'text-orange-500': pr.priority === 'high'}">{{ pr.priority }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                    :class="{
                      'bg-yellow-100 text-yellow-800': pr.status === 'pending',
                      'bg-green-100 text-green-800': pr.status === 'approved',
                      'bg-red-100 text-red-800': pr.status === 'rejected'
                    }">
                    {{ pr.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.purchase-requests.show', pr.id)" class="text-indigo-600 hover:text-indigo-900">Detail</Link>
                </td>
              </tr>
              <tr v-if="purchaseRequests.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada dokumen PR.</td>
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
  props: { purchaseRequests: Object, filters: Object },
};
</script>
