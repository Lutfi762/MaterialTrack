<template>
  <Head><title>{{ $t('sales_invoices.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-file-invoice text-indigo-600"></i>
        {{ $t('sales_invoices.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['sales_invoices.create'])"
        href="/apps/sales-invoices/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="invoices.data"
      :pagination="invoices"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('sales_invoices.empty')"
    >
      <template #cell-invoice_number="{ row }">
        <code>{{ row.invoice_number }}</code>
      </template>
      <template #cell-sales_order="{ row }">
        {{ row.sales_order?.so_number }}
      </template>
      <template #cell-customer="{ row }">
        {{ row.customer?.name }}
      </template>
      <template #cell-invoice_date="{ row }">
        {{ row.invoice_date }}
      </template>
      <template #cell-due_date="{ row }">
        {{ row.due_date }}
      </template>
      <template #cell-total="{ row }">
        Rp {{ Number(row.total).toLocaleString('id-ID') }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            :href="`/apps/sales-invoices/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
          <Link
            v-if="hasAnyPermission(['sales_invoices.edit'])"
            :href="`/apps/sales-invoices/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['sales_invoices.delete'])"
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
  props: { invoices: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "invoice_number", label: t("sales_invoices.col_invoice") },
      { key: "sales_order", label: t("sales_invoices.col_so") },
      { key: "customer", label: t("sales_invoices.col_customer") },
      { key: "invoice_date", label: t("sales_invoices.col_date") },
      { key: "due_date", label: t("sales_invoices.col_due") },
      { key: "total", label: t("sales_invoices.col_total") },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);
    const remove = async (row) => {
      if (await confirmDelete(t("sales_invoices.delete_confirm"))) {
        router.delete(`/apps/sales-invoices/${row.id}`);
      }
    };
    return { remove, columns };
  },
};
</script>
