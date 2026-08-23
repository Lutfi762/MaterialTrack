<template>
  <Head><title>{{ $t('stock_opname.title') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-clipboard-check text-indigo-600"></i>
        {{ $t('stock_opname.heading') }}
      </h2>
      <div class="d-flex gap-2">
        <a v-if="hasAnyPermission(['stock_opnames.export', 'stock_opnames.index'])" :href="exportExcelUrl" class="btn-modern btn-modern-outline"><i class="fa fa-file-excel me-2"></i> Export Excel</a>
        <Link v-if="hasAnyPermission(['stock_opnames.create'])" :href="`/apps/stock-opnames/create?period=${selectedPeriod}`" class="btn-modern btn-modern-primary"><i class="fa fa-plus-circle me-2"></i> {{ $t('stock_opname.add') }}</Link>
      </div>
    </div>

    <div class="filter-bar mb-4">
      <div class="filter-item">
        <i class="fa fa-calendar text-muted"></i>
        <select v-model="selectedPeriod" class="filter-select" @change="changePeriod">
          <option :value="currentPeriod" v-if="!availablePeriods.includes(currentPeriod)">{{ currentPeriod }} ({{ $t('stock_opname.empty') }})</option>
          <option v-for="p in availablePeriods" :key="p" :value="p">{{ p }}</option>
        </select>
      </div>
    </div>

    <DataTable :columns="columns" :rows="opnames.data" :pagination="opnames" row-key="id" :striped="true" :loading="false" :empty-text="$t('stock_opname.empty')" :searchable="false">
      <template #cell-reference_no="{ row }"><code>{{ row.reference_no }}</code></template>
      <template #cell-date="{ row }">{{ formatDate(row.date) }}</template>
      <template #cell-item="{ row }"><div><div class="fw-semibold">{{ row.item?.name }}</div><div class="text-muted small">{{ row.item?.code }}</div></div></template>
      <template #cell-category="{ row }">{{ row.category?.name || '-' }}</template>
      <template #cell-system_stock="{ row }">{{ row.system_stock ?? '-' }}</template>
      <template #cell-physical_stock="{ row }">{{ row.physical_stock ?? '-' }}</template>
      <template #cell-diff="{ row }">
        <span :class="row.diff > 0 ? 'text-green-600' : row.diff < 0 ? 'text-red-600' : 'text-gray-600'">{{ row.diff > 0 ? '+' : '' }}{{ row.diff ?? 0 }}</span>
      </template>
      <template #cell-notes="{ row }">{{ row.notes ?? '-' }}</template>
      <template #cell-recorded_by="{ row }">{{ row.recorded_by?.name ?? '-' }}</template>
      <template #cell-status="{ row }">
        <span class="px-2 py-1 text-xs rounded-full font-bold" :class="statusClass(row.status)">{{ row.status }}</span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link :href="`/apps/stock-opnames/${row.id}`" class="act-btn act-btn-neutral" :aria-label="$t('common.view')"><i class="fa fa-eye"></i></Link>
          <Link v-if="row.status === 'pending'" :href="`/apps/stock-opnames/${row.id}/edit`" class="act-btn act-btn-warning" :aria-label="$t('stock_opname.revision')"><i class="fa fa-edit"></i></Link>
          <form v-if="row.status === 'pending' && hasAnyPermission(['stock_opnames.verify'])" @submit.prevent="verify(row)">
            <button type="submit" class="act-btn act-btn-success" :title="$t('stock_opname.verify', 'Verify')"><i class="fa fa-check-double"></i></button>
          </form>
          <a :href="`/apps/stock-opnames/${row.id}/pdf`" class="act-btn act-btn-danger" :aria-label="$t('common.print')" target="_blank"><i class="fa fa-file-pdf"></i></a>
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
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: {
    opnames: Object,
    availablePeriods: Array,
    currentPeriod: String,
  },
  setup(props) {
    const { t } = useI18n();
    const selectedPeriod = ref(props.currentPeriod);
    const exportExcelUrl = `/apps/stock-opnames/export/excel?period=${props.currentPeriod}`;
    const columns = ref([
      { key: "reference_no", label: "Ref" },
      { key: "date", label: t("stock_opname.col_date") },
      { key: "item", label: t("stock_opname.col_material") },
      { key: "category", label: t("stock_opname.col_category") },
      { key: "system_stock", label: t("stock_opname.col_system_stock"), align: "center" },
      { key: "physical_stock", label: t("stock_opname.col_physical_stock"), align: "center" },
      { key: "diff", label: t("stock_opname.col_diff"), align: "center" },
      { key: "notes", label: t("stock_opname.col_notes") },
      { key: "recorded_by", label: t("stock_opname.col_by") },
      { key: "status", label: t("common.status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);
    const changePeriod = () => {
      router.get('/apps/stock-opnames', { period: selectedPeriod.value }, { preserveState: true });
    };
    const verify = (op) => {
      if (confirm(`Verifikasi opname ${op.reference_no}?`)) {
        router.patch(`/apps/stock-opnames/${op.id}/verify`);
      }
    };
    const formatDate = (dateStr) => {
      return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };
    const statusClass = (status) => {
      const map = { pending: 'bg-yellow-100 text-yellow-800', approved: 'bg-green-100 text-green-800', rejected: 'bg-red-100 text-red-800' };
      return map[status] || 'bg-secondary';
    };
    return { opnames: props.opnames, availablePeriods: props.availablePeriods, currentPeriod: props.currentPeriod, selectedPeriod, exportExcelUrl, columns, changePeriod, verify, formatDate, statusClass };
  },
};
</script>
