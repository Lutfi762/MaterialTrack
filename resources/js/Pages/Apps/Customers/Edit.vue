<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pelanggan</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Pelanggan *</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Kode Pelanggan</label>
                <input v-model="form.code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tipe</label>
                <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="company">Perusahaan (Company)</option>
                  <option value="individual">Perorangan (Individual)</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Kontak Person</label>
                <input v-model="form.contact_person" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Email</label>
                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Telepon</label>
                <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Batas Kredit (Rp)</label>
                <input v-model="form.credit_limit" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tempo Pembayaran (Hari)</label>
                <input v-model="form.payment_term_days" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div class="md:col-span-2">
                <label class="block font-medium text-sm text-gray-700">Alamat</label>
                <textarea v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <Link :href="route('apps.customers.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">
                Batal
              </Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">
                Update
              </button>
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
  props: {
    customer: Object,
    errors: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: this.customer.name,
        code: this.customer.code || '',
        contact_person: this.customer.contact_person || '',
        email: this.customer.email || '',
        phone: this.customer.phone || '',
        address: this.customer.address || '',
        type: this.customer.type || 'company',
        credit_limit: this.customer.credit_limit || 0,
        payment_term_days: this.customer.payment_term_days || 30,
        notes: this.customer.notes || '',
        is_active: this.customer.is_active,
      }),
    };
  },
  methods: {
    submit() {
      this.form.put(route('apps.customers.update', this.customer.id));
    },
  },
};
</script>
