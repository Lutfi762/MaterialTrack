<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Karyawan (Employees)</h2>
        <Link :href="route('apps.employees.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Tambah Karyawan
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK / Kode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen & Jabatan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Kerja</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="e in employees.data" :key="e.id">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold">{{ e.employee_id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ e.first_name }} {{ e.last_name || '' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ e.department?.name || '-' }} <br><span class="text-xs text-gray-400">{{ e.position || '-' }}</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ e.employment_status }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">Rp {{ Number(e.basic_salary || 0).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.employees.edit', e.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                  <button @click="destroy(e.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                </td>
              </tr>
              <tr v-if="employees.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data karyawan.</td>
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
  props: { employees: Object },
  methods: {
    destroy(id) {
      if (confirm('Hapus data karyawan ini?')) {
        this.$inertia.delete(route('apps.employees.destroy', id));
      }
    },
  },
};
</script>
