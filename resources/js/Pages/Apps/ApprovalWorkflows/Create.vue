<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Alur Persetujuan (Approval Workflow)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Workflow *</label>
                <input v-model="form.name" type="text" placeholder="misal: Approval PO > 50 Juta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Modul Target *</label>
                <select v-model="form.module" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="purchase_request">Purchase Request</option>
                  <option value="purchase_order">Purchase Order</option>
                  <option value="sales_order">Sales Order</option>
                  <option value="leave">Leave Request</option>
                  <option value="stock_out">Stock Out</option>
                  <option value="other">Lainnya</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="block font-medium text-sm text-gray-700">Deskripsi</label>
                <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Keterangan alur persetujuan ini"></textarea>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nominal Minimal (Rp)</label>
                <input v-model="form.min_amount" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nominal Maksimal (Rp) (Opsional)</label>
                <input v-model="form.max_amount" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>

            <!-- Steps -->
            <div class="mb-6">
              <h3 class="font-semibold text-md text-gray-700 mb-2">Tahapan Approver</h3>
              <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-16">Urutan</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tipe Approver *</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Approver</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 w-16">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(step, index) in form.steps" :key="index">
                    <td class="p-3 text-sm font-bold text-center">{{ index + 1 }}</td>
                    <td class="p-2">
                      <select v-model="step.approver_type" class="w-full rounded-md border-gray-300 text-sm" required>
                        <option value="user">User Spesifik</option>
                        <option value="role">Role Jabatan</option>
                        <option value="manager">Atasan / Manager Direct</option>
                      </select>
                    </td>
                    <td class="p-2">
                      <select v-if="step.approver_type === 'user'" v-model="step.approver_value" class="w-full rounded-md border-gray-300 text-sm" required>
                        <option value="">-- Pilih User --</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                      </select>
                      <input v-else-if="step.approver_type === 'role'" v-model="step.approver_value" type="text" placeholder="Manager Finance" class="w-full rounded-md border-gray-300 text-sm" required />
                      <span v-else class="text-sm text-gray-500 italic">Atasan Langsung</span>
                    </td>
                    <td class="p-2 text-center">
                      <button @click.prevent="removeStep(index)" class="text-red-600 font-bold hover:text-red-800" :disabled="form.steps.length === 1">✕</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <button @click.prevent="addStep" class="mt-2 text-sm text-indigo-600 font-semibold hover:text-indigo-800">+ Tambah Tahap</button>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.approval-workflows.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Simpan Workflow</button>
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
  props: { users: Array },
  data() {
    return {
      form: this.$inertia.form({
        name: '',
        module: 'purchase_order',
        min_amount: 0,
        max_amount: null,
        description: '',
        steps: [{ approver_type: 'user', approver_value: '' }],
      }),
    };
  },
  methods: {
    addStep() {
      this.form.steps.push({ approver_type: 'user', approver_value: '' });
    },
    removeStep(index) {
      if (this.form.steps.length > 1) {
        this.form.steps.splice(index, 1);
      }
    },
    submit() {
      this.form.post(route('apps.approval-workflows.store'));
    },
  },
};
</script>
