<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Akun Akuntansi</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Kode Akun *</label>
                <input v-model="form.code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Akun *</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Kategori Utama *</label>
                <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="asset">Aset</option>
                  <option value="liability">Kewajiban</option>
                  <option value="equity">Ekuitas</option>
                  <option value="revenue">Pendapatan</option>
                  <option value="expense">Beban</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Sub Tipe *</label>
                <input v-model="form.sub_type" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Saldo Awal (Rp)</label>
                <input v-model="form.opening_balance" type="number" step="any" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <p class="text-xs text-gray-400 mt-1">Konvensi debit-positive: aset/beban positif; kewajiban/ekuitas/pendapatan negatif (mis. modal -155000000).</p>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <Link :href="route('apps.chart-of-accounts.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Update</button>
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
  props: { account: Object, headerAccounts: Array },
  data() {
    return {
      form: this.$inertia.form({
        code: this.account.code,
        name: this.account.name,
        type: this.account.type,
        sub_type: this.account.sub_type,
        parent_id: this.account.parent_id || '',
        is_header: this.account.is_header,
        opening_balance: this.account.opening_balance || 0,
        description: this.account.description || '',
        is_active: this.account.is_active,
      }),
    };
  },
  methods: {
    submit() {
      this.form.put(route('apps.chart-of-accounts.update', this.account.id));
    },
  },
};
</script>
