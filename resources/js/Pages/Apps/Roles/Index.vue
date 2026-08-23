<template>
  <Head><title>{{ $t('roles.heading') }} - MaterialTrack</title></Head>

  <main class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fa fa-shield-alt text-indigo-600"></i>
        {{ $t('roles.heading') }}
      </h2>
      <div class="flex items-center gap-4">
        <div class="search-box">
          <input
            type="text"
            v-model="search"
            :placeholder="$t('roles.search_placeholder')"
            class="search-input"
            @input="handleSearch"
          />
          <i class="fa fa-search search-icon"></i>
        </div>
        <Link
          v-if="hasAnyPermission(['roles.create'])"
          href="/apps/roles/create"
          class="btn-modern btn-modern-primary"
        >
          <i class="fa fa-plus me-2"></i> {{ $t('common.new') }}
        </Link>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :rows="roles.data"
      :pagination="roles"
      row-key="id"
      :striped="true"
      :loading="false"
      :empty-text="$t('roles.empty')"
    >
      <template #cell-name="{ row }">
        <div class="flex items-center gap-2">
          {{ row.name }}
          <span v-if="isLockedRole(row.name)" class="badge bg-secondary ms-2">
            <i class="fa fa-lock me-1"></i> {{ $t('roles.locked') }}
          </span>
        </div>
      </template>
      <template #cell-permissions="{ row }">
        <div class="flex flex-wrap gap-1">
          <span
            v-for="(permission, i) in row.permissions"
            :key="i"
            class="badge bg-primary shadow border-0"
          >
            {{ permission.name }}
          </span>
        </div>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-center gap-2">
          <template v-if="!isLockedRole(row.name)">
            <Link
              v-if="hasAnyPermission(['roles.edit'])"
              :href="`/apps/roles/${row.id}/edit`"
              class="act-btn act-btn-success"
              :aria-label="$t('common.edit')"
            >
              <i class="fa fa-pencil-alt"></i>
            </Link>
            <button
              v-if="hasAnyPermission(['roles.delete'])"
              class="act-btn act-btn-danger"
              :aria-label="$t('common.delete')"
              @click="handleDelete(row.id)"
            >
              <i class="fa fa-trash"></i>
            </button>
          </template>
          <span v-else class="text-muted small">-</span>
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

const LOCKED_ROLES = ["department_user"];

export default {
  layout: LayoutApp,
  components: { Head, Link, DataTable, Pagination },
  props: { roles: Object },
  setup() {
    const { t } = useI18n();
    const search = ref(
      (new URL(document.location)).searchParams.get("q") ?? ""
    );

    const columns = ref([
      { key: "name", label: t("roles.col_name"), sortable: true },
      { key: "permissions", label: t("roles.col_permissions") },
      { key: "actions", label: t("common.action"), align: "center", width: "150px" },
    ]);

    const handleSearch = () => {
      router.get("/apps/roles", { q: search.value }, { preserveState: true, replace: true });
    };

    const handleDelete = async (id) => {
      if (await confirmDelete(t("roles.delete_confirm"))) {
        router.delete(`/apps/roles/${id}`);
      }
    };

    const isLockedRole = (roleName) => LOCKED_ROLES.includes(roleName);

    return { search, handleSearch, handleDelete, isLockedRole, columns };
  },
};
</script>
