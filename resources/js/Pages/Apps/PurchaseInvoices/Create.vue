<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Faktur Pembelian (Purchase Invoice)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Pilih Purchase Order (PO) *</label>
                <select v-model="form.purchase_order_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="">-- Pilih PO --</option>
                  <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">{{ po.po_number }} - {{ po.supplier?.name }} (Rp {{ Number(po.total).toLocaleString('id-ID') }})</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">No. Faktur dari Supplier</label>
                <input v-model="form.supplier_invoice_no" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Faktur *</label>
                <input v-model="form.invoice_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Jatuh Tempo *</label>
                <input v-model="form.due_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.purchase-invoices.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Terbitkan Faktur</button>
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
  props: { purchaseOrders: Array },
  data() {
    const today = new Date().toISOString().substr(0, 10);
    const dueDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10);
    return {
      form: this.$inertia.form({
        purchase_order_id: '',
        supplier_invoice_no: '',
        invoice_date: today,
        due_date: dueDate,
        notes: '',
      }),
    };
  },
  methods: {
    submit() {
      this.form.post(route('apps.purchase-invoices.store'));
    },
  },
};
</script>
