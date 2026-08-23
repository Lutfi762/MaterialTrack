<template>
  <Head><title>{{ $t('stock_adjustments.title') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-balance-scale text-primary"></i>
        {{ $t('stock_adjustments.title') }}
      </h2>
      <div class="flex gap-2">
        <a v-if="hasAnyPermission(['activity_logs.index'])" href="/apps/activity-logs" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg"><i class="fa fa-history me-1"></i> Audit Trail</a>
        <Link v-if="hasAnyPermission(['stock_adjustments.create'])" href="/apps/stock-adjustments/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]"><i class="fa fa-plus-circle me-2"></i> {{ $t('stock_adjustments.add') }}</Link>
      </div>
    </div>

    <div v-if="pendingCount > 0 && hasAnyPermission(['stock_adjustments.approve'])" class="mb-4 rounded-lg border border-warning/30 bg-warning/10 px-4 py-2 text-sm text-warning"><i class="fa fa-clock me-1"></i> {{ pendingCount }} penyesuaian menunggu persetujuan.</div>

    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
      <select v-model="filters.status" class="min-w-40 rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"><option value="">{{ $t('common.all') }}</option><option value="pending">{{ $t('stock_adjustments.status_pending') }}</option><option value="approved">{{ $t('stock_adjustments.status_approved') }}</option><option value="rejected">{{ $t('stock_adjustments.status_rejected') }}</option></select>
      <select v-model="filters.item_id" class="min-w-48 rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"><option value="">{{ $t('common.all') }}</option><option v-for="item in items" :key="item.id" :value="item.id">{{ item.code }} - {{ item.name }}</option></select>
      <input type="date" v-model="filters.date_from" class="rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
      <input type="date" v-model="filters.date_to" class="rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
      <div class="flex gap-2">
        <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white dark:text-[#1000a9]" @click="applyFilter"><i class="fa fa-filter me-2"></i>{{ $t('common.apply') }}</button>
        <button class="rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg" @click="resetFilter"><i class="fa fa-undo"></i></button>
      </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
    <DataTable :columns="columns" :rows="adjustments.data" :pagination="adjustments" row-key="id" :striped="true" :loading="false" :empty-text="$t('stock_adjustments.empty')" :searchable="false">
      <template #cell-reference_no="{ row }"><code>{{ row.reference_no }}</code></template>
      <template #cell-created_at="{ row }">{{ formatDate(row.created_at) }}</template>
      <template #cell-item="{ row }"><div><div class="font-semibold">{{ row.item?.name }}</div><div class="text-xs text-on-surface-variant">{{ row.item?.code }}</div></div></template>
      <template #cell-qty="{ row }">
        <span :class="row.qty > 0 ? 'font-bold text-success' : 'font-bold text-danger'">{{ row.qty > 0 ? '+' : '' }}{{ row.qty }} <span class="text-xs text-on-surface-variant">{{ row.item?.unit }}</span></span>
      </template>
      <template #cell-reason="{ row }"><span class="inline-block truncate" :title="row.reason">{{ row.reason }}</span></template>
      <template #cell-user="{ row }">{{ row.user?.name ?? '-' }}</template>
      <template #cell-status="{ row }">
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(row.status)">{{ row.status }}</span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <template v-if="row.status === 'pending'">
            <form v-if="hasAnyPermission(['stock_adjustments.approve'])" @submit.prevent="approve(row)"><button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-success hover:bg-success/10" :title="$t('common.approve')"><i class="fa fa-check"></i></button></form>
            <form v-if="hasAnyPermission(['stock_adjustments.approve'])" @submit.prevent="reject(row)"><button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-danger hover:bg-danger/10" :title="$t('common.reject')"><i class="fa fa-times"></i></button></form>
            <Link v-if="hasAnyPermission(['stock_adjustments.create'])" :href="`/apps/stock-adjustments/${row.id}/edit`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-warning hover:bg-warning/10"><i class="fa fa-edit"></i></Link>
          </template>
          <a v-else :href="`/apps/stock-adjustments/${row.id}/pdf`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-danger hover:bg-danger/10" :aria-label="$t('common.print')" target="_blank"><i class="fa fa-file-pdf"></i></a>
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
import { ref, reactive } from "vue";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { adjustments: Object, items: Array, filters: Object, pendingCount: Number },
  setup(props) {
    const { t } = useI18n();
    const filterForm = reactive({
      status: props.filters?.status || '',
      item_id: props.filters?.item_id || '',
      date_from: props.filters?.date_from || '',
      date_to: props.filters?.date_to || '',
    });
    const columns = ref([
      { key: "reference_no", label: t("stock_adjustments.col_ref") },
      { key: "created_at", label: t("stock_adjustments.col_date") },
      { key: "item", label: t("stock_adjustments.col_item") },
      { key: "qty", label: t("stock_adjustments.col_qty"), align: "center" },
      { key: "reason", label: t("stock_adjustments.col_reason") },
      { key: "user", label: t("stock_adjustments.col_by") },
      { key: "status", label: t("stock_adjustments.col_status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "180px" },
    ]);
    const applyFilter = () => { router.get('/apps/stock-adjustments', filterForm, { preserveState: true }); };
    const resetFilter = () => {
      filterForm.status = ''; filterForm.item_id = ''; filterForm.date_from = ''; filterForm.date_to = '';
      router.get('/apps/stock-adjustments');
    };
    const approve = (adj) => {
            if (confirm(t("stock_adjustments.approve_confirm"))) {
        router.patch(`/apps/stock-adjustments/${adj.id}/approve`);
      }
    };
    const reject = (adj) => {
      if (confirm(t("stock_adjustments.reject_confirm", "Reject this adjustment?"))) {
        router.patch(`/apps/stock-adjustments/${adj.id}/reject`);
      }
    };
    const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const statusClass = (s) => ({ pending: 'bg-warning/15 text-warning', approved: 'bg-success/15 text-success', rejected: 'bg-danger/15 text-danger' }[s] || 'bg-bg text-on-surface-variant');
    return { adjustments: props.adjustments, items: props.items, filters: filterForm, pendingCount: props.pendingCount, columns, applyFilter, resetFilter, approve, reject, formatDate, statusClass };
  },
};
</script>
