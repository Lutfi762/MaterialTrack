<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Penggajian: {{ period.name }}</h2>
        <div class="flex gap-2">
          <button v-if="period.status === 'draft'" @click="approve" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 font-semibold">Setujui Payroll</button>
          <button v-if="period.status === 'approved'" @click="processPay" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 font-semibold">Proses Pembayaran (Paid)</button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><span class="text-xs text-gray-500 block">Periode:</span> <strong>{{ period.start_date }} s/d {{ period.end_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Tanggal Bayar:</span> <strong>{{ period.pay_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Status:</span> <strong class="capitalize">{{ period.status }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Total Pengeluaran:</span> <strong class="text-indigo-700 font-mono">Rp {{ Number(period.total_amount || 0).toLocaleString('id-ID') }}</strong></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Rincian Slip Gaji Karyawan</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tunjangan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Potongan</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gaji Bersih (Net)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in period.items" :key="item.id">
                <td class="px-6 py-4 text-sm font-bold">{{ item.employee?.first_name }} {{ item.employee?.last_name }}</td>
                <td class="px-6 py-4 text-sm font-mono">Rp {{ Number(item.basic_salary).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 text-sm font-mono text-green-600">Rp {{ Number(item.allowances).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 text-sm font-mono text-red-600">Rp {{ Number(item.deductions).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 text-sm text-right font-mono font-bold text-indigo-700">Rp {{ Number(item.net_salary).toLocaleString('id-ID') }}</td>
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
  props: { period: Object },
  methods: {
    approve() {
      if (confirm('Setujui penggajian ini?')) {
        this.$inertia.post(route('apps.payroll.approve', this.period.id));
      }
    },
    processPay() {
      if (confirm('Tandai penggajian ini telah dibayarkan?')) {
        this.$inertia.post(route('apps.payroll.process', this.period.id));
      }
    },
  },
};
</script>
