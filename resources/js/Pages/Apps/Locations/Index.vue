<template>
  <Head>
    <title>{{ $t('locations.heading') }} - MaterialTrack</title>
  </Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-location-dot text-indigo-600"></i>
        {{ $t('locations.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['locations.create'])"
        href="/apps/locations/create"
        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <i class="fa fa-plus-circle me-2"></i>
        {{ $t('common.new') }}
      </Link>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
      <div class="flex-1 min-w-[250px]">
        <div class="input-group">
          <input
            v-model="search"
            type="text"
            class="form-control"
            :placeholder="$t('locations.search_placeholder')"
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
      :rows="locations.data"
      :pagination="locations"
      row-key="id"
      :striped="true"
      :loading="isLoading"
      :empty-text="$t('locations.empty')"
    >
      <template #cell-actions="{ row, index }">
        <div class="flex items-center gap-2 justify-center">
          <Link
            v-if="hasAnyPermission(['locations.edit'])"
            :href="`/apps/locations/${row.id}/edit`"
            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['locations.delete'])"
            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            :aria-label="$t('common.delete')"
            @click="handleDelete(row.id)"
          >
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>
  </main>
</template>

<script>
import LayoutApp from "../../../Layouts/App.vue";
import DataTable from "../../../Components/DataTable.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { confirmDelete } from "@/Utils/confirmDialog";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable },
  props: {
    locations: Object,
    filters: Object,
  },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.search ?? "");
    const isLoading = ref(false);

    const columns = [
      { key: (row, i) => (props.locations.from ?? 1) + i, label: t("locations.col_no"), align: "center", width: "60px" },
      { key: "name", label: t("locations.col_name"), sortable: true },
      { key: "warehouse_zone", label: t("locations.col_zone") },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ];

    const applyFilters = () => {
      router.get("/apps/locations", {
        search: search.value,
      }, {
        preserveState: true,
        replace: true,
      });
    };

    const resetFilters = () => {
      search.value = "";
      router.get("/apps/locations", {}, { preserveState: true, replace: true });
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("locations.delete_confirm"))) {
        isLoading.value = true;
        router.delete(`/apps/locations/${id}`, {
          onFinish: () => { isLoading.value = false; },
          onError: () => { isLoading.value = false; },
        });
      }
    };

    return { search, columns, handleDelete, applyFilters, resetFilters, isLoading };
  },
};
</script>