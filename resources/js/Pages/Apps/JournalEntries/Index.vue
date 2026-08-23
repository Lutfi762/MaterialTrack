<template>
  <Head><title>{{ $t('journal_entries.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-book text-indigo-600"></i>
        {{ $t('journal_entries.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['journal_entries.create'])"
        href="/apps/journal-entries/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="journalEntries.data"
      :pagination="journalEntries"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('journal_entries.empty')"
    >
      <template #cell-entry_number="{ row }">
        <code>{{ row.entry_number }}</code>
      </template>
      <template #cell-entry_date="{ row }">
        {{ row.entry_date }}
      </template>
      <template #cell-narration="{ row }">
        {{ row.narration || '-' }}
      </template>
      <template #cell-status="{ row }">
        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
          {{ row.status }}
        </span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex justify-center">
          <Link
            :href="`/apps/journal-entries/${row.id}`"
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
  props: { journalEntries: Object },
  setup() {
    const { t } = useI18n();
    const columns = ref([
      { key: "entry_number", label: t("journal_entries.col_reference") },
      { key: "entry_date", label: t("journal_entries.col_date") },
      { key: "narration", label: t("journal_entries.col_description") },
      { key: "status", label: t("journal_entries.col_status") },
      { key: "actions", label: t("common.action"), align: "center", width: "100px" },
    ]);
    return { columns };
  },
};
</script>
