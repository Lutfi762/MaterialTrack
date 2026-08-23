<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Penawaran Penjualan (Quotation)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Pelanggan (Customer) *</label>
                <select v-model="form.customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="">-- Pilih Pelanggan --</option>
                  <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.code }})</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Berlaku Sampai Tanggal</label>
                <input v-model="form.valid_until" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>

            <div class="mb-6">
              <h3 class="font-semibold text-md text-gray-700 mb-2">Item Penawaran</h3>
              <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Barang *</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-32">Qty *</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Harga Penawaran *</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 w-44">Subtotal</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 w-16">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in form.items" :key="index">
                    <td class="p-2">
                      <select v-model="row.item_id" @change="onItemChange(row)" class="w-full rounded-md border-gray-300 text-sm" required>
                        <option value="">-- Pilih Barang --</option>
                        <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }} ({{ item.code }})</option>
                      </select>
                    </td>
                    <td class="p-2">
                      <input v-model.number="row.qty" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm" required />
                    </td>
                    <td class="p-2">
                      <input v-model.number="row.unit_price" type="number" min="0" class="w-full rounded-md border-gray-300 text-sm" required />
                    </td>
                    <td class="p-2 text-right font-mono text-sm">
                      Rp {{ Number(row.qty * row.unit_price).toLocaleString('id-ID') }}
                    </td>
                    <td class="p-2 text-center">
                      <button @click.prevent="removeItem(index)" class="text-red-600 font-bold hover:text-red-800" :disabled="form.items.length === 1">✕</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <button @click.prevent="addItem" class="mt-2 text-sm text-indigo-600 font-semibold hover:text-indigo-800">+ Tambah Baris</button>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.quotations.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
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
  props: { quotation: Object, customers: Array, items: Array },
  data() {
    return {
      form: this.$inertia.form({
        customer_id: this.quotation?.customer_id ? String(this.quotation.customer_id) : '',
        valid_until: this.quotation?.valid_until ?? '',
        notes: this.quotation?.notes ?? '',
        items: (this.quotation?.items ?? [{ item_id: '', qty: 1, unit_price: 0 }]).map(i => ({
          item_id: i.item_id ? String(i.item_id) : '',
          qty: i.qty,
          unit_price: i.unit_price ?? 0,
        })),
      }),
    };
  },
  methods: {
    onItemChange(row) {
      const selected = this.items.find(i => i.id === row.item_id);
      if (selected) {
        row.unit_price = selected.price || 0;
      }
    },
    addItem() {
      this.form.items.push({ item_id: '', qty: 1, unit_price: 0 });
    },
    removeItem(index) {
      if (this.form.items.length > 1) {
        this.form.items.splice(index, 1);
      }
    },
    submit() {
      this.form.put(route('apps.quotations.update', this.quotation.id));
    },
  },
};
</script>
