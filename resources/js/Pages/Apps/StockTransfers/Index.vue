<template>
  <Head><title>{{ $t('stock_transfer.title') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-exchange-alt text-primary"></i>
        {{ $t('stock_transfer.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['stock_ins.create'])"
        href="/apps/stock-transfers/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]"
      >
        <i class="fa fa-plus me-1"></i> {{ $t('stock_transfer.add') }}
      </Link>
    </div>

    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
      <div class="min-w-48">
        <select v-model="filters.status" class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter">
          <option value="">{{ $t('stock_transfer.filter_all_status') }}</option>
          <option value="pending">{{ $t('stock_transfer.status_pending') }}</option>
          <option value="approved">{{ $t('stock_transfer.status_approved') }}</option>
          <option value="rejected">{{ $t('stock_transfer.status_rejected') }}</option>
        </select>
      </div>
      <div class="min-w-48">
        <select v-model="filters.department_id" class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter">
          <option value="">{{ $t('stock_transfer.filter_all_dept') }}</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
        </select>
      </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
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
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(row.status)">{{ row.status }}</span>
      </template>
      <template #cell-created_at="{ row }">
        {{ row.created_at }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex justify-center">
          <Link
            :href="`/apps/stock-transfers/${row.id}`"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-primary hover:bg-primary/10"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
        </div>
      </template>
    </DataTable>
    </div>
    </div>
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
        pending: 'bg-warning/15 text-warning',
        approved: 'bg-success/15 text-success',
        rejected: 'bg-danger/15 text-danger',
      }[status] || 'bg-bg text-on-surface-variant';
    };
    return { transfers: props.transfers, departments: props.departments, filters, columns, applyFilter, statusClass };
  },
};
</script>
