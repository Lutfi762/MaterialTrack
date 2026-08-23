<template>
  <Head><title>{{ $t('customers.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-users text-indigo-600"></i>
        {{ $t('customers.heading') }}
      </h2>
      <div class="flex items-center gap-4">
        <div class="search-box">
          <input
            type="text"
            v-model="search"
            :placeholder="$t('customers.search_placeholder')"
            class="search-input"
            @input="handleSearch"
          />
          <i class="fa fa-search search-icon"></i>
        </div>
        <Link
          v-if="hasAnyPermission(['customers.create'])"
          href="/apps/customers/create"
          class="btn-modern btn-modern-primary"
        >
          <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
        </Link>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :rows="customers.data"
      :pagination="customers"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('customers.empty')"
    >
      <template #cell-code="{ row }">
        <code>{{ row.code || '-' }}</code>
      </template>
      <template #cell-name="{ row }">
        {{ row.name }}
      </template>
      <template #cell-contact="{ row }">
        {{ row.contact_person || '-' }}
        <br />
        <span class="text-xs text-gray-400">{{ row.email }}</span>
      </template>
      <template #cell-type="{ row }">
        {{ row.type }}
      </template>
      <template #cell-credit_limit="{ row }">
        Rp {{ Number(row.credit_limit || 0).toLocaleString('id-ID') }}
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <Link
            v-if="hasAnyPermission(['customers.edit'])"
            :href="`/apps/customers/${row.id}/edit`"
            class="act-btn act-btn-success"
            :aria-label="$t('common.edit')"
          >
            <i class="fa fa-pencil-alt"></i>
          </Link>
          <button
            v-if="hasAnyPermission(['customers.delete'])"
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
  props: { customers: Object, filters: Object },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.search || "");

    const columns = ref([
      { key: "code", label: t("customers.col_code"), width: "120px" },
      { key: "name", label: t("customers.col_name"), sortable: true },
      { key: "contact", label: t("customers.col_contact") },
      { key: "type", label: t("customers.col_type") },
      { key: "credit_limit", label: t("customers.col_credit_limit") },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ]);

    const handleSearch = () => {
      router.get(
        "/apps/customers",
        { search: search.value },
        { preserveState: true, replace: true }
      );
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("customers.delete_confirm"))) {
        router.delete(`/apps/customers/${id}`);
      }
    };

    return { search, handleSearch, handleDelete, columns };
  },
};
</script>
