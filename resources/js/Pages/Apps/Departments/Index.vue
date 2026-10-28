<template>
  <Head><title>{{ $t('departments.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-building text-indigo-600"></i>
        {{ $t('departments.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['departments.create'])"
        href="/apps/departments/create"
        class="btn-modern btn-modern-primary"
      >
        <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
      </Link>
    </div>

    <DataTable
      :columns="columns"
      :rows="departments.data"
      :pagination="departments"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('departments.empty')"
    >
      <template #cell-code="{ row }">
        <code>{{ row.code ?? '-' }}</code>
      </template>
      <template #cell-name="{ row }">
        {{ row.name }}
      </template>
      <template #cell-description="{ row }">
        {{ row.description ?? '-' }}
      </template>
      <template #cell-pics_count="{ row }">
        {{ row.pics_count }}
      </template>
      <template #cell-status="{ row }">
        <span v-if="row.status === 'active'" class="mtx-badge mtx-badge-success">{{ $t('common.active') }}</span>
        <span v-else class="mtx-badge mtx-badge-neutral">{{ $t('common.inactive') }}</span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            v-if="hasAnyPermission(['departments.edit'])"
            :href="`/apps/departments/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['departments.delete'])"
            class="act-btn act-btn-danger"
            :aria-label="$t('common.delete')"
            @click="handleDelete(row.id)"
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
  props: { departments: Object },
  setup() {
    const { t } = useI18n();
    const isLoading = ref(false);
    const columns = ref([
      { key: "code", label: t("common.code") },
      { key: "name", label: t("common.name"), sortable: true },
      { key: "description", label: t("common.description") },
      { key: "pics_count", label: t("departments.col_pics"), align: "center" },
      { key: "status", label: t("common.status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);

    const handleDelete = async (id) => {
      if (await confirmDelete(t("departments.delete_confirm"))) {
        isLoading.value = true;
        router.delete(`/apps/departments/${id}`, {
          onFinish: () => { isLoading.value = false; },
          onError: () => { isLoading.value = false; },
        });
      }
    };
    return { handleDelete, isLoading, columns };
  },
};
</script>
