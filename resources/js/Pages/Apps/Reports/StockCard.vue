<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kartu Stok</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Filter -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="apply" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
              <label class="block font-medium text-sm text-gray-700">Barang *</label>
              <select v-model="form.item_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                <option value="">-- Pilih Barang --</option>
                <option v-for="i in items" :key="i.id" :value="i.id">{{ i.name }} ({{ i.code }})</option>
              </select>
            </div>
            <div>
              <label class="block font-medium text-sm text-gray-700">Dari Tanggal</label>
              <input v-model="form.date_from" type="date" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="block font-medium text-sm text-gray-700">Sampai Tanggal</label>
              <input v-model="form.date_to" type="date" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div class="flex gap-2">
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Tampilkan</button>
              <a v-if="card.item" :href="exportUrl" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Export CSV</a>
            </div>
          </form>
        </div>

        <!-- Hasil -->
        <div v-if="card.item" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-1">{{ card.item.name }} ({{ card.item.code }})</h3>
          <p class="text-sm text-gray-500 mb-4">Periode {{ card.date_from }} s/d {{ card.date_to }}</p>

          <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Referensi</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Masuk</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Keluar</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr class="bg-blue-50">
                <td colspan="5" class="px-4 py-2 font-semibold">Saldo Awal</td>
                <td class="px-4 py-2 text-right font-mono font-semibold">{{ Number(card.opening).toLocaleString('id-ID') }}</td>
              </tr>
              <tr v-for="(row, idx) in ledgerRows" :key="idx">
                <td class="px-4 py-2 text-sm whitespace-nowrap">{{ row.tgl }}</td>
                <td class="px-4 py-2 text-sm font-mono">{{ row.ref || '-' }}</td>
                <td class="px-4 py-2 text-sm">{{ row.ket }}</td>
                <td class="px-4 py-2 text-right font-mono text-green-700">{{ row.qty > 0 ? row.qty : '' }}</td>
                <td class="px-4 py-2 text-right font-mono text-red-700">{{ row.qty < 0 ? -row.qty : '' }}</td>
                <td class="px-4 py-2 text-right font-mono font-semibold">{{ Number(row.saldo).toLocaleString('id-ID') }} {{ card.item.unit }}</td>
              </tr>
              <tr v-if="!card.rows.length">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada mutasi pada periode ini.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center text-gray-500">
          Pilih barang dan periode untuk melihat kartu stok.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/App.vue';

export default {
  components: { AppLayout },
  props: { card: Object, items: Array, filters: Object },
  data() {
    return {
      form: {
        item_id: this.filters?.item_id || '',
        date_from: this.filters?.date_from || '',
        date_to: this.filters?.date_to || '',
      },
    };
  },
  computed: {
    ledgerRows() {
      let saldo = Number(this.card.opening);
      return (this.card.rows || []).map((row) => {
        saldo += Number(row.qty);
        return { ...row, saldo };
      });
    },
    exportUrl() {
      return route('apps.reports.stock-card.export', this.form);
    },
  },
  methods: {
    apply() {
      this.$inertia.get(route('apps.reports.stock-card'), this.form, { preserveState: true });
    },
  },
};
</script>