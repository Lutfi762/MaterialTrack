<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Jurnal: {{ journalEntry.entry_number }}</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><span class="text-xs text-gray-500 block">Tanggal:</span> <strong>{{ journalEntry.entry_date }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Pembuat:</span> <strong>{{ journalEntry.creator?.name }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Status:</span> <strong class="capitalize text-green-600">{{ journalEntry.status }}</strong></div>
          <div><span class="text-xs text-gray-500 block">Narasi:</span> <strong>{{ journalEntry.narration || '-' }}</strong></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Rincian Akun</h3>
          <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Akun</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit (Rp)</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kredit (Rp)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="line in journalEntry.lines" :key="line.id">
                <td class="px-6 py-4 text-sm font-mono font-bold">{{ line.account?.code }}</td>
                <td class="px-6 py-4 text-sm">{{ line.account?.name }}</td>
                <td class="px-6 py-4 text-sm text-right font-mono">{{ line.debit > 0 ? 'Rp ' + Number(line.debit).toLocaleString('id-ID') : '-' }}</td>
                <td class="px-6 py-4 text-sm text-right font-mono">{{ line.credit > 0 ? 'Rp ' + Number(line.credit).toLocaleString('id-ID') : '-' }}</td>
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

export default {
  components: { AppLayout },
  props: { journalEntry: Object },
};
</script>
