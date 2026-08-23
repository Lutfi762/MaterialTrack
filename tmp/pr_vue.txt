<template>
  <Head><title>{{ $t('purchase_requests.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-file-alt text-indigo-600"></i>
        {{ $t('purchase_requests.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['purchase_requests.create'])"
        href="/apps/purchase-requests/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="purchaseRequests.data"
      :pagination="purchaseRequests"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('purchase_requests.empty')"
    >
      <template #cell-pr_number="{ row }">
        <code>{{ row.pr_number }}</code>
      </template>
      <template #cell-department="{ row }">
        {{ row.department?.name || '-' }}
      </template>
      <template #cell-requester="{ row }">
        {{ row.requester?.name || '-' }}
      </template>
      <template #cell-priority="{ row }">
        <span :class="{
          'text-red-600 font-bold': row.priority === 'urgent',
          'text-orange-500': row.priority === 'high',
          'text-blue-600': row.priority === 'medium',
          'text-gray-600': row.priority === 'low'
        }">{{ row.priority }}</span>
      </template>
      <template #cell-status="{ row }">
        <span
          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
          :class="{
            'bg-yellow-100 text-yellow-800': row.status === 'pending',
            'bg-green-100 text-green-800': row.status === 'approved',
            'bg-red-100 text-red-800': row.status === 'rejected'
          }"
        >
          {{ row.status }}
        </span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            :href="`/apps/purchase-requests/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
          <Link
            v-if="hasAnyPermission(['purchase_requests.edit'])"
            :href="`/apps/purchase-requests/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['purchase_requests.delete'])"
            class="act-btn act-btn-danger"
            :aria-label="$t('common.delete')"
            @click="remove(row)"
          >
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>
  </main>
</template>

<script>
import LayoutApp from "@/Layouts/App.vue";
import DataTable from "@/Components/DataTable.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { confirmDelete } from "@/Utils/confirmDialog";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { purchaseRequests: Object, filters: Object },
  setup(props) {
    const { t } = useI18n();
    const columns = ref([
      { key: "pr_number", label: t("purchase_requests.col_pr") },
      { key: "department", label: t("purchase_requests.col_department") },
      { key: "requester", label: t("purchase_requests.col_requester") },
      { key: "priority", label: t("purchase_requests.col_priority"), align: "center" },
      { key: "status", label: t("purchase_requests.col_status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);

    const remove = async (row) => {
      if (await confirmDelete(t("purchase_requests.delete_confirm"))) {
        router.delete(`/apps/purchase-requests/${row.id}`);
      }
    };

    return { remove, columns };
  },
};
</script>
