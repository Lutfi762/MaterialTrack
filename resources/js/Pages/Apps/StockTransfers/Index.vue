<template>
  <Head><title>{{ $t('stock_transfer.title') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-exchange-alt text-indigo-600"></i>
        {{ $t('stock_transfer.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['stock_ins.create'])"
        href="/apps/stock-transfers/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-1"></i> {{ $t('stock_transfer.add') }}
      </Link>
    </div>

    <div class="filter-bar mb-4">
      <div class="filter-item">
        <i class="fa fa-layer-group text-muted"></i>
        <select v-model="filters.status" class="filter-select" @change="applyFilter">
          <option value="">{{ $t('stock_transfer.filter_all_status') }}</option>
          <option value="pending">{{ $t('stock_transfer.status_pending') }}</option>
          <option value="approved">{{ $t('stock_transfer.status_approved') }}</option>
          <option value="rejected">{{ $t('stock_transfer.status_rejected') }}</option>
        </select>
      </div>
      <div class="filter-item">
        <i class="fa fa-building text-muted"></i>
        <select v-model="filters.department_id" class="filter-select" @change="applyFilter">
          <option value="">{{ $t('stock_transfer.filter_all_dept') }}</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
        </select>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :rows="transfers.data"
      :pagination="transfers"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('stock_transfer.empty')"
    >
      <template #cell-reference_no="{ row }">
        <code>{{ row.reference_no }}</code>
      </template>
      <template #cell-item="{ row }">
        {{ row.item?.name ?? '-' }}
      </template>
      <template #cell-from_department="{ row }">
        {{ row.from_department?.name ?? '-' }}
      </template>
      <template #cell-to_department="{ row }">
        {{ row.to_department?.name ?? '-' }}
      </template>
      <template #cell-qty="{ row }">
        <strong>{{ row.qty }} {{ row.unit }}</strong>
      </template>
      <template #cell-status="{ row }">
        <span class="badge" :class="statusClass(row.status)">{{ row.status }}</span>
      </template>
      <template #cell-created_at="{ row }">
        {{ row.created_at }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex justify-center">
          <Link
            :href="`/apps/stock-transfers/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
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
import { reactive, ref } from "vue";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: {
    transfers: Object,
    departments: Array,
  },
  setup(props) {
    const { t } = useI18n();
    const filters = reactive({
      status: '',
      department_id: '',
    });
    const columns = ref([
      { key: "reference_no", label: t("stock_transfer.col_ref") },
      { key: "item", label: t("stock_transfer.material") },
      { key: "from_department", label: t("stock_transfer.col_from") },
      { key: "to_department", label: t("stock_transfer.col_to") },
      { key: "qty", label: t("stock_transfer.col_qty"), align: "center" },
      { key: "status", label: t("stock_transfer.col_status"), align: "center" },
      { key: "created_at", label: t("stock_transfer.col_date") },
      { key: "actions", label: t("common.action"), align: "center", width: "100px" },
    ]);
    const applyFilter = () => {
      router.get('/apps/stock-transfers', {
        status: filters.status || undefined,
        department_id: filters.department_id || undefined,
      }, { preserveState: true });
    };
    const statusClass = (status) => {
      return {
        pending: 'bg-warning text-dark',
        approved: 'bg-success',
        rejected: 'bg-danger',
      }[status] || 'bg-secondary';
    };
    return { transfers: props.transfers, departments: props.departments, filters, columns, applyFilter, statusClass };
  },
};
</script>
