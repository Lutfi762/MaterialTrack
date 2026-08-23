<template>
  <AppLayout>
    <Head title="Approval Workflows" />
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Alur Persetujuan (Approval Workflows)</h2>
        <Link v-if="hasAnyPermission(['approval_workflows.create'])" :href="route('apps.approval-workflows.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
          + Buat Workflow Baru
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Pending requests -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Permintaan Persetujuan Menunggu</h3>
          <template v-if="pendingRequests.length > 0">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modul</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengaju</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workflow</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahap Saat Ini</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diajukan</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="r in pendingRequests" :key="r.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm capitalize font-mono">{{ r.approvable_type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ r.requester?.name ?? '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ r.workflow?.name ?? '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ r.current_step }} / {{ r.steps?.length || 1 }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ new Date(r.created_at).toLocaleDateString('id-ID') }}</td>
                </tr>
              </tbody>
            </table>
          </template>
          <p v-else class="text-sm text-gray-500">Tidak ada permintaan persetujuan yang menunggu.</p>
        </div>

        <!-- Workflow master -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="font-bold text-gray-700 mb-4">Daftar Workflow Aktif</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Workflow</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modul Target</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batas Nominal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahapan Approver</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="w in workflows" :key="w.id">
                <td class="px-6 py-4 text-sm font-bold align-top">
                  {{ w.name }}
                  <span v-if="w.description" class="block text-xs font-normal text-gray-500 mt-1">{{ w.description }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize font-mono align-top">{{ w.module }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono align-top">
                  Rp {{ Number(w.min_amount || 0).toLocaleString('id-ID') }} - {{ w.max_amount ? 'Rp ' + Number(w.max_amount).toLocaleString('id-ID') : 'Tak terbatas' }}
                </td>
                <td class="px-6 py-4 text-sm align-top">
                  <ol class="list-decimal list-inside space-y-1">
                    <li v-for="s in w.steps" :key="s.id">
                      <span class="font-medium">{{ stepLabel(s) }}:</span>
                      <span class="text-gray-600">{{ s.approver_value || 'Atasan Langsung' }}</span>
                    </li>
                  </ol>
                </td>
              </tr>
              <tr v-if="workflows.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada workflow approval.</td>
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
import { Head, Link } from '@inertiajs/vue3';

export default {
  components: { AppLayout, Head, Link },
  props: { workflows: Array, pendingRequests: Array },
  methods: {
    stepLabel(step) {
      const labels = { user: 'User', role: 'Role', manager: 'Manager' };
      return labels[step.approver_type] || step.approver_type;
    },
  },
};
</script>
