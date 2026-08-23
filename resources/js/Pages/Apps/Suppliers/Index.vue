<template>
  <Head>
    <title>{{ $t('suppliers.title') }} - MaterialTrack</title>
  </Head>

  <main class="p-6">
    <div class="mtx-page-header">
      <h2 class="mtx-page-title">
        <i class="fa fa-truck mtx-title-icon" aria-hidden="true"></i>
        {{ $t('suppliers.heading') }}
      </h2>
      <Link
        v-if="hasAnyPermission(['suppliers.create'])"
        href="/apps/suppliers/create"
        class="mtx-btn mtx-btn-primary"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        {{ $t('common.new') }}
      </Link>
    </div>

    <div class="mtx-toolbar">
      <div class="mtx-search-wrap">
        <i class="fa fa-search mtx-search-icon" aria-hidden="true"></i>
        <input
          v-model="search"
          type="text"
          class="mtx-input !pl-9"
          :placeholder="$t('suppliers.search_placeholder')"
          @keyup.enter="applyFilters"
        />
      </div>
      <select v-model="filterActive" class="mtx-select w-auto" @change="applyFilters">
        <option value="">{{ $t('common.all') }}</option>
        <option :value="1">{{ $t('common.active') }}</option>
        <option :value="0">{{ $t('common.inactive') }}</option>
      </select>
      <button class="mtx-btn mtx-btn-secondary mtx-btn-sm" @click="resetFilters">
        <i class="fa fa-rotate-left" aria-hidden="true"></i> {{ $t('common.reset') }}
      </button>
    </div>

    <div class="mtx-card overflow-hidden">
      <DataTable
        :columns="columns"
        :rows="suppliers.data"
        :pagination="suppliers"
        row-key="id"
        :striped="false"
        :loading="isLoading"
        :empty-text="$t('common.no_data')"
      >
        <template #cell-status="{ value }">
          <span class="mtx-badge" :class="value ? 'mtx-badge-success' : 'mtx-badge-neutral'">
            {{ value ? $t('common.active') : $t('common.inactive') }}
          </span>
        </template>
        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1 justify-center">
            <Link
              v-if="hasAnyPermission(['suppliers.edit'])"
              :href="`/apps/suppliers/${row.id}/edit`"
              class="mtx-icon-btn"
              :aria-label="$t('common.edit')"
            >
              <i class="fa fa-pencil" aria-hidden="true"></i>
            </Link>
            <button
              v-if="hasAnyPermission(['suppliers.delete'])"
              class="mtx-icon-btn mtx-icon-btn-danger"
              :aria-label="$t('common.delete')"
              @click="handleDelete(row.id)"
            >
              <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
          </div>
        </template>
      </DataTable>
    </div>
  </main>
</template>

<script>
import LayoutApp from "../../../Layouts/App.vue";
import DataTable from "../../../Components/DataTable.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { confirmDelete } from "@/Utils/confirmDialog";
import { useI18n } from "vue-i18n";

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable },
  props: {
    suppliers: Object,
    filters: Object,
  },
  setup(props) {
    const { t } = useI18n();
    const search = ref(props.filters?.search ?? "");
    const filterActive = ref(props.filters?.is_active ?? "");
    const isLoading = ref(false);

    const columns = [
      { key: (row, i) => (props.suppliers.from ?? 1) + i, label: t("suppliers.col_no"), align: "center", width: "60px" },
      { key: "code", label: t("suppliers.col_code"), sortable: true },
      { key: "name", label: t("suppliers.col_name"), sortable: true },
      { key: "contact_person", label: t("suppliers.col_contact_person") },
      { key: "email", label: t("suppliers.col_email") },
      { key: "phone", label: t("suppliers.col_phone") },
      { key: "is_active", label: t("suppliers.col_status"), align: "center" },
      { key: "actions", label: t("common.action"), align: "center", width: "120px" },
    ];

    const applyFilters = () => {
      router.get("/apps/suppliers", {
        search: search.value,
        is_active: filterActive.value,
      }, {
        preserveState: true,
        replace: true,
      });
    };

    const resetFilters = () => {
      search.value = "";
      filterActive.value = "";
      router.get("/apps/suppliers", {}, { preserveState: true, replace: true });
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("suppliers.delete_confirm"))) {
        isLoading.value = true;
        router.delete(`/apps/suppliers/${id}`, {
          onFinish: () => { isLoading.value = false; },
          onError: () => { isLoading.value = false; },
        });
      }
    };

    return { search, filterActive, handleDelete, applyFilters, resetFilters, isLoading, columns };
  },
};
</script>