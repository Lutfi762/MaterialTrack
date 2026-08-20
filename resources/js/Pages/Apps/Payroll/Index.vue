<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penggajian Karyawan (Payroll)</h2>
        <Link :href="route('apps.payroll.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat Periode Gaji
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode Gaji</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rentang Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Bayar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Nominal (Rp)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="p in periods.data" :key="p.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ p.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ p.start_date }} s/d {{ p.end_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ p.pay_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold">Rp {{ Number(p.total_amount || 0).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                  <span class="px-2 py-1 text-xs rounded-full font-bold"
                    :class="{
                      'bg-yellow-100 text-yellow-800': p.status === 'draft',
                      'bg-blue-100 text-blue-800': p.status === 'approved',
                      'bg-green-100 text-green-800': p.status === 'paid'
                    }">
                    {{ p.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.payroll.show', p.id)" class="text-indigo-600 hover:text-indigo-900">Detail Slips</Link>
                </td>
              </tr>
              <tr v-if="periods.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada periode gaji.</td>
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
  props: { periods: Object },
};
</script>
