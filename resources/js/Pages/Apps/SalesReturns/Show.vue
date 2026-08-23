<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Retur - {{ salesReturn.return_number }}</h2>
        <div class="flex gap-2">
          <Link :href="route('apps.sales-returns.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-medium">Kembali</Link>
          <template v-if="salesReturn.status === 'pending' && hasAnyPermission(['sales_returns.approve'])">
            <button @click="approve" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">Setujui &amp; Restok</button>
            <button @click="reject" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">Tolak</button>
          </template>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div v-if="$page.props.flash?.error" class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-md text-sm">{{ $page.props.flash.error }}</div>

        <div class="bg-white shadow-sm rounded-lg p-6 mb-4">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">No. Retur</div>
              <div class="font-mono font-medium">{{ salesReturn.return_number }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Tanggal</div>
              <div>{{ salesReturn.return_date }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Pelanggan</div>
              <div>{{ salesReturn.customer?.name || '-' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Invoice</div>
              <div>{{ salesReturn.sales_invoice?.invoice_number || '-' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Status</div>
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="{
                      'bg-yellow-100 text-yellow-700': salesReturn.status === 'pending',
                      'bg-green-100 text-green-700': salesReturn.status === 'approved',
                      'bg-red-100 text-red-700': salesReturn.status === 'rejected'
                    }">
                {{ statusLabel(salesReturn.status) }}
              </span>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Dibuat Oleh</div>
              <div>{{ salesReturn.creator?.name || '-' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Diproses Oleh</div>
              <div>{{ salesReturn.approver?.name || '-' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 uppercase mb-1">Waktu Proses</div>
              <div>{{ salesReturn.approved_at || '-' }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga/Unit</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="line in salesReturn.items" :key="line.id">
                <td class="px-4 py-2.5">
                  <div class="text-sm">{{ line.item?.name }}</div>
                  <div class="text-xs text-gray-400 font-mono">{{ line.item?.code }}</div>
                </td>
                <td class="px-4 py-2.5 text-sm text-right">{{ line.qty }} {{ line.unit }}</td>
                <td class="px-4 py-2.5 text-sm font-mono text-right">{{ fmt(line.unit_price) }}</td>
                <td class="px-4 py-2.5 text-sm font-mono text-right">{{ fmt(line.subtotal) }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50">
              <tr>
                <td colspan="3" class="px-4 py-3 text-sm font-bold uppercase">Total</td>
                <td class="px-4 py-3 text-sm font-bold font-mono text-right">{{ fmt(total) }}</td>
              </tr>
            </tfoot>
          </table>
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
  props: { salesReturn: Object },
  computed: {
    total() {
      return (this.salesReturn.items || []).reduce((acc, i) => acc + Number(i.subtotal || 0), 0);
    },
  },
  methods: {
    fmt(v) {
      return 'Rp ' + Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    },
    statusLabel(s) {
      return { pending: 'Pending', approved: 'Disetujui', rejected: 'Ditolak' }[s] || s;
    },
    approve() {
      if (confirm('Setujui retur ini? Stok akan dikembalikan ke gudang.')) {
        this.$inertia.post(route('apps.sales-returns.approve', this.salesReturn.id));
      }
    },
    reject() {
      if (confirm('Tolak retur ini?')) {
        this.$inertia.post(route('apps.sales-returns.reject', this.salesReturn.id));
      }
    },
  },
};
</script>