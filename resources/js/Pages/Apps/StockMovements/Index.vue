<template>
  <Head><title>{{ $t('stock_movements.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-clock-rotate-left text-indigo-600"></i>
        {{ $t('stock_movements.heading') }}
      </h2>
      <div class="d-flex gap-2">
        <a v-if="hasAnyPermission(['stock_movements.export', 'stock_movements.index'])" :href="exportExcelUrl" class="btn-modern btn-modern-outline"><i class="fa fa-file-excel me-2"></i> Export Excel</a>
        <a v-if="hasAnyPermission(['stock_movements.export', 'stock_movements.index'])" :href="downloadAllUrl" class="btn-modern btn-modern-outline"><i class="fa fa-file-pdf me-2"></i> {{ $t('common.download_all_pdf') }}</a>
      </div>
    </div>

    <div class="filter-bar mb-4">
      <div class="filter-item"><i class="fa fa-building text-muted"></i><select v-model="filterForm.department_id" class="filter-select" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_dept') }}</option><option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option></select></div>
      <div class="filter-item"><i class="fa fa-box text-muted"></i><select v-model="filterForm.item_id" class="filter-select" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_material') }}</option><option v-for="item in filteredItems" :key="item.id" :value="item.id">[{{ item.code }}] {{ item.name }}</option></select></div>
      <div class="filter-item"><i class="fa fa-filter text-muted"></i><select v-model="filterForm.type" class="filter-select" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_type') }}</option><option value="in">{{ $t('stock_movements.type_in') }}</option><option value="out">{{ $t('stock_movements.type_out') }}</option><option value="opname">{{ $t('stock_movements.type_opname') }}</option><option value="adjustment">{{ $t('stock_movements.type_adjustment') }}</option></select></div>
      <div class="filter-item"><i class="fa fa-user text-muted"></i><select v-model="filterForm.user_id" class="filter-select" @change="applyFilter"><option value="">{{ $t('stock_movements.filter_all_user') }}</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select></div>
      <div class="filter-item"><i class="fa fa-calendar text-muted"></i><input type="date" v-model="filterForm.date_from" class="filter-select" @change="applyFilter"></div>
      <div class="filter-item"><span class="text-muted small">{{ $t('stock_movements.date_to_placeholder') }}</span><input type="date" v-model="filterForm.date_to" class="filter-select" @change="applyFilter"></div>
    </div>

    <DataTable :columns="columns" :rows="movements.data" :pagination="movements" row-key="id" :striped="true" :loading="false" :empty-text="$t('stock_movements.empty')" :searchable="false">
      <template #cell-created_at="{ row }">{{ row.created_at }}</template>
      <template #cell-item="{ row }"><div><div class="fw-semibold">{{ row.item?.name }}</div><div class="text-muted small">{{ row.item?.code }}</div></div></template>
      <template #cell-type="{ row }">
        <span v-if="row.type === 'in'" class="badge bg-success">⬇ {{ $t('stock_movements.type_in') }}</span>
        <span v-else-if="row.type === 'out'" class="badge bg-danger">⬆ {{ $t('stock_movements.type_out') }}</span>
        <span v-else-if="row.type === 'opname'" class="badge bg-primary">📋 {{ $t('stock_movements.type_opname') }}</span>
        <span v-else class="badge bg-warning text-dark">🔧 {{ $t('stock_movements.type_adjustment') }}</span>
      </template>
      <template #cell-qty="{ row }"><strong>{{ row.qty }}</strong></template>
      <template #cell-stock_before="{ row }">{{ row.stock_before ?? '-' }}</template>
      <template #cell-balance_after="{ row }">{{ row.balance_after ?? '-' }}</template>
      <template #cell-notes="{ row }">{{ row.notes ?? '-' }}</template>
      <template #cell-pic="{ row }">{{ row.pic?.name ?? row.user?.name ?? '-' }}</template>
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
