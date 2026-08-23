<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Faktur Pembelian (Purchase Invoice)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Pemasok / Supplier *</label>
                <input type="text" :value="invoice?.purchase_order?.supplier?.name" class="mt-1 block w-full rounded-md border-gray-200 text-sm bg-gray-100" readonly />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">No. PO *</label>
                <input type="text" :value="invoice?.purchase_order?.po_number" class="mt-1 block w-full rounded-md border-gray-200 text-sm bg-gray-100" readonly />
              </div>

              <div></div>

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

            <div class="mb-6">
              <label class="block font-medium text-sm text-gray-700">Catatan</label>
              <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.purchase-invoices.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Simpan Perubahan</button>
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
  props: { invoice: Object },
  data() {
    return {
      form: this.$inertia.form({
        supplier_invoice_no: this.invoice?.supplier_invoice_no ?? '',
        invoice_date: this.invoice?.invoice_date ?? '',
        due_date: this.invoice?.due_date ?? '',
        notes: this.invoice?.notes ?? '',
      }),
    };
  },
  methods: {
    submit() {
      this.form.put(route('apps.purchase-invoices.update', this.invoice.id));
    },
  },
};
</script>
