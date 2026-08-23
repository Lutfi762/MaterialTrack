<template>
  <Head><title>{{ $t('stock_opname.title') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-clipboard-check text-primary"></i>
        {{ $t('stock_opname.heading') }}
      </h2>
      <div class="flex gap-2">
        <a v-if="hasAnyPermission(['stock_opnames.export', 'stock_opnames.index'])" :href="exportExcelUrl" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg"><i class="fa fa-file-excel me-2"></i> Export Excel</a>
        <Link v-if="hasAnyPermission(['stock_opnames.create'])" :href="`/apps/stock-opnames/create?period=${selectedPeriod}`" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]"><i class="fa fa-plus-circle me-2"></i> {{ $t('stock_opname.add') }}</Link>
      </div>
    </div>

    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
      <div class="min-w-48">
        <select v-model="selectedPeriod" class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="changePeriod">
          <option :value="currentPeriod" v-if="!availablePeriods.includes(currentPeriod)">{{ currentPeriod }} ({{ $t('stock_opname.empty') }})</option>
          <option v-for="p in availablePeriods" :key="p" :value="p">{{ p }}</option>
        </select>
      </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
    <DataTable :columns="columns" :rows="opnames.data" :pagination="opnames" row-key="id" :striped="true" :loading="false" :empty-text="$t('stock_opname.empty')" :searchable="false">
      <template #cell-reference_no="{ row }"><code>{{ row.reference_no }}</code></template>
      <template #cell-date="{ row }">{{ formatDate(row.date) }}</template>
      <template #cell-item="{ row }"><div><div class="font-semibold">{{ row.item?.name }}</div><div class="text-xs text-on-surface-variant">{{ row.item?.code }}</div></div></template>
      <template #cell-category="{ row }">{{ row.category?.name || '-' }}</template>
      <template #cell-system_stock="{ row }">{{ row.system_stock ?? '-' }}</template>
      <template #cell-physical_stock="{ row }">{{ row.physical_stock ?? '-' }}</template>
      <template #cell-diff="{ row }">
        <span :class="row.diff > 0 ? 'text-success' : row.diff < 0 ? 'text-danger' : 'text-on-surface-variant'">{{ row.diff > 0 ? '+' : '' }}{{ row.diff ?? 0 }}</span>
      </template>
      <template #cell-notes="{ row }">{{ row.notes ?? '-' }}</template>
      <template #cell-recorded_by="{ row }">{{ row.recorded_by?.name ?? '-' }}</template>
      <template #cell-status="{ row }">
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(row.status)">{{ row.status }}</span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link :href="`/apps/stock-opnames/${row.id}`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-primary hover:bg-primary/10" :aria-label="$t('common.view')"><i class="fa fa-eye"></i></Link>
          <Link v-if="row.status === 'pending'" :href="`/apps/stock-opnames/${row.id}/edit`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-warning hover:bg-warning/10" :aria-label="$t('stock_opname.revision')"><i class="fa fa-edit"></i></Link>
          <form v-if="row.status === 'pending' && hasAnyPermission(['stock_opnames.verify'])" @submit.prevent="verify(row)">
            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-success hover:bg-success/10" :title="$t('stock_opname.verify', 'Verify')"><i class="fa fa-check-double"></i></button>
          </form>
          <a :href="`/apps/stock-opnames/${row.id}/pdf`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-danger hover:bg-danger/10" :aria-label="$t('common.print')" target="_blank"><i class="fa fa-file-pdf"></i></a>
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
      const map = { pending: 'bg-warning/15 text-warning', approved: 'bg-success/15 text-success', rejected: 'bg-danger/15 text-danger' };
      return map[status] || 'bg-bg text-on-surface-variant';
    };
    return { opnames: props.opnames, availablePeriods: props.availablePeriods, currentPeriod: props.currentPeriod, selectedPeriod, exportExcelUrl, columns, changePeriod, verify, formatDate, statusClass };
  },
};
</script>
