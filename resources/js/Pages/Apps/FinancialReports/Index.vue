<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Keuangan</h2>
        <a :href="route('apps.financial-reports.pdf', { tab, from, to })" target="_blank"
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
          &#128196; Export PDF
        </a>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

          <!-- Tab & Periode -->
          <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="flex gap-2">
              <button v-for="t in tabs" :key="t.value" @click="switchTab(t.value)"
                      class="px-4 py-2 rounded-md text-sm font-medium border"
                      :class="tab === t.value ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                {{ t.label }}
              </button>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <input type="date" :value="from" @change="from = $event.target.value" class="border rounded-md px-2 py-1.5" />
              <span>s/d</span>
              <input type="date" :value="to" @change="to = $event.target.value" class="border rounded-md px-2 py-1.5" />
              <button @click="apply" class="px-3 py-1.5 bg-gray-800 text-white rounded-md hover:bg-gray-700">Tampilkan</button>
            </div>
          </div>

          <!-- Trial Balance -->
          <template v-if="tab === 'trial-balance'">
            <h3 class="text-lg font-semibold mb-3">Neraca Saldo (Trial Balance)</h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akun</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kredit</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="r in trialBalance.rows" :key="r.code">
                  <td class="px-4 py-2 font-mono text-sm">{{ r.code }}</td>
                  <td class="px-4 py-2 text-sm">{{ r.name }}</td>
                  <td class="px-4 py-2 text-sm font-mono text-right">{{ fmt(r.debit) }}</td>
                  <td class="px-4 py-2 text-sm font-mono text-right">{{ fmt(r.credit) }}</td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="2" class="px-4 py-3 text-sm font-bold uppercase">Total</td>
                  <td class="px-4 py-3 text-sm font-bold font-mono text-right">{{ fmt(trialBalance.total_debit) }}</td>
                  <td class="px-4 py-3 text-sm font-bold font-mono text-right">{{ fmt(trialBalance.total_credit) }}</td>
                </tr>
              </tfoot>
            </table>
            <p v-if="hasError" class="mt-3 text-sm text-red-600 font-medium">Perhatian: Neraca saldo tidak balance (debit &#8800; kredit). Periksa jurnal.</p>
            <p v-else class="mt-3 text-sm text-green-700 font-medium">Neraca saldo balance: Debit = Kredit.</p>
          </template>
<!-- Income Statement -->
          <template v-else-if="tab === 'income-statement'">
            <h3 class="text-lg font-semibold mb-3">Laporan Laba Rugi</h3>
            <div v-for="s in incomeStatement.sections" :key="s.label" class="mb-5">
              <h4 class="text-sm font-semibold text-indigo-700 uppercase tracking-wide mb-2">{{ s.label }}</h4>
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Akun</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="r in s.rows" :key="s.label + r.code">
                    <td class="px-4 py-2 text-sm">{{ r.code }} &mdash; {{ r.name }}</td>
                    <td class="px-4 py-2 text-sm font-mono text-right">{{ fmt(r.amount) }}</td>
                  </tr>
                  <tr class="bg-indigo-50">
                    <td class="px-4 py-2 text-sm font-bold">Total {{ s.label }}</td>
                    <td class="px-4 py-2 text-sm font-bold font-mono text-right">{{ fmt(s.total) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-4 border-t-2 border-gray-200 pt-4">
              <table class="min-w-full">
                <tbody class="text-sm">
                  <tr><td class="py-1.5 px-4">Laba Kotor (Gross Profit)</td><td class="py-1.5 px-4 font-mono text-right">{{ fmt(incomeStatement.gross_profit) }}</td></tr>
                  <tr><td class="py-1.5 px-4">Laba Usaha (Operating Income)</td><td class="py-1.5 px-4 font-mono text-right">{{ fmt(incomeStatement.operating_income) }}</td></tr>
                  <tr class="bg-green-50">
                    <td class="py-2.5 px-4 font-bold uppercase">Laba Bersih Periode</td>
                    <td class="py-2.5 px-4 font-bold font-mono text-right">{{ fmt(incomeStatement.net_income) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

          <!-- Balance Sheet -->
          <template v-else>
            <h3 class="text-lg font-semibold mb-3">Neraca (Posisi Keuangan)</h3>
            <div v-for="g in balanceGroups" :key="g.type" class="mb-5">
              <h4 class="text-sm font-semibold text-indigo-700 uppercase tracking-wide mb-2">{{ g.label }}</h4>
              <table class="min-w-full divide-y divide-gray-200">
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="r in rowsByType(g.type)" :key="'bs-' + g.type + '-' + r.code">
                    <td class="px-4 py-2 text-sm">{{ r.code }} &mdash; {{ r.name }}</td>
                    <td class="px-4 py-2 text-sm font-mono text-right">{{ fmt(r.amount) }}</td>
                  </tr>
                  <tr v-if="g.type === 'equity'" class="bg-yellow-50">
                    <td class="px-4 py-2 text-sm">Laba Berjalan (periode laporan)</td>
                    <td class="px-4 py-2 text-sm font-mono text-right">{{ fmt(balanceSheet.net_income) }}</td>
                  </tr>
                  <tr class="bg-indigo-50">
                    <td class="px-4 py-2 text-sm font-bold">Total {{ g.label }}</td>
                    <td class="px-4 py-2 text-sm font-bold font-mono text-right">{{ fmt(balanceSheet.totals[g.type]) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="border-t-2 border-gray-200 pt-4 flex justify-between text-sm font-bold px-1">
              <span>Total Aset: <span class="font-mono">{{ fmt(balanceSheet.totals.asset) }}</span></span>
              <span>Total Kewajiban + Ekuitas: <span class="font-mono">{{ fmt(balanceSheet.totals.liability + balanceSheet.totals.equity) }}</span></span>
            </div>
          </template>
</div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/App.vue';

export default {
  components: { AppLayout },
  props: {
    tab: String,
    from: String,
    to: String,
    trialBalance: Object,
    incomeStatement: Object,
    balanceSheet: Object,
  },
  data() {
    return {
      tabs: [
        { value: 'trial-balance', label: 'Neraca Saldo' },
        { value: 'income-statement', label: 'Laba Rugi' },
        { value: 'balance-sheet', label: 'Neraca' },
      ],
      balanceGroups: [
        { type: 'asset', label: 'Aset' },
        { type: 'liability', label: 'Kewajiban' },
        { type: 'equity', label: 'Ekuitas' },
      ],
    };
  },
  computed: {
    hasError() {
      return Math.abs((Number(this.trialBalance.total_debit) || 0) - (Number(this.trialBalance.total_credit) || 0)) > 0.01;
    },
  },
  methods: {
    fmt(v) {
      return 'Rp ' + Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    },
    rowsByType(type) {
      return (this.balanceSheet.rows || []).filter((r) => r.type === type);
    },
    switchTab(t) {
      this.tab = t;
      this.apply();
    },
    apply() {
      this.$inertia.get(route('apps.financial-reports.index'), {
        tab: this.tab, from: this.from, to: this.to,
      }, { preserveState: true, replace: true, preserveScroll: true });
    },
  },
};
</script>