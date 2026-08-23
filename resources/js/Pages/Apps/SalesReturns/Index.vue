<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Retur Penjualan (Sales Returns)</h2>
        <Link :href="route('apps.sales-returns.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat Retur
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div v-if="$page.props.flash?.error" class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-md text-sm">{{ $page.props.flash.error }}</div>
        <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded-md text-sm">{{ $page.props.flash.success }}</div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4 flex flex-wrap gap-3 items-end">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select v-model="form.status" @change="apply" class="border rounded-md px-2 py-1.5 text-sm">
              <option value="">Semua</option>
              <option value="pending">Pending</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Pelanggan</label>
            <select v-model="form.customer_id" @change="apply" class="border rounded-md px-2 py-1.5 text-sm">
              <option value="">Semua</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Dari</label>
            <input type="date" v-model="form.date_from" @change="apply" class="border rounded-md px-2 py-1.5 text-sm" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai</label>
            <input type="date" v-model="form.date_to" @change="apply" class="border rounded-md px-2 py-1.5 text-sm" />
          </div>
          <button @click="reset" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md text-sm">Reset</button>
        </div>
<!-- List -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Retur</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="sr in salesReturns.data" :key="sr.id">
                <td class="px-4 py-2.5 font-mono text-sm">
                  <Link :href="route('apps.sales-returns.show', sr.id)" class="text-indigo-600 hover:underline">{{ sr.return_number }}</Link>
                </td>
                <td class="px-4 py-2.5 text-sm">{{ sr.return_date }}</td>
                <td class="px-4 py-2.5 text-sm">{{ sr.customer?.name }}</td>
                <td class="px-4 py-2.5 text-sm text-gray-600">{{ sr.items.length }} item</td>
                <td class="px-4 py-2.5 text-sm font-mono text-right">{{ fmt(totalOf(sr)) }}</td>
                <td class="px-4 py-2.5">
                  <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                        :class="{
                          'bg-yellow-100 text-yellow-700': sr.status === 'pending',
                          'bg-green-100 text-green-700': sr.status === 'approved',
                          'bg-red-100 text-red-700': sr.status === 'rejected'
                        }">
                    {{ statusLabel(sr.status) }}
                  </span>
                </td>
                                <td class="px-4 py-2.5 text-right text-sm whitespace-nowrap">
                  <Link :href="route('apps.sales-returns.show', sr.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Detail</Link>
                  <template v-if="hasAnyPermission(['sales_returns.edit'])">
                    <Link :href="route('apps.sales-returns.edit', sr.id)" class="text-amber-600 hover:text-amber-900 mr-3">Edit</Link>
                  </template>
                  <template v-if="sr.status === 'pending' && hasAnyPermission(['sales_returns.approve'])">
                    <button @click="approve(sr)" class="text-green-600 hover:text-green-900 mr-3">Setujui</button>
                    <button @click="reject(sr)" class="text-red-600 hover:text-red-900">Tolak</button>
                  </template>
                  <template v-if="hasAnyPermission(['sales_returns.delete'])">
                    <button @click="remove(sr)" type="button" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                  </template>
                </td>
              </tr>
              <tr v-if="salesReturns.data.length === 0">
                <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Belum ada retur penjualan.</td>
              </tr>
            </tbody>
          </table>

          <div v-if="salesReturns.last_page > 1" class="flex justify-between items-center p-4">
            <Link v-if="salesReturns.prev_page_url" :href="salesReturns.prev_page_url" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Sebelumnya</Link>
            <span v-else class="px-3 py-2 text-gray-300 text-sm">Sebelumnya</span>
            <span class="text-sm text-gray-500">Halaman {{ salesReturns.current_page }} / {{ salesReturns.last_page }}</span>
            <Link v-if="salesReturns.next_page_url" :href="salesReturns.next_page_url" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Berikutnya</Link>
            <span v-else class="px-3 py-2 text-gray-300 text-sm">Berikutnya</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script>
import AppLayout from '@/Layouts/App.vue';
import { Link } from '@inertiajs/vue3';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
  components: { AppLayout, Link },
  props: { salesReturns: Object, customers: Array, filters: Object },
  data() {
    return {
      form: {
        status: this.filters.status || '',
        customer_id: this.filters.customer_id || '',
        date_from: this.filters.date_from || '',
        date_to: this.filters.date_to || '',
      },
    };
  },
  methods: {
    fmt(v) {
      return 'Rp ' + Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    },
    totalOf(sr) {
      return (sr.items || []).reduce((acc, i) => acc + Number(i.subtotal || 0), 0);
    },
    statusLabel(s) {
      return { pending: 'Pending', approved: 'Disetujui', rejected: 'Ditolak' }[s] || s;
    },
    apply() {
      this.$inertia.get(route('apps.sales-returns.index'), this.form, {
        preserveState: true, replace: true, preserveScroll: true,
      });
    },
    reset() {
      this.$inertia.get(route('apps.sales-returns.index'), {}, { replace: true });
    },
    approve(sr) {
      if (confirm('Setujui retur ' + sr.return_number + '? Stok barang akan dikembalikan ke gudang.')) {
        this.$inertia.post(route('apps.sales-returns.approve', sr.id));
      }
    },
        reject(sr) {
      if (confirm('Tolak retur ' + sr.return_number + '?')) {
        this.$inertia.post(route('apps.sales-returns.reject', sr.id));
      }
    },
    async remove(sr) {
      if (await confirmDelete()) {
        this.$inertia.delete(route('apps.sales-returns.destroy', sr.id));
      }
    },
  },
};
</script>