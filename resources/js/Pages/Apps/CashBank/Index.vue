<template>
  <Head><title>{{ $t('cash_bank.heading') }} - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Finance</p>
        <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
          <i class="fa fa-university text-primary" aria-hidden="true"></i>
          {{ $t('cash_bank.title') }}
        </h2>
      </div>
      <Link
        v-if="hasAnyPermission(['cash_bank.create'])"
        href="/apps/cash-bank/create"
        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 dark:text-[#1000a9]"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        {{ $t('cash_bank.add') }}
      </Link>
    </div>

    <div class="mb-4 flex flex-wrap items-end gap-3">
      <div>
        <label class="mb-1 block text-xs font-medium text-on-surface-variant">{{ $t('cash_bank.col_type') }}</label>
        <select v-model="form.type" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="apply">
          <option value="">{{ $t('common.all') }}</option>
          <option value="in">{{ $t('cash_bank.type_in', 'Kas Masuk') }}</option>
          <option value="out">{{ $t('cash_bank.type_out', 'Kas Keluar') }}</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-on-surface-variant">{{ $t('cash_bank.col_account') }}</label>
        <select v-model="form.account_id" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="apply">
          <option value="">{{ $t('common.all') }}</option>
          <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-on-surface-variant">{{ $t('common.from') }}</label>
        <input type="date" v-model="form.date_from" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="apply">
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-on-surface-variant">{{ $t('common.to') }}</label>
        <input type="date" v-model="form.date_to" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="apply">
      </div>
      <button @click="reset" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg">
        <i class="fa fa-rotate-left" aria-hidden="true"></i> {{ $t('common.reset') }}
      </button>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
    <DataTable
      :columns="columns"
      :rows="transactions.data"
      :pagination="transactions"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('cash_bank.empty')"
    >
      <template #cell-transaction_number="{ row }">
        <code>{{ row.transaction_number }}</code>
      </template>
      <template #cell-transaction_date="{ row }">
        {{ row.transaction_date }}
      </template>
      <template #cell-type="{ row }">
        <span
          class="px-2 py-0.5 rounded-full text-xs font-semibold"
          :class="row.type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
        >
          {{ row.type === 'in' ? $t('cash_bank.type_in', 'MASUK') : $t('cash_bank.type_out', 'KELUAR') }}
        </span>
      </template>
      <template #cell-account="{ row }">
        <div>{{ row.account?.name }}</div>
        <div class="text-xs text-gray-400 font-mono">{{ row.account?.code }}</div>
      </template>
      <template #cell-counter_account="{ row }">
        <div>{{ row.counter_account?.name }}</div>
        <div class="text-xs text-gray-400 font-mono">{{ row.counter_account?.code }}</div>
      </template>
      <template #cell-amount="{ row }">
        <span
          class="text-sm font-mono font-semibold"
          :class="row.type === 'in' ? 'text-green-700' : 'text-red-700'"
        >
          {{ row.type === 'in' ? '+' : '-' }} {{ fmt(row.amount) }}
        </span>
      </template>
      <template #cell-description="{ row }">
        {{ row.description || '-' }}
      </template>
    </DataTable>
    </div>
    </div>
  </main>
</template>

<script>
import LayoutApp from "@/Layouts/App.vue";
import DataTable from "@/Components/DataTable.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { transactions: Object, accounts: Array, filters: Object },
  setup(props) {
    const { t } = useI18n();
    const form = reactive({
      type: props.filters?.type || '',
      account_id: props.filters?.account_id || '',
      date_from: props.filters?.date_from || '',
      date_to: props.filters?.date_to || '',
    });
    const columns = ref([
      { key: "transaction_number", label: t("cash_bank.col_number") },
      { key: "transaction_date", label: t("cash_bank.col_date") },
      { key: "type", label: t("cash_bank.col_type"), align: "center" },
      { key: "account", label: t("cash_bank.col_account") },
      { key: "counter_account", label: t("cash_bank.col_counter_account") },
      { key: "amount", label: t("cash_bank.col_amount"), align: "right" },
      { key: "description", label: t("cash_bank.col_description") },
    ]);
    const apply = () => {
            router.get('/apps/cash-bank', {
        type: form.type || undefined,
        account_id: form.account_id || undefined,
        date_from: form.date_from || undefined,
        date_to: form.date_to || undefined,
      }, { preserveState: true });
    };
    const reset = () => {
      form.type = '';
      form.account_id = '';
      form.date_from = '';
      form.date_to = '';
      router.get('/apps/cash-bank');
    };
    const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
    return { transactions: props.transactions, accounts: props.accounts, form, columns, apply, reset, fmt };
  },
};
</script>
