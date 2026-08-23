<template>
  <Head><title>{{ $t('permissions.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-key text-indigo-600"></i>
        {{ $t('permissions.heading') }}
      </h2>
      <div class="search-box">
        <input
          type="text"
          v-model="search"
          :placeholder="$t('permissions.search_placeholder')"
          class="search-input"
          @input="handleSearch"
        />
        <i class="fa fa-search search-icon"></i>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :rows="permissions.data"
      :pagination="permissions"
      row-key="name"
      :striped="true"
      :loading="false"
      :empty-text="$t('permissions.empty')"
    >
      <template #cell-no="{ rowIndex }">
        {{ permissions.from + rowIndex }}
      </template>
      <template #cell-name="{ row }">
        {{ row.name }}
      </template>
    </DataTable>
  </main>
</template>

<script>
import LayoutApp from "@/Layouts/App.vue";
import DataTable from "@/Components/DataTable.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { permissions: Object },
  setup() {
    const { t } = useI18n();
    const search = ref("");
    const columns = ref([
      { key: "no", label: t("permissions.col_no"), align: "center" },
      { key: "name", label: t("permissions.col_name"), sortable: true },
    ]);
    const handleSearch = () => {
      router.get("/apps/permissions", { q: search.value });
    };
    return { search, handleSearch, columns };
  },
};
</script>
