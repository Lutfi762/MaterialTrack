<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Entri Jurnal (Journal Entry)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Jurnal *</label>
                <input v-model="form.entry_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Keterangan / Narasi</label>
                <input v-model="form.narration" type="text" placeholder="misal: Pembayaran Biaya Sewa Kantor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>

            <!-- Detail Jurnal Lines -->
            <div class="mb-6">
              <h3 class="font-semibold text-md text-gray-700 mb-2">Baris Akun Jurnal</h3>
              <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Akun (COA) *</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Debit (Rp)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Kredit (Rp)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Memo</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 w-16">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in form.lines" :key="index">
                    <td class="p-2">
                      <select v-model="row.chart_of_account_id" class="w-full rounded-md border-gray-300 text-sm" required>
                        <option value="">-- Pilih Akun --</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option>
                      </select>
                    </td>
                    <td class="p-2">
                      <input v-model.number="row.debit" type="number" min="0" step="any" class="w-full rounded-md border-gray-300 text-sm" />
                    </td>
                    <td class="p-2">
                      <input v-model.number="row.credit" type="number" min="0" step="any" class="w-full rounded-md border-gray-300 text-sm" />
                    </td>
                    <td class="p-2">
                      <input v-model="row.memo" type="text" class="w-full rounded-md border-gray-300 text-sm" />
                    </td>
                    <td class="p-2 text-center">
                      <button @click.prevent="removeLine(index)" class="text-red-600 font-bold hover:text-red-800" :disabled="form.lines.length <= 2">✕</button>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 font-bold text-sm">
                  <tr>
                    <td class="p-3 text-right">Total:</td>
                    <td class="p-3 font-mono">Rp {{ totalDebit.toLocaleString('id-ID') }}</td>
                    <td class="p-3 font-mono">Rp {{ totalCredit.toLocaleString('id-ID') }}</td>
                    <td colspan="2" class="p-3">
                      <span v-if="totalDebit === totalCredit && totalDebit > 0" class="text-green-600 text-xs">✓ Seimbang</span>
                      <span v-else class="text-red-600 text-xs">✕ Belum Seimbang</span>
                    </td>
                  </tr>
                </tfoot>
              </table>
              <button @click.prevent="addLine" class="mt-2 text-sm text-indigo-600 font-semibold hover:text-indigo-800">+ Tambah Baris Akun</button>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.journal-entries.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing || totalDebit !== totalCredit || totalDebit === 0" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Posting Jurnal</button>
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
  props: { accounts: Array },
  data() {
    return {
      form: this.$inertia.form({
        entry_date: new Date().toISOString().substr(0, 10),
        narration: '',
        lines: [
          { chart_of_account_id: '', debit: 0, credit: 0, memo: '' },
          { chart_of_account_id: '', debit: 0, credit: 0, memo: '' },
        ],
      }),
    };
  },
  computed: {
    totalDebit() {
      return this.form.lines.reduce((sum, item) => sum + (Number(item.debit) || 0), 0);
    },
    totalCredit() {
      return this.form.lines.reduce((sum, item) => sum + (Number(item.credit) || 0), 0);
    },
  },
  methods: {
    addLine() {
      this.form.lines.push({ chart_of_account_id: '', debit: 0, credit: 0, memo: '' });
    },
    removeLine(index) {
      if (this.form.lines.length > 2) {
        this.form.lines.splice(index, 1);
      }
    },
    submit() {
      this.form.post(route('apps.journal-entries.store'));
    },
  },
};
</script>
