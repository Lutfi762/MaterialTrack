<template>
  <Head>
    <title>{{ $t('warehouses.title') }} - MaterialTrack</title>
  </Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-warehouse text-indigo-600"></i>
        {{ $t('warehouses.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['warehouses.create'])"
        href="/apps/warehouses/create"
        class="btn btn-primary rounded-pill"
      >
        <i class="fa fa-plus me-1"></i>
        {{ $t('warehouses.add') }}
      </Link>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
      <div class="flex-1 min-w-[250px]">
        <div class="input-group">
          <input
            v-model="search"
            type="text"
            class="form-control"
            :placeholder="$t('warehouses.search_placeholder')"
            @keyup.enter="applyFilters"
          />
          <button class="btn btn-outline-secondary" @click="applyFilters">
            <i class="fa fa-search"></i>
          </button>
        </div>
      </div>
      <div class="w-auto">
        <button class="btn btn-outline-secondary btn-sm" @click="resetFilters">
          <i class="fa fa-redo me-1"></i> {{ $t('common.reset') }}
        </button>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :rows="warehouses.data"
      :pagination="warehouses"
      row-key="id"
      :striped="true"
      :loading="isLoading"
      :empty-text="$t('warehouses.empty')"
    >
      <template #cell-code="{ value }">
        <code class="text-sm">{{ value }}</code>
      </template>
      <template #cell-items_count="{ value }">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          {{ value }} {{ $t('warehouses.items') }}
        </span>
      </template>
      <template #cell-status="{ value }">
        <span
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
          :class="value ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
        >
          {{ value ? $t('common.active') : $t('common.inactive') }}
        </span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center gap-1 justify-center">
          <Link
            v-if="hasAnyPermission(['warehouses.edit'])"
            :href="`/apps/warehouses/${row.id}/edit`"
            class="btn btn-sm btn-outline-primary"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-edit"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['warehouses.delete'])"
            class="btn btn-sm btn-outline-danger"
            @click="deleteWarehouse(row)"
            :aria-label="$t('common.delete')"
          >
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>
  </main>
</template>

<script>
import LayoutApp from "@/Layouts/App.vue";
import DataTable from "@/Components/DataTable.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { confirmDelete } from "@/Utils/confirmDialog";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable },
  props: {
    warehouses: Object,
    filters: Object,
  },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.search ?? "");
    const isLoading = ref(false);

    const columns = [
      { key: (row, i) => (props.warehouses.from ?? 1) + i, label: t("warehouses.col_no"), align: "center", width: "60px" },
      { key: "code", label: t("common.code"), sortable: true },
      { key: "name", label: t("common.name"), sortable: true },
      { key: "address", label: t("common.description") },
      { key: "items_count", label: t("warehouses.items_count"), align: "center" },
      { key: "is_active", label: t("common.status"), align: "center" },
      { key: "actions", label: t("common.actions"), align: "center", width: "130px" },
    ];

    const applyFilters = () => {
      router.get("/apps/warehouses", {
        search: search.value,
      }, {
        preserveState: true,
        replace: true,
      });
    };

    const resetFilters = () => {
      search.value = "";
      router.get("/apps/warehouses", {}, { preserveState: true, replace: true });
    };

    const deleteWarehouse = async (warehouse) => {
      const confirmed = await confirmDelete(t("warehouses.delete_confirm"));
      if (confirmed) {
        router.delete("/apps/warehouses/" + warehouse.id);
      }
    };

    return { search, columns, deleteWarehouse, applyFilters, resetFilters, isLoading };
  },
};
</script>