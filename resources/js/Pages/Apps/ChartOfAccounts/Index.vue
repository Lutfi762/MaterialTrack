<template>
  <Head><title>{{ $t('chart_of_accounts.title') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-book text-indigo-600"></i>
        {{ $t('chart_of_accounts.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['chart_of_accounts.create'])"
        href="/apps/chart-of-accounts/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="accounts.data"
      :pagination="accounts"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('chart_of_accounts.empty')"
    >
      <template #cell-code="{ row }">
        <code>{{ row.code }}</code>
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
