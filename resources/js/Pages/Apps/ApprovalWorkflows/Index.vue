<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Alur Persetujuan (Approval Workflows)</h2>
        <Link :href="route('apps.approval-workflows.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat Workflow Baru
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Master Workflow Active</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Workflow</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modul Target</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batas Nominal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Tahap (Steps)</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="w in workflows" :key="w.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ w.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize font-mono">{{ w.module }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                  Rp {{ Number(w.min_amount || 0).toLocaleString('id-ID') }} - {{ w.max_amount ? 'Rp ' + Number(w.max_amount).toLocaleString('id-ID') : 'Tak terbatas' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ w.steps?.length || 0 }} Tahap</td>
              </tr>
              <tr v-if="workflows.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada workflow approval.</td>
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
  props: { workflows: Array, pendingRequests: Array },
};
</script>
