<template>
  <Head><title>{{ $t('purchase_orders.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-shopping-cart text-indigo-600"></i>
        {{ $t('purchase_orders.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['purchase_orders.create'])"
        href="/apps/purchase-orders/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="purchaseOrders.data"
      :pagination="purchaseOrders"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('purchase_orders.empty')"
    >
      <template #cell-po_number="{ row }">
        <code>{{ row.po_number }}</code>
      </template>
      <template #cell-supplier="{ row }">
        {{ row.supplier?.name }}
      </template>
      <template #cell-order_date="{ row }">
        {{ row.order_date }}
      </template>
      <template #cell-total="{ row }">
        Rp {{ Number(row.total).toLocaleString('id-ID') }}
      </template>
      <template #cell-status="{ row }">
        {{ row.status }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            :href="`/apps/purchase-orders/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
          <Link
            v-if="hasAnyPermission(['purchase_orders.edit'])"
            :href="`/apps/purchase-orders/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['purchase_orders.delete'])"
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
  props: { purchaseOrders: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "po_number", label: t("purchase_orders.col_po") },
      { key: "supplier", label: t("purchase_orders.col_supplier") },
      { key: "order_date", label: t("purchase_orders.col_date") },
      { key: "total", label: t("purchase_orders.col_total") },
      { key: "status", label: t("purchase_orders.col_status") },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);
    const remove = async (row) => {
      if (await confirmDelete(t("purchase_orders.delete_confirm"))) {
        router.delete(`/apps/purchase-orders/${row.id}`);
      }
    };
    return { remove, columns };
  },
};
</script>
