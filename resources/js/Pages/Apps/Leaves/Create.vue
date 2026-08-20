<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengajuan Cuti Karyawan</h2>
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
                  <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.employee_code }} - {{ e.first_name }} {{ e.last_name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Jenis Cuti *</label>
                <select v-model="form.leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="">-- Pilih Jenis Cuti --</option>
                  <option v-for="lt in leaveTypes" :key="lt.id" :value="lt.id">{{ lt.name }} (Max: {{ lt.default_days }} Hari)</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Mulai *</label>
                <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Selesai *</label>
                <input v-model="form.end_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div class="md:col-span-2">
                <label class="block font-medium text-sm text-gray-700">Alasan Cuti</label>
                <textarea v-model="form.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <Link :href="route('apps.leaves.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Kirim Pengajuan</button>
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
  props: { employees: Array, leaveTypes: Array },
  data() {
    const today = new Date().toISOString().substr(0, 10);
    return {
      form: this.$inertia.form({
        employee_id: '',
        leave_type_id: '',
        start_date: today,
        end_date: today,
        reason: '',
      }),
    };
  },
  methods: {
    submit() {
      this.form.post(route('apps.leaves.store'));
    },
  },
};
</script>
