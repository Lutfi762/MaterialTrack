<template>
  <Head><title>{{ $t('payroll.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-money-bill-wave text-indigo-600"></i>
        {{ $t('payroll.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['payroll.create'])"
        href="/apps/payroll/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="periods.data"
      :pagination="periods"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('payroll.empty')"
    >
      <template #cell-name="{ row }">
        <strong>{{ row.name }}</strong>
      </template>
      <template #cell-date_range="{ row }">
        {{ row.start_date }} s/d {{ row.end_date }}
      </template>
      <template #cell-pay_date="{ row }">
        {{ row.pay_date }}
      </template>
      <template #cell-total_amount="{ row }">
        Rp {{ Number(row.total_amount || 0).toLocaleString('id-ID') }}
      </template>
      <template #cell-status="{ row }">
        <span
          class="px-2 py-1 text-xs rounded-full font-bold"
          :class="{
            'bg-yellow-100 text-yellow-800': row.status === 'draft',
            'bg-blue-100 text-blue-800': row.status === 'approved',
            'bg-green-100 text-green-800': row.status === 'paid'
          }"
        >
          {{ row.status }}
        </span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex justify-center">
          <Link
            :href="`/apps/payroll/${row.id}`"
            class="act-btn act-btn-neutral"
            :aria-label="$t('common.view')"
          >
            <i class="fa fa-eye"></i>
          </Link>
        </div>
      </template>
    </DataTable>
  </main>
</template>

<script>
import LayoutApp from "@/Layouts/App.vue";
import DataTable from "@/Components/DataTable.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { periods: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "name", label: t("payroll.col_period") },
      { key: "date_range", label: t("payroll.col_date_range") },
      { key: "pay_date", label: t("payroll.col_pay_date") },
      { key: "total_amount", label: t("payroll.col_total") },
      { key: "status", label: t("payroll.col_status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "100px" },
    ]);
    return { columns };
  },
};
</script>
