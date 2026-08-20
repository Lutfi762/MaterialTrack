<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Presensi / Kehadiran Karyawan (Attendance)</h2>
        <Link :href="route('apps.attendances.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Catat Presensi
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Keluar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="att in attendances.data" :key="att.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ att.date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ att.employee?.first_name }} {{ att.employee?.last_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ att.clock_in || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ att.clock_out || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                  <span class="px-2 py-1 text-xs rounded-full font-bold"
                    :class="{
                      'bg-green-100 text-green-800': att.status === 'present',
                      'bg-yellow-100 text-yellow-800': att.status === 'late',
                      'bg-red-100 text-red-800': att.status === 'absent',
                      'bg-blue-100 text-blue-800': att.status === 'leave'
                    }">
                    {{ att.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="attendances.data.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada rekaman presensi.</td>
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
  props: { attendances: Object },
};
</script>
