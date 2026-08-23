<template>
  <Head>
    <title>{{ $t('categories.heading') }} - MaterialTrack</title>
  </Head>

  <main class="p-6">
    <div class="mtx-page-header">
      <h2 class="mtx-page-title">
        <i class="fa fa-folder mtx-title-icon" aria-hidden="true"></i>
        {{ $t('categories.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['categories.create'])"
        href="/apps/categories/create"
        class="mtx-btn mtx-btn-primary"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        {{ $t('common.new') }}
      </Link>
    </div>

    <div class="mtx-toolbar">
      <div class="mtx-search-wrap">
        <i class="fa fa-search mtx-search-icon" aria-hidden="true"></i>
        <input
          v-model="search"
          type="text"
          class="mtx-input !pl-9"
          :placeholder="$t('categories.search_placeholder')"
          @keyup.enter="applyFilters"
        />
      </div>
      <button class="mtx-btn mtx-btn-secondary mtx-btn-sm" @click="resetFilters">
        <i class="fa fa-rotate-left" aria-hidden="true"></i> {{ $t('common.reset') }}
      </button>
    </div>

    <div class="mtx-card overflow-hidden">
      <DataTable
        :columns="columns"
        :rows="categories.data"
        :pagination="categories"
        row-key="id"
        :striped="false"
        :loading="isLoading"
        :empty-text="$t('categories.empty')"
      >
        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1 justify-center">
            <Link
              v-if="hasAnyPermission(['categories.edit'])"
              :href="`/apps/categories/${row.id}/edit`"
              class="mtx-icon-btn"
              :aria-label="$t('categories.edit')"
            >
              <i class="fa fa-pencil" aria-hidden="true"></i>
            </Link>
            <button
              v-if="hasAnyPermission(['categories.delete'])"
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
    categories: Object,
    filters: Object,
  },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.search ?? "");
    const isLoading = ref(false);

    const columns = [
      { key: (row, i) => (props.categories.from ?? 1) + i, label: t("categories.col_no"), align: "center", width: "60px" },
      { key: "code_prefix", label: t("categories.col_code"), sortable: true },
      { key: "name", label: t("categories.col_name"), sortable: true },
      { key: "description", label: t("categories.col_description") },
      { key: "items_count", label: t("categories.col_items"), align: "center", format: "number" },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ];

    const applyFilters = () => {
      router.get("/apps/categories", {
        search: search.value,
      }, {
        preserveState: true,
        replace: true,
      });
    };

    const resetFilters = () => {
      search.value = "";
      router.get("/apps/categories", {}, { preserveState: true, replace: true });
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("categories.delete_confirm"))) {
        isLoading.value = true;
        router.delete(`/apps/categories/${id}`, {
          onFinish: () => { isLoading.value = false; },
          onError: () => { isLoading.value = false; },
        });
      }
    };

    return { search, handleDelete, applyFilters, resetFilters, isLoading, columns };
  },
};
</script>