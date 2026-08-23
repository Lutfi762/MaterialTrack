<template>
  <Head><title>{{ $t('stock_movements.heading') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-clock-rotate-left text-primary"></i>
        {{ $t('stock_movements.heading') }}
      </h2>
      <div class="flex gap-2">
        <a v-if="hasAnyPermission(['stock_movements.export', 'stock_movements.index'])" :href="exportExcelUrl" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg"><i class="fa fa-file-excel me-2"></i> Export Excel</a>
        <a v-if="hasAnyPermission(['stock_movements.export', 'stock_movements.index'])" :href="downloadAllUrl" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg"><i class="fa fa-file-pdf me-2"></i> {{ $t('common.download_all_pdf') }}</a>
      </div>
    </div>

    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
      <select v-model="filterForm.department_id" class="min-w-40 rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_dept') }}</option><option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option></select>
      <select v-model="filterForm.item_id" class="min-w-48 rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_material') }}</option><option v-for="item in filteredItems" :key="item.id" :value="item.id">[{{ item.code }}] {{ item.name }}</option></select>
      <select v-model="filterForm.type" class="min-w-40 rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_type') }}</option><option value="in">{{ $t('stock_movements.type_in') }}</option><option value="out">{{ $t('stock_movements.type_out') }}</option><option value="opname">{{ $t('stock_movements.type_opname') }}</option><option value="adjustment">{{ $t('stock_movements.type_adjustment') }}</option></select>
      <select v-model="filterForm.user_id" class="min-w-40 rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_user') }}</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
      <input type="date" v-model="filterForm.date_from" class="rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter">
      <div class="flex items-center gap-2">
        <span class="text-xs text-on-surface-variant">{{ $t('stock_movements.date_to_placeholder') }}</span>
        <input type="date" v-model="filterForm.date_to" class="rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" @change="applyFilter">
      </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
    <DataTable :columns="columns" :rows="movements.data" :pagination="movements" row-key="id" :striped="true" :loading="false" :empty-text="$t('stock_movements.empty')" :searchable="false">
      <template #cell-created_at="{ row }">{{ row.created_at }}</template>
      <template #cell-item="{ row }"><div><div class="font-semibold">{{ row.item?.name }}</div><div class="text-xs text-on-surface-variant">{{ row.item?.code }}</div></div></template>
      <template #cell-type="{ row }">
        <span v-if="row.type === 'in'" class="inline-flex rounded-full bg-success/15 px-2.5 py-1 text-xs font-semibold text-success">⬇ {{ $t('stock_movements.type_in') }}</span>
        <span v-else-if="row.type === 'out'" class="inline-flex rounded-full bg-danger/15 px-2.5 py-1 text-xs font-semibold text-danger">⬆ {{ $t('stock_movements.type_out') }}</span>
        <span v-else-if="row.type === 'opname'" class="inline-flex rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-primary">📋 {{ $t('stock_movements.type_opname') }}</span>
        <span v-else class="inline-flex rounded-full bg-warning/15 px-2.5 py-1 text-xs font-semibold text-warning">🔧 {{ $t('stock_movements.type_adjustment') }}</span>
      </template>
      <template #cell-qty="{ row }"><strong>{{ row.qty }}</strong></template>
      <template #cell-stock_before="{ row }">{{ row.stock_before ?? '-' }}</template>
      <template #cell-balance_after="{ row }">{{ row.balance_after ?? '-' }}</template>
      <template #cell-notes="{ row }">{{ row.notes ?? '-' }}</template>
      <template #cell-pic="{ row }">{{ row.pic?.name ?? row.user?.name ?? '-' }}</template>
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
    movements: Object,
    departments: Array,
    items: Array,
    users: Array,
    filters: Object,
  },
  setup(props) {
    const { t } = useI18n();
    const filterForm = ref({
      department_id: props.filters?.department_id ?? '',
      item_id: props.filters?.item_id ?? '',
      type: props.filters?.type ?? '',
      user_id: props.filters?.user_id ?? '',
      date_from: props.filters?.date_from ?? '',
      date_to: props.filters?.date_to ?? '',
    });
    const filteredItems = computed(() => {
      if (!filterForm.value.department_id) return props.items;
      const deptId = Number(filterForm.value.department_id);
      return props.items.filter(item => (item.department_ids ?? []).includes(deptId));
    });
    const downloadAllUrl = computed(() => {
      const params = new URLSearchParams();
      Object.entries(filterForm.value).forEach(([k, v]) => { if (v) params.append(k, v); });
      const qs = params.toString();
      return `/apps/stock-movements/download-all/pdf${qs ? '?' + qs : ''}`;
    });
    const exportExcelUrl = computed(() => {
      const params = new URLSearchParams();
      Object.entries(filterForm.value).forEach(([k, v]) => { if (v) params.append(k, v); });
      const qs = params.toString();
      return `/apps/stock-movements/export/excel${qs ? '?' + qs : ''}`;
    });
    const columns = ref([
      { key: "created_at", label: t("stock_movements.col_date") },
      { key: "item", label: t("stock_movements.col_material") },
      { key: "type", label: t("stock_movements.col_type") },
      { key: "qty", label: t("stock_movements.col_qty"), align: "center" },
      { key: "stock_before", label: t("stock_movements.col_before"), align: "center" },
      { key: "balance_after", label: t("stock_movements.col_after"), align: "center" },
      { key: "notes", label: t("stock_movements.col_notes") },
      { key: "pic", label: t("stock_movements.col_by") },
    ]);
    const applyFilter = () => {
      router.get('/apps/stock-movements', filterForm.value, { preserveState: true });
    };
    const resetFilter = () => {
      filterForm.value = { department_id: '', item_id: '', type: '', user_id: '', date_from: '', date_to: '' };
      router.get('/apps/stock-movements');
    };
    return {
      movements: props.movements, departments: props.departments, users: props.users,
      filterForm, filteredItems, downloadAllUrl, exportExcelUrl, columns, applyFilter, resetFilter,
    };
  },
};
</script>
