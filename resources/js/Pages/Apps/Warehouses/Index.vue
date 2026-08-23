<template>
  <Head>
    <title>{{ $t('warehouses.title') }} - MaterialTrack</title>
  </Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
    <div class="mx-auto max-w-7xl">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Master Data</p>
        <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-warehouse text-primary"></i>
        {{ $t('warehouses.heading') }}
        </h2>
      </div>
      <Link
        v-if="hasAnyPermission(['warehouses.create'])"
        href="/apps/warehouses/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
      >
        <i class="fa fa-plus"></i>
        {{ $t('warehouses.add') }}
      </Link>
      </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
      <div class="relative min-w-[250px] flex-1 sm:max-w-md">
        <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant"></i>
          <input
            v-model="search"
            type="text"
            class="w-full rounded-lg border border-outline/30 bg-surface py-2.5 pl-9 pr-11 text-sm text-on-surface outline-none transition placeholder:text-on-surface-variant/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
            :placeholder="$t('warehouses.search_placeholder')"
            @keyup.enter="applyFilters"
          />
          <button class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-md p-2 text-on-surface-variant transition hover:bg-bg hover:text-primary" :aria-label="$t('common.search')" @click="applyFilters">
            <i class="fa fa-search"></i>
          </button>
      </div>
      <div>
        <button class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg hover:text-on-surface" @click="resetFilters">
          <i class="fa fa-redo"></i> {{ $t('common.reset') }}
        </button>
      </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
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
        <code class="rounded bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">{{ value }}</code>
      </template>
      <template #cell-items_count="{ value }">
        <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
          {{ value }} {{ $t('warehouses.items') }}
        </span>
      </template>
      <template #cell-status="{ value }">
        <span
          class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
          :class="value ? 'bg-success/10 text-success' : 'bg-bg text-on-surface-variant'"
        >
          <span class="h-1.5 w-1.5 rounded-full" :class="value ? 'bg-success' : 'bg-outline'"></span>
          {{ value ? $t('common.active') : $t('common.inactive') }}
        </span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center gap-1 justify-center">
          <Link
            v-if="hasAnyPermission(['warehouses.edit'])"
            :href="`/apps/warehouses/${row.id}/edit`"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-on-surface-variant transition hover:bg-primary/10 hover:text-primary"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-edit"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['warehouses.delete'])"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-on-surface-variant transition hover:bg-danger/10 hover:text-danger"
            @click="deleteWarehouse(row)"
            :aria-label="$t('common.delete')"
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