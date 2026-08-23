<template>
  <Head><title>{{ $t('chart_of_accounts.title') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface"><div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Accounting</p><h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-book text-primary"></i>
        {{ $t('chart_of_accounts.heading') }}
      </h2></div>
      <Link
        v-if="hasAnyPermission(['chart_of_accounts.create'])"
        href="/apps/chart-of-accounts/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
      >
        <i class="fa fa-plus"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <section class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm"><DataTable
      :columns="columns"
      :rows="accounts.data"
      :pagination="accounts"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('chart_of_accounts.empty')"
    >
      <template #cell-code="{ row }">
        <code class="rounded bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">{{ row.code }}</code>
      </template>
      <template #cell-name="{ row }">
        <span :class="{ 'pl-8': !row.is_header }">{{ row.name }}</span>
      </template>
      <template #cell-type="{ row }">
        {{ row.type }}
      </template>
      <template #cell-sub_type="{ row }">
        {{ row.sub_type }}
      </template>
      <template #cell-opening_balance="{ row }">
        Rp {{ Number(row.opening_balance || 0).toLocaleString('id-ID') }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            :href="`/apps/chart-of-accounts/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            @click="destroy(row.id)"
            class="act-btn act-btn-danger"
            :aria-label="$t('common.delete')"
          >
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </template>
    </DataTable></section></div>
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
  props: { accounts: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "code", label: t("chart_of_accounts.col_code") },
      { key: "name", label: t("chart_of_accounts.col_name"), sortable: true },
      { key: "type", label: t("chart_of_accounts.col_type") },
      { key: "sub_type", label: t("chart_of_accounts.col_sub_type") },
      { key: "opening_balance", label: t("chart_of_accounts.col_balance") },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ]);

    const destroy = async (id) => {
      if (await confirmDelete(t("chart_of_accounts.delete_confirm"))) {
        router.delete(`/apps/chart-of-accounts/${id}`);
      }
    };

    return { destroy, columns };
  },
};
</script>
