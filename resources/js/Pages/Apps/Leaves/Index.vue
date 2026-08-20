<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengajuan Cuti (Leaves)</h2>
        <Link :href="route('apps.leaves.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Ajukan Cuti
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Cuti</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Mulai - Selesai</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Hari</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="l in leaves.data" :key="l.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ l.employee?.first_name }} {{ l.employee?.last_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ l.leave_type?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ l.start_date }} s/d {{ l.end_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ l.total_days }} Hari</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                  <span class="px-2 py-1 text-xs rounded-full font-bold"
                    :class="{
                      'bg-yellow-100 text-yellow-800': l.status === 'pending',
                      'bg-green-100 text-green-800': l.status === 'approved',
                      'bg-red-100 text-red-800': l.status === 'rejected'
                    }">
                    {{ l.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button v-if="l.status === 'pending'" @click="approve(l.id)" class="text-green-600 hover:text-green-900 mr-2">Setujui</button>
                  <button v-if="l.status === 'pending'" @click="reject(l.id)" class="text-red-600 hover:text-red-900">Tolak</button>
                </td>
              </tr>
              <tr v-if="leaves.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada pengajuan cuti.</td>
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
  props: { leaves: Object },
  methods: {
    approve(id) {
      if (confirm('Setujui pengajuan cuti ini?')) {
        this.$inertia.post(route('apps.leaves.approve', id));
      }
    },
    reject(id) {
      const reason = prompt('Alasan penolakan:');
      if (reason) {
        this.$inertia.post(route('apps.leaves.reject', id), { rejection_reason: reason });
      }
    },
  },
};
</script>
