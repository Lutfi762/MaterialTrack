<template>
  <Head>
    <title>{{ $t('categories.heading') }} - MaterialTrack</title>
  </Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Master Data</p>
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-folder text-primary" aria-hidden="true"></i>
        {{ $t('categories.heading') }}
      </h2>
      </div>
      <Link
        v-if="hasAnyPermission(['categories.create'])"
        href="/apps/categories/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        {{ $t('common.new') }}
      </Link>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
      <div class="relative min-w-[240px] flex-1 sm:max-w-md">
        <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant" aria-hidden="true"></i>
        <input
          v-model="search"
          type="text"
          class="w-full rounded-lg border border-outline/30 bg-surface py-2.5 pl-9 pr-3 text-sm text-on-surface outline-none transition placeholder:text-on-surface-variant/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
          :placeholder="$t('categories.search_placeholder')"
          @keyup.enter="applyFilters"
        />
      </div>
      <button class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg" @click="resetFilters">
        <i class="fa fa-rotate-left" aria-hidden="true"></i> {{ $t('common.reset') }}
      </button>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
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