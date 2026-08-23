<template>
  <Head><title>{{ $t('stock_adjustments.title') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-balance-scale text-indigo-600"></i>
        {{ $t('stock_adjustments.title') }}
      </h2>
      <div class="d-flex gap-2">
        <a v-if="hasAnyPermission(['activity_logs.index'])" href="/apps/activity-logs" class="btn-modern btn-modern-outline"><i class="fa fa-history me-1"></i> Audit Trail</a>
        <Link v-if="hasAnyPermission(['stock_adjustments.create'])" href="/apps/stock-adjustments/create" class="btn-modern btn-modern-primary"><i class="fa fa-plus-circle me-2"></i> {{ $t('stock_adjustments.add') }}</Link>
      </div>
    </div>

    <div v-if="pendingCount > 0 && hasAnyPermission(['stock_adjustments.approve'])" class="alert alert-warning py-2 mb-4"><i class="fa fa-clock me-1"></i> {{ pendingCount }} penyesuaian menunggu persetujuan.</div>

    <div class="filter-bar mb-4">
      <div class="filter-item"><i class="fa fa-layer-group text-muted"></i><select v-model="filters.status" class="filter-select"><option value="">{{ $t('common.all') }}</option><option value="pending">{{ $t('stock_adjustments.status_pending') }}</option><option value="approved">{{ $t('stock_adjustments.status_approved') }}</option><option value="rejected">{{ $t('stock_adjustments.status_rejected') }}</option></select></div>
      <div class="filter-item"><i class="fa fa-box text-muted"></i><select v-model="filters.item_id" class="filter-select"><option value="">{{ $t('common.all') }}</option><option v-for="item in items" :key="item.id" :value="item.id">{{ item.code }} - {{ item.name }}</option></select></div>
      <div class="filter-item"><i class="fa fa-calendar text-muted"></i><input type="date" v-model="filters.date_from" class="filter-select"></div>
      <div class="filter-item"><span class="text-muted small">{{ $t('common.to') }}</span><input type="date" v-model="filters.date_to" class="filter-select"></div>
      <div class="filter-item"><button class="btn-modern btn-modern-primary" @click="applyFilter"><i class="fa fa-filter me-2"></i>{{ $t('common.apply') }}</button><button class="btn-modern btn-modern-outline" @click="resetFilter"><i class="fa fa-undo"></i></button></div>
    </div>

    <DataTable :columns="columns" :rows="adjustments.data" :pagination="adjustments" row-key="id" :striped="true" :loading="false" :empty-text="$t('stock_adjustments.empty')" :searchable="false">
      <template #cell-reference_no="{ row }"><code>{{ row.reference_no }}</code></template>
      <template #cell-created_at="{ row }">{{ formatDate(row.created_at) }}</template>
      <template #cell-item="{ row }"><div><div class="fw-semibold">{{ row.item?.name }}</div><div class="text-muted small">{{ row.item?.code }}</div></div></template>
      <template #cell-qty="{ row }">
        <span :class="row.qty > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'">{{ row.qty > 0 ? '+' : '' }}{{ row.qty }} <span class="text-muted small">{{ row.item?.unit }}</span></span>
      </template>
      <template #cell-reason="{ row }"><span class="d-inline-block text-truncate" :title="row.reason">{{ row.reason }}</span></template>
      <template #cell-user="{ row }">{{ row.user?.name ?? '-' }}</template>
      <template #cell-status="{ row }">
        <span class="px-2 py-1 text-xs rounded-full font-bold" :class="statusClass(row.status)">{{ row.status }}</span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <template v-if="row.status === 'pending'">
            <form v-if="hasAnyPermission(['stock_adjustments.approve'])" @submit.prevent="approve(row)"><button type="submit" class="act-btn act-btn-success" :title="$t('common.approve')"><i class="fa fa-check"></i></button></form>
            <form v-if="hasAnyPermission(['stock_adjustments.approve'])" @submit.prevent="reject(row)"><button type="submit" class="act-btn act-btn-danger" :title="$t('common.reject')"><i class="fa fa-times"></i></button></form>
            <Link v-if="hasAnyPermission(['stock_adjustments.create'])" :href="`/apps/stock-adjustments/${row.id}/edit`" class="act-btn act-btn-warning"><i class="fa fa-edit"></i></Link>
          </template>
          <a v-else :href="`/apps/stock-adjustments/${row.id}/pdf`" class="act-btn act-btn-danger" :aria-label="$t('common.print')" target="_blank"><i class="fa fa-file-pdf"></i></a>
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
    const statusClass = (s) => ({ pending: 'bg-yellow-100 text-yellow-800', approved: 'bg-green-100 text-green-800', rejected: 'bg-red-100 text-red-800' }[s] || 'bg-secondary');
    return { adjustments: props.adjustments, items: props.items, filters: filterForm, pendingCount: props.pendingCount, columns, applyFilter, resetFilter, approve, reject, formatDate, statusClass };
  },
};
</script>
