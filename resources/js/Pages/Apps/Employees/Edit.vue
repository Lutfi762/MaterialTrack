<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Karyawan</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">NIK / Kode Karyawan *</label>
                <input v-model="form.employee_id" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Depan *</label>
                <input v-model="form.first_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Belakang</label>
                <input v-model="form.last_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Email</label>
                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Telepon / WhatsApp</label>
                <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Departemen</label>
                <select v-model="form.department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">-- Pilih Departemen --</option>
                  <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Jabatan / Posisi</label>
                <input v-model="form.position" type="text" placeholder="misal: Staff Accounting" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Status Kepegawaian *</label>
                <select v-model="form.employment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="permanent">Tetap (Permanent)</option>
                  <option value="contract">Kontrak (Contract)</option>
                  <option value="probation">Probation</option>
                  <option value="intern">Magang (Intern)</option>
                  <option value="resigned">Resign</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Masuk (Hire Date) *</label>
                <input v-model="form.hire_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Gaji Pokok (Rp)</label>
                <input v-model="form.basic_salary" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Status Aktif *</label>
                <select v-model="form.is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option :value="true">Aktif</option>
                  <option :value="false">Non-Aktif</option>
                </select>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <Link :href="route('apps.employees.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Update Karyawan</button>
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
  props: { employee: Object, departments: Array },
  data() {
    return {
      form: this.$inertia.form({
        employee_id: this.employee.employee_id,
        first_name: this.employee.first_name,
        last_name: this.employee.last_name || '',
        email: this.employee.email || '',
        phone: this.employee.phone || '',
        department_id: this.employee.department_id || '',
        position: this.employee.position || '',
        employment_status: this.employee.employment_status || 'permanent',
        hire_date: this.employee.hire_date,
        basic_salary: this.employee.basic_salary || 0,
        is_active: this.employee.is_active,
      }),
    };
  },
  methods: {
    submit() {
      this.form.put(route('apps.employees.update', this.employee.id));
    },
  },
};
</script>
