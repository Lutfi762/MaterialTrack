<template>
  <Head><title>{{ $t('sales_orders.heading') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface"><div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Sales</p><h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
        <i class="fa fa-shopping-cart text-primary"></i>
        {{ $t('sales_orders.heading') }}
      </h2></div>
      <Link
        v-if="hasAnyPermission(['sales_orders.create'])"
        href="/apps/sales-orders/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
      >
        <i class="fa fa-plus"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <section class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm"><DataTable
      :columns="columns"
      :rows="salesOrders.data"
      :pagination="salesOrders"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('sales_orders.empty')"
    >
      <template #cell-so_number="{ row }">
        <code class="rounded bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">{{ row.so_number }}</code>
      </template>
      <template #cell-customer="{ row }">
        {{ row.customer?.name }}
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
            :href="`/apps/sales-orders/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
          <Link
            v-if="hasAnyPermission(['sales_orders.edit'])"
            :href="`/apps/sales-orders/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['sales_orders.delete'])"
            class="act-btn act-btn-danger"
            :aria-label="$t('common.delete')"
            @click="remove(row)"
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
  props: { salesOrders: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "so_number", label: t("sales_orders.col_so") },
      { key: "customer", label: t("sales_orders.col_customer") },
      { key: "order_date", label: t("sales_orders.col_date") },
      { key: "total", label: t("sales_orders.col_total") },
      { key: "status", label: t("sales_orders.col_status") },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);
    const remove = async (row) => {
      if (await confirmDelete(t("sales_orders.delete_confirm"))) {
        router.delete(`/apps/sales-orders/${row.id}`);
      }
    };
    return { remove, columns };
  },
};
</script>
