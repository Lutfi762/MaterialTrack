<template>
  <Head><title>{{ $t('employees.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-users text-indigo-600"></i>
        {{ $t('employees.heading') }}
      </h2>
      <div class="flex items-center gap-4">
        <div class="search-box">
          <input
            type="text"
            v-model="search"
            :placeholder="$t('employees.search_placeholder')"
            class="search-input"
            @input="handleSearch"
          />
          <i class="fa fa-search search-icon"></i>
        </div>
        <Link
          v-if="hasAnyPermission(['employees.create'])"
          href="/apps/employees/create"
          class="btn-modern btn-modern-primary"
        >
          <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
        </Link>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :rows="employees.data"
      :pagination="employees"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('employees.empty')"
    >
      <template #cell-no="{ rowIndex }">
        {{ employees.from + rowIndex }}
      </template>
      <template #cell-employee_id="{ row }">
        <code>{{ row.employee_id }}</code>
      </template>
      <template #cell-name="{ row }">
        {{ row.first_name }} {{ row.last_name || '' }}
      </template>
      <template #cell-department="{ row }">
        <div>
          <span>{{ row.department?.name || '-' }}</span>
          <br />
          <span class="text-xs text-gray-400">{{ row.position || '-' }}</span>
        </div>
      </template>
      <template #cell-employment_status="{ row }">
        {{ row.employment_status }}
      </template>
      <template #cell-basic_salary="{ row }">
        Rp {{ Number(row.basic_salary || 0).toLocaleString('id-ID') }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            v-if="hasAnyPermission(['employees.edit'])"
            :href="`/apps/employees/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['employees.delete'])"
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
  props: { employees: Object, filters: Object },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.q ?? "");

    const columns = ref([
      { key: "no", label: t("employees.col_no"), align: "center", width: "60px" },
      { key: "employee_id", label: t("employees.col_employee_id") },
      { key: "name", label: t("employees.col_name"), sortable: true },
      { key: "department", label: t("employees.col_department") },
      { key: "employment_status", label: t("employees.col_status") },
      { key: "basic_salary", label: t("employees.col_salary") },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ]);

    const handleSearch = () => {
      router.get(
        "/apps/employees",
        { q: search.value },
        { preserveState: true, replace: true }
      );
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("employees.delete_confirm"))) {
        router.delete(`/apps/employees/${id}`);
      }
    };

    return { search, handleSearch, handleDelete, columns };
  },
};
</script>
