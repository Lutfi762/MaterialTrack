<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Retur Penjualan</h2>
        <Link :href="route('apps.sales-returns.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-medium">Kembali</Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Retur *</label>
                <input type="date" v-model="form.return_date" class="w-full border rounded-md px-3 py-2 text-sm" />
                <p v-if="$page.props.errors.return_date" class="text-xs text-red-600 mt-1">{{ $page.props.errors.return_date }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan *</label>
                <select v-model="form.customer_id" @change="onCustomerChange" class="w-full border rounded-md px-3 py-2 text-sm">
                  <option value="">-- Pilih Pelanggan --</option>
                  <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.code }} - {{ c.name }}</option>
                </select>
                <p v-if="$page.props.errors.customer_id" class="text-xs text-red-600 mt-1">{{ $page.props.errors.customer_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Referensi Invoice</label>
                <select v-model="form.sales_invoice_id" class="w-full border rounded-md px-3 py-2 text-sm">
                  <option value="">Tanpa invoice</option>
                  <option v-for="inv in filteredInvoices" :key="inv.id" :value="inv.id">{{ inv.invoice_number }} - {{ inv.invoice_date }} ({{ fmt(inv.total) }})</option>
                </select>
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Retur</label>
              <textarea v-model="form.reason" rows="2" class="w-full border rounded-md px-3 py-2 text-sm" placeholder="opsional"></textarea>
            </div>

            <!-- Items -->
            <div class="mt-6">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-gray-700">Item yang Dikembalikan</h3>
                <button type="button" @click="addRow" class="px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-md text-sm hover:bg-gray-200">+ Tambah Item</button>
              </div>
              <p v-if="$page.props.errors.items" class="text-xs text-red-600 mb-2">{{ $page.props.errors.items }}</p>

              <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item *</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase" style="width:110px">Qty *</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase" style="width:150px">Harga/Unit *</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase" style="width:130px">Subtotal</th>
                    <th class="px-3 py-2" style="width:40px"></th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(row, idx) in form.items" :key="idx">
                    <td class="px-3 py-2">
                      <select v-model="row.item_id" @change="onItemSelect(row)" class="w-full border rounded-md px-2 py-1.5 text-sm">
                        <option value="">-- Pilih Item --</option>
                        <option v-for="it in items" :key="it.id" :value="it.id">{{ it.code }} - {{ it.name }} (stok {{ it.current_stock }})</option>
                      </select>
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" min="1" v-model="row.qty" class="w-full border rounded-md px-2 py-1.5 text-sm" />
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" min="0" step="0.01" v-model="row.unit_price" class="w-full border rounded-md px-2 py-1.5 text-sm" />
                    </td>
                    <td class="px-3 py-2 text-right text-sm font-mono">{{ subOf(row) }}</td>
                    <td class="px-3 py-2 text-center">
                      <button type="button" @click="removeRow(idx)" class="text-red-600 text-sm">&#10005;</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
<div class="mt-4 flex items-center justify-between">
              <div class="text-sm font-semibold">Total Retur: <span class="font-mono">{{ fmt(total) }}</span></div>
              <div class="flex gap-2">
                <Link :href="route('apps.sales-returns.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Batal</Link>
                <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium disabled:opacity-50">
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Retur' }}
                </button>
              </div>
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
  props: { customers: Array, salesInvoices: Array, items: Array },
  data() {
    return {
      form: this.$inertia.form({
        return_date: new Date().toISOString().slice(0, 10),
        customer_id: '',
        sales_invoice_id: '',
        reason: '',
        items: [{ item_id: '', qty: 1, unit_price: '' }],
      }),
    };
  },
  computed: {
    filteredInvoices() {
      if (!this.form.customer_id) return this.salesInvoices;
      return this.salesInvoices.filter((i) => Number(i.customer_id) === Number(this.form.customer_id));
    },
    total() {
      return this.form.items.reduce((acc, r) => {
        const qty = Number(r.qty) || 0;
        const price = Number(r.unit_price) || 0;
        return acc + qty * price;
      }, 0);
    },
  },
  methods: {
    fmt(v) {
      return 'Rp ' + Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    },
    subOf(row) {
      const qty = Number(row.qty) || 0;
      const price = Number(row.unit_price) || 0;
      return qty * price;
    },
    addRow() {
      this.form.items.push({ item_id: '', qty: 1, unit_price: '' });
    },
    removeRow(idx) {
      this.form.items.splice(idx, 1);
    },
    onItemSelect(row) {
      const item = this.items.find((i) => Number(i.id) === Number(row.item_id));
      row.unit_price = item ? item.price || 0 : '';
    },
    onCustomerChange() {
      this.form.sales_invoice_id = '';
    },
    submit() {
      this.form.post(route('apps.sales-returns.store'), {
        preserveScroll: true,
        onSuccess: () => this.form.reset(),
      });
    },
  },
};
</script>