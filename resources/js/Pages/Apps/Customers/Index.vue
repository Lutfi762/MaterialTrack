<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Pelanggan (Customers)</h2>
        <Link :href="route('apps.customers.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Tambah Pelanggan
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div class="mb-4 flex justify-between items-center">
            <input v-model="search" @input="doSearch" type="text" placeholder="Cari pelanggan..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-64" />
          </div>

          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak / Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batas Kredit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="c in customers.data" :key="c.id">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ c.code || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ c.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ c.contact_person || '-' }} <br><span class="text-xs text-gray-400">{{ c.email }}</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ c.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">Rp {{ Number(c.credit_limit || 0).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.customers.edit', c.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                  <button @click="destroy(c.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                </td>
              </tr>
              <tr v-if="customers.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data pelanggan.</td>
              </tr>
            </tbody>
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
  props: {
    customers: Object,
    filters: Object,
  },
  data() {
    return {
      search: this.filters.search || '',
    };
  },
  methods: {
    doSearch() {
      this.$inertia.get(route('apps.customers.index'), { search: this.search }, { preserveState: true, replace: true });
    },
    destroy(id) {
      if (confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')) {
        this.$inertia.delete(route('apps.customers.destroy', id));
      }
    },
  },
};
</script>
