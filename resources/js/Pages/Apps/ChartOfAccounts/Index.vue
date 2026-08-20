<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bagan Akun (Chart of Accounts / COA)</h2>
        <Link :href="route('apps.chart-of-accounts.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Tambah Akun
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Akun</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sub Tipe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo Awal</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="acc in accounts.data" :key="acc.id" :class="{'bg-gray-50 font-bold': acc.is_header}">
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ acc.code }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="{'pl-8': !acc.is_header}">{{ acc.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ acc.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-gray-600">{{ acc.sub_type }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">Rp {{ Number(acc.opening_balance || 0).toLocaleString('id-ID') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('apps.chart-of-accounts.edit', acc.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                  <button @click="destroy(acc.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                </td>
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
  props: { accounts: Object },
  methods: {
    destroy(id) {
      if (confirm('Hapus akun ini?')) {
        this.$inertia.delete(route('apps.chart-of-accounts.destroy', id));
      }
    },
  },
};
</script>
