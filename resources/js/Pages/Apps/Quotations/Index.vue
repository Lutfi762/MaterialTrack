<template>
  <Head><title>{{ $t('quotations.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-file-contract text-indigo-600"></i>
        {{ $t('quotations.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['quotations.create'])"
        href="/apps/quotations/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="quotations.data"
      :pagination="quotations"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('quotations.empty')"
    >
      <template #cell-quotation_number="{ row }">
        <code>{{ row.quotation_number }}</code>
      </template>
      <template #cell-customer="{ row }">
        {{ row.customer?.name }}
      </template>
      <template #cell-quotation_date="{ row }">
        {{ row.quotation_date }}
      </template>
      <template #cell-valid_until="{ row }">
        {{ row.valid_until || '-' }}
      </template>
      <template #cell-total="{ row }">
        Rp {{ Number(row.total).toLocaleString('id-ID') }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            :href="`/apps/quotations/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
          <Link
            v-if="hasAnyPermission(['quotations.edit'])"
            :href="`/apps/quotations/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['quotations.delete'])"
            class="act-btn act-btn-danger"
            :aria-label="$t('common.delete')"
            @click="remove(row)"
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
  props: { quotations: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "quotation_number", label: t("quotations.col_quotation") },
      { key: "customer", label: t("quotations.col_customer") },
      { key: "quotation_date", label: t("quotations.col_date") },
      { key: "valid_until", label: t("quotations.col_valid") },
      { key: "total", label: t("quotations.col_total") },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);
    const remove = async (row) => {
      if (await confirmDelete(t("quotations.delete_confirm"))) {
        router.delete(`/apps/quotations/${row.id}`);
      }
    };
    return { remove, columns };
  },
};
</script>
