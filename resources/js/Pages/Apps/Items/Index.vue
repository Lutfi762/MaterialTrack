<template>
  <Head>
    <title>{{ $t('items.title') }} - MaterialTrack</title>
  </Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Inventory</p>
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-box text-primary" aria-hidden="true"></i>
        {{ $t('items.heading') }}
      </h2>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="hasAnyPermission(['items.create'])"
          @click="showImportModal = true"
          class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg"
        >
          <i class="fa fa-file-import" aria-hidden="true"></i>
          {{ $t('items.import_excel') }}
        </button>
        <Link
          v-if="hasAnyPermission(['items.create'])"
          href="/apps/items/create"
          class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
        >
          <i class="fa fa-plus" aria-hidden="true"></i>
          {{ $t('common.new') }}
        </Link>
      </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2" v-if="departments.length > 0 || categories.length > 0">
      <select class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" v-model="filterDepartment" @change="onDepartmentChange" :disabled="isLoading">
        <option value="">{{ $t('items.filter_all_dept') }}</option>
        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
      </select>
      <select class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" v-model="filterCategory" @change="applyFilter" :disabled="isLoading">
        <option value="">{{ $t('items.filter_all_cat') }}</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <select class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" v-model="filterStatus" @change="applyFilter" :disabled="isLoading">
        <option value="">{{ $t('items.filter_all_status') }}</option>
        <option value="aman">{{ $t('items.status_safe') }}</option>
        <option value="menipis">{{ $t('items.status_low') }}</option>
      </select>
      <i v-if="isLoading" class="fa fa-spinner fa-spin text-primary" aria-hidden="true"></i>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
      <DataTable
        :columns="columns"
        :rows="items.data"
        :pagination="items"
        row-key="id"
        :striped="false"
        :loading="isLoading"
        :empty-text="$t('common.no_data')"
      >
        <template #cell-stock="{ value }">
          <span :class="value < 10 ? 'font-mono font-semibold text-danger' : 'font-mono text-on-surface'">{{ value }}</span>
        </template>
        <template #cell-min_stock="{ value }">
          <span class="font-mono text-on-surface-variant">{{ value }}</span>
        </template>
        <template #cell-status="{ value }">
          <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium" :class="value === 'aman' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'">
            <span class="h-1.5 w-1.5 rounded-full" :class="value === 'aman' ? 'bg-success' : 'bg-warning'"></span>
            {{ $t("items.status_" + value) || value }}
          </span>
        </template>
        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1 justify-center">
            <Link
              :href="`/apps/items/${row.id}`"
              class="mtx-icon-btn"
              :aria-label="$t('common.view')"
            >
              <i class="fa fa-eye" aria-hidden="true"></i>
            </Link>
            <Link
              v-if="hasAnyPermission(['items.edit'])"
              :href="`/apps/items/${row.id}/edit`"
              class="mtx-icon-btn"
              :aria-label="$t('common.edit')"
            >
              <i class="fa fa-pencil" aria-hidden="true"></i>
            </Link>
            <button
              v-if="hasAnyPermission(['items.delete'])"
              class="mtx-icon-btn mtx-icon-btn-danger"
              :aria-label="$t('common.delete')"
              @click="handleDelete(row.id)"
            >
              <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="mtx-modal-overlay" @click.self="showImportModal = false" aria-labelledby="importModalLabel" aria-modal="true" role="dialog">
      <div class="mtx-modal">
        <div class="mtx-modal-header">
          <h3 id="importModalLabel" class="mtx-modal-title">{{ $t('items.import_excel') }}</h3>
          <button @click="showImportModal = false" class="mtx-icon-btn" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="mtx-modal-body">
          <p class="text-sm text-gray-600 mb-4">{{ $t('items.import_instructions') }}</p>
          <input type="file" class="mtx-input" accept=".xlsx,.xls,.csv" @change="importFile = $event.target.files[0]" />
        </div>
        <div class="mtx-modal-footer">
          <button type="button" class="mtx-btn mtx-btn-secondary" @click="showImportModal = false">{{ $t('common.cancel') }}</button>
          <button type="button" class="mtx-btn mtx-btn-primary" @click="handleImport" :disabled="!importFile || isImporting">
            <i v-if="isImporting" class="fa fa-spinner fa-spin" aria-hidden="true"></i>
            {{ isImporting ? $t('common.saving') : $t('items.import_excel') }}
          </button>
        </div>
      </div>
    </div>
    </div>
  </main>
</template>

<script>
import LayoutApp from "../../../Layouts/App.vue";
import DataTable from "../../../Components/DataTable.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { confirmDelete } from "@/Utils/confirmDialog";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable },
  props: { items: Object, departments: Array, categories: Array, filters: Object },
  setup(props) {
    const { t } = useI18n();
    const filterDepartment = ref(props.filters?.department_id ?? "");
    const filterCategory = ref(props.filters?.category_id ?? "");
    const filterStatus = ref(props.filters?.status ?? "");
    const isLoading = ref(false);
    const showImportModal = ref(false);
    const importFile = ref(null);
    const isImporting = ref(false);

    const columns = [
      { key: (row, i) => (props.items.from ?? 1) + i, label: t("items.col_no"), align: "center", width: "60px" },
      { key: "code", label: t("items.col_code"), sortable: true },
      { key: "name", label: t("items.col_name"), sortable: true },
      { key: "category_name", label: t("items.col_category") },
      { key: "location_name", label: t("items.col_location") },
      { key: "stock", label: t("items.col_stock"), align: "right", format: "number" },
      { key: "min_stock", label: t("items.col_min_stock"), align: "right", format: "number" },
      { key: "status", label: t("items.col_status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ];

    const onDepartmentChange = () => {
      filterCategory.value = "";
      applyFilter();
    };

    const applyFilter = () => {
      isLoading.value = true;
      router.get("/apps/items", {
        department_id: filterDepartment.value || undefined,
        category_id: filterCategory.value || undefined,
        status: filterStatus.value || undefined,
      }, {
        preserveState: true,
        onFinish: () => { isLoading.value = false; },
      });
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("items.delete_confirm"))) {
        isLoading.value = true;
        router.delete(`/apps/items/${id}`, {
          onFinish: () => { isLoading.value = false; },
          onError: () => { isLoading.value = false; },
        });
      }
    };

    const handleImport = () => {
      if (!importFile.value) return;
      isImporting.value = true;
      const formData = new FormData();
      formData.append("file", importFile.value);

      router.post("/apps/items/import", formData, {
        onFinish: () => {
          isImporting.value = false;
          showImportModal.value = false;
          importFile.value = null;
        },
      });
    };

    return {
      filterDepartment,
      filterCategory,
      filterStatus,
      onDepartmentChange,
      applyFilter,
      handleDelete,
      handleImport,
      isLoading,
      showImportModal,
      importFile,
      isImporting,
      columns,
    };
  },
};
</script>