<template>
  <Head><title>{{ $t('customers.heading') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div><p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Partners</p>
      <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-users text-primary"></i>
        {{ $t('customers.heading') }}
      </h2></div>
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative min-w-[220px]">
          <input
            type="text"
            v-model="search"
            :placeholder="$t('customers.search_placeholder')"
            class="w-full rounded-lg border border-outline/30 bg-surface py-2.5 pl-3 pr-9 text-sm text-on-surface outline-none placeholder:text-on-surface-variant/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
            @input="handleSearch"
          />
          <i class="fa fa-search pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant"></i>
        </div>
        <Link
          v-if="hasAnyPermission(['customers.create'])"
          href="/apps/customers/create"
          class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
        >
          <i class="fa fa-plus"></i> {{ $t('common.new') }}
        </Link>
      </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm"><DataTable
      :columns="columns"
      :rows="customers.data"
      :pagination="customers"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('customers.empty')"
    >
      <template #cell-code="{ row }">
        <code class="rounded bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">{{ row.code || '-' }}</code>
      </template>
      <template #cell-name="{ row }">
        {{ row.name }}
      </template>
      <template #cell-contact="{ row }">
        {{ row.contact_person || '-' }}
        <br />
        <span class="text-xs text-on-surface-variant">{{ row.email }}</span>
      </template>
      <template #cell-type="{ row }">
        {{ row.type }}
      </template>
      <template #cell-credit_limit="{ row }">
        Rp {{ Number(row.credit_limit || 0).toLocaleString('id-ID') }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            v-if="hasAnyPermission(['customers.edit'])"
            :href="`/apps/customers/${row.id}/edit`"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-on-surface-variant transition hover:bg-primary/10 hover:text-primary"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['customers.delete'])"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-on-surface-variant transition hover:bg-danger/10 hover:text-danger"
            :aria-label="$t('common.delete')"
            @click="handleDelete(row.id)"
          >
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </template>
    </DataTable></section>
    </div>
  </main>
</template>

<script>
import LayoutApp from "@/Layouts/App.vue";
import DataTable from "@/Components/DataTable.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { confirmDelete } from "@/Utils/confirmDialog";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { customers: Object, filters: Object },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.search || "");

    const columns = ref([
      { key: "code", label: t("customers.col_code"), width: "120px" },
      { key: "name", label: t("customers.col_name"), sortable: true },
      { key: "contact", label: t("customers.col_contact") },
      { key: "type", label: t("customers.col_type") },
      { key: "credit_limit", label: t("customers.col_credit_limit") },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ]);

    const handleSearch = () => {
      router.get(
        "/apps/customers",
        { search: search.value },
        { preserveState: true, replace: true }
      );
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("customers.delete_confirm"))) {
        router.delete(`/apps/customers/${id}`);
      }
    };

    return { search, handleSearch, handleDelete, columns };
  },
};
</script>
