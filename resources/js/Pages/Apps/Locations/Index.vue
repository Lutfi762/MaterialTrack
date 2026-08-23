<template>
  <Head>
    <title>{{ $t('locations.heading') }} - MaterialTrack</title>
  </Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Warehouse Setup</p>
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-location-dot text-primary"></i>
        {{ $t('locations.heading') }}
      </h2>
      </div>
      <Link
        v-if="hasAnyPermission(['locations.create'])"
        href="/apps/locations/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
      >
        <i class="fa fa-plus-circle"></i>
        {{ $t('common.new') }}
      </Link>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
      <div class="relative min-w-[250px] flex-1 sm:max-w-md">
        <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant"></i>
          <input
            v-model="search"
            type="text"
            class="w-full rounded-lg border border-outline/30 bg-surface py-2.5 pl-9 pr-11 text-sm text-on-surface outline-none transition placeholder:text-on-surface-variant/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
            :placeholder="$t('locations.search_placeholder')"
            @keyup.enter="applyFilters"
          />
          <button class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-md p-2 text-on-surface-variant transition hover:bg-bg hover:text-primary" :aria-label="$t('common.search')" @click="applyFilters">
            <i class="fa fa-search"></i>
          </button>
      </div>
      <div>
        <button class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg" @click="resetFilters">
          <i class="fa fa-redo"></i> {{ $t('common.reset') }}
        </button>
      </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
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
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-on-surface-variant transition hover:bg-primary/10 hover:text-primary"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['locations.delete'])"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-on-surface-variant transition hover:bg-danger/10 hover:text-danger"
            :aria-label="$t('common.delete')"
            @click="handleDelete(row.id)"
          >
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>
    </section>
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