<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Akun Akuntansi</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Kode Akun *</label>
                <input v-model="form.code" type="text" placeholder="misal: 1101" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Akun *</label>
                <input v-model="form.name" type="text" placeholder="misal: Kas Utama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Kategori Utama (Type) *</label>
                <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="asset">Aset (Asset)</option>
                  <option value="liability">Kewajiban (Liability)</option>
                  <option value="equity">Ekuitas (Equity)</option>
                  <option value="revenue">Pendapatan (Revenue)</option>
                  <option value="expense">Beban / Biaya (Expense)</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Sub Tipe *</label>
                <input v-model="form.sub_type" type="text" placeholder="misal: current_asset, fixed_asset" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Akun Induk (Parent Header)</label>
                <select v-model="form.parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">-- Tidak ada (Header Utama) --</option>
                  <option v-for="acc in headerAccounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Saldo Awal (Rp)</label>
                <input v-model="form.opening_balance" type="number" step="any" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <p class="text-xs text-gray-400 mt-1">Konvensi debit-positive: aset/beban positif; kewajiban/ekuitas/pendapatan negatif (mis. modal -155000000).</p>
              </div>

              <div class="flex items-center">
                <label class="inline-flex items-center">
                  <input type="checkbox" v-model="form.is_header" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <span class="ml-2 text-sm text-gray-600 font-medium">Jadikan Akun Induk / Header (Tidak bisa di-posting jurnal)</span>
                </label>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <Link :href="route('apps.chart-of-accounts.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Simpan Akun</button>
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
  props: { headerAccounts: Array, errors: Object },
  data() {
    return {
      form: this.$inertia.form({
        code: '',
        name: '',
        type: 'asset',
        sub_type: 'current_asset',
        parent_id: '',
        is_header: false,
        opening_balance: 0,
        description: '',
      }),
    };
  },
  methods: {
    submit() {
      this.form.post(route('apps.chart-of-accounts.store'));
    },
  },
};
</script>
