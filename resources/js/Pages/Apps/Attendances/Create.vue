<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Presensi Karyawan</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Karyawan *</label>
                <select v-model="form.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="">-- Pilih Karyawan --</option>
                  <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.employee_id }} - {{ e.first_name }} {{ e.last_name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal *</label>
                <input v-model="form.date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Jam Masuk (Clock In)</label>
                <input v-model="form.clock_in" type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Jam Keluar (Clock Out)</label>
                <input v-model="form.clock_out" type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Status Kehadiran *</label>
                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="present">Hadir (Present)</option>
                  <option value="late">Terlambat (Late)</option>
                  <option value="absent">Absen / Alpa (Absent)</option>
                  <option value="sick">Sakit (Sick)</option>
                  <option value="leave">Cuti / Izin (Leave)</option>
                  <option value="holiday">Libur (Holiday)</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="block font-medium text-sm text-gray-700">Catatan</label>
                <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <Link :href="route('apps.attendances.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Simpan Presensi</button>
            </div>
          </form>
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
  props: { employees: Array },
  data() {
    return {
      form: this.$inertia.form({
        employee_id: '',
        date: new Date().toISOString().substr(0, 10),
        clock_in: '08:00',
        clock_out: '17:00',
        status: 'present',
        notes: '',
      }),
    };
  },
  methods: {
    submit() {
      this.form.post(route('apps.attendances.store'));
    },
  },
};
</script>
