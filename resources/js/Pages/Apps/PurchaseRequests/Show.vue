<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Purchase Request: {{ purchaseRequest.pr_number }}</h2>
        <div class="flex gap-2">
          <button v-if="purchaseRequest.status === 'pending'" @click="approve" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 font-semibold">Setujui (Approve)</button>
          <button v-if="purchaseRequest.status === 'pending'" @click="reject" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 font-semibold">Tolak</button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><span class="text-xs text-gray-500 block">Departemen:</span> <strong>{{ purchaseRequest.department?.name || '-' }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Pemohon:</span> <strong>{{ purchaseRequest.requester?.name || '-' }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Tanggal Pengajuan:</span> <strong>{{ purchaseRequest.request_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Status:</span> <strong class="capitalize">{{ purchaseRequest.status }}</strong></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Daftar Item</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estimasi Harga</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in purchaseRequest.items" :key="item.id">
                <td class="px-6 py-4 text-sm">{{ item.item?.name }} ({{ item.item?.code }})</td>
                <td class="px-6 py-4 text-sm font-semibold">{{ item.qty }} {{ item.item?.unit }}</td>
                <td class="px-6 py-4 text-sm font-mono">Rp {{ Number(item.estimated_price).toLocaleString('id-ID') }}</td>
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
  props: { purchaseRequest: Object },
  methods: {
    approve() {
      if (confirm('Setujui PR ini?')) {
        this.$inertia.post(route('apps.purchase-requests.approve', this.purchaseRequest.id));
      }
    },
    reject() {
      const reason = prompt('Masukkan alasan penolakan:');
      if (reason) {
        this.$inertia.post(route('apps.purchase-requests.reject', this.purchaseRequest.id), { rejection_reason: reason });
      }
    },
  },
};
</script>
