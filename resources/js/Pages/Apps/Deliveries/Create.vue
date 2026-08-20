<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Surat Jalan (Delivery Order)</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Pilih Sales Order (SO) *</label>
                <select v-model="form.sales_order_id" @change="onSoSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                  <option value="">-- Pilih SO --</option>
                  <option v-for="so in salesOrders" :key="so.id" :value="so.id">{{ so.so_number }} - {{ so.customer?.name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Tanggal Pengiriman *</label>
                <input v-model="form.delivery_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Gudang Asal Barang</label>
                <select v-model="form.warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">-- Pilih Gudang --</option>
                  <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Pengemudi / Kurir</label>
                <input v-model="form.driver_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Plat Nomor Kendaraan</label>
                <input v-model="form.vehicle_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>

            <!-- Item Lines dari SO -->
            <div class="mb-6" v-if="selectedSo">
              <h3 class="font-semibold text-md text-gray-700 mb-2">Item Barang Dikirim</h3>
              <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nama Barang</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty SO</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Sudah Dikirim</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Qty Dikirim Saat Ini *</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in form.items" :key="item.sales_order_item_id">
                    <td class="p-3 text-sm font-medium">{{ item.item_name }}</td>
                    <td class="p-3 text-sm font-mono">{{ item.qty_ordered }}</td>
                    <td class="p-3 text-sm font-mono text-gray-500">{{ item.qty_prev_shipped }}</td>
                    <td class="p-2">
                      <input v-model.number="item.qty_shipped" type="number" min="1" :max="item.qty_ordered - item.qty_prev_shipped" class="w-full rounded-md border-gray-300 text-sm" required />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="flex justify-end gap-3">
              <Link :href="route('apps.deliveries.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300">Batal</Link>
              <button type="submit" :disabled="form.processing || !selectedSo" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">Proses Surat Jalan & Kurangi Stok</button>
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
  props: { salesOrders: Array, warehouses: Array },
  data() {
    return {
      selectedSo: null,
      form: this.$inertia.form({
        sales_order_id: '',
        warehouse_id: '',
        delivery_date: new Date().toISOString().substr(0, 10),
        driver_name: '',
        vehicle_number: '',
        notes: '',
        items: [],
      }),
    };
  },
  methods: {
    onSoSelect() {
      this.selectedSo = this.salesOrders.find(s => s.id === this.form.sales_order_id);
      if (this.selectedSo) {
        this.form.items = this.selectedSo.items.map(i => ({
          sales_order_item_id: i.id,
          item_id: i.item_id,
          item_name: i.item?.name,
          qty_ordered: i.qty,
          qty_prev_shipped: i.qty_delivered || 0,
          qty_shipped: i.qty - (i.qty_delivered || 0),
        }));
      } else {
        this.form.items = [];
      }
    },
    submit() {
      this.form.post(route('apps.deliveries.store'));
    },
  },
};
</script>
