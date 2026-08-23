<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Penerimaan Barang (GR)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Purchase Order (PO) *</label>
                <input type="text" :value="goodsReceipt?.purchase_order?.po_number" class="mt-1 block w-full rounded-md border-gray-200 text-sm bg-gray-100" readonly />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Gudang Tujuan</label>
                <select v-model="form.warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">-- Pilih Gudang --</option>
                  <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Penerimaan *</label>
                <input v-model="form.receipt_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>
            </div>

            <!-- Item Lines dari PO -->
            <div class="mb-6" v-if="selectedPo">
              <h3 class="font-semibold text-md text-gray-700 mb-2">Item Barang Diterima</h3>
              <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nama Barang</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Jumlah PO</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Sudah Diterima</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Jumlah Diterima Saat Ini *</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in form.items" :key="item.purchase_order_item_id">
                    <td class="p-3 text-sm font-medium">{{ item.item_name }}</td>
                    <td class="p-3 text-sm font-mono">{{ item.qty_ordered }}</td>
                    <td class="p-3 text-sm font-mono text-gray-500">{{ item.qty_prev_received }}</td>
                    <td class="p-2">
                      <input v-model.number="item.qty_received" type="number" min="0" :max="item.qty_ordered - item.qty_prev_received" class="w-full rounded-md border-gray-300 text-sm" required />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mb-6">
              <label class="block font-medium text-sm text-gray-700">Catatan</label>
              <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.goods-receipts.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
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
  props: { goodsReceipt: Object, warehouses: Array },
  data() {
    return {
      selectedPo: this.goodsReceipt?.purchase_order ?? null,
      form: this.$inertia.form({
        warehouse_id: this.goodsReceipt?.warehouse_id ? String(this.goodsReceipt.warehouse_id) : '',
        receipt_date: this.goodsReceipt?.receipt_date ?? '',
        notes: this.goodsReceipt?.notes ?? '',
        items: (this.goodsReceipt?.purchase_order?.items ?? []).map(i => ({
          purchase_order_item_id: i.id,
          item_id: i.item_id,
          item_name: i.item?.name ?? '',
          qty_ordered: i.qty,
          qty_prev_received: i.qty_received || 0,
          qty_received: this.goodsReceipt?.items?.find(g => Number(g.purchase_order_item_id) === i.id)?.qty_received ?? 0,
        })),
      }),
    };
  },
  methods: {
    submit() {
      this.form.put(route('apps.goods-receipts.update', this.goodsReceipt.id));
    },
  },
};
</script>
