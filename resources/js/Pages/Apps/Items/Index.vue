<template>
  <Head>
    <title>{{ $t('items.title') }} - MaterialTrack</title>
  </Head>

  <main class="p-6">
    <div class="mtx-page-header">
      <h2 class="mtx-page-title">
        <i class="fa fa-box mtx-title-icon" aria-hidden="true"></i>
        {{ $t('items.heading') }}
      </h2>
      <div class="flex items-center gap-2">
        <button
          v-if="hasAnyPermission(['items.create'])"
          @click="showImportModal = true"
          class="mtx-btn mtx-btn-secondary"
        >
          <i class="fa fa-file-import" aria-hidden="true"></i>
          {{ $t('items.import_excel') }}
        </button>
        <Link
          v-if="hasAnyPermission(['items.create'])"
          href="/apps/items/create"
          class="mtx-btn mtx-btn-primary"
        >
          <i class="fa fa-plus" aria-hidden="true"></i>
          {{ $t('common.new') }}
        </Link>
      </div>
    </div>

    <div class="mtx-toolbar" v-if="departments.length > 0 || categories.length > 0">
      <select class="mtx-select w-auto" v-model="filterDepartment" @change="onDepartmentChange" :disabled="isLoading">
        <option value="">{{ $t('items.filter_all_dept') }}</option>
        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
      </select>
      <select class="mtx-select w-auto" v-model="filterCategory" @change="applyFilter" :disabled="isLoading">
        <option value="">{{ $t('items.filter_all_cat') }}</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <select class="mtx-select w-auto" v-model="filterStatus" @change="applyFilter" :disabled="isLoading">
        <option value="">{{ $t('items.filter_all_status') }}</option>
        <option value="aman">{{ $t('items.status_safe') }}</option>
        <option value="menipis">{{ $t('items.status_low') }}</option>
      </select>
      <i v-if="isLoading" class="fa fa-spinner fa-spin text-indigo-600" aria-hidden="true"></i>
    </div>

    <div class="mtx-card overflow-hidden">
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
          <span :class="value < 10 ? 'text-red-600 font-semibold' : 'font-mono'">{{ value }}</span>
        </template>
        <template #cell-min_stock="{ value }">
          <span class="font-mono">{{ value }}</span>
        </template>
        <template #cell-status="{ value }">
          <span class="mtx-badge" :class="value === 'aman' ? 'mtx-badge-success' : 'mtx-badge-warning'">
            {{ $t("items.status_" + value) || value }}
          </span>
        </template>
        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1 justify-center">
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