<template>
    <Head><title>{{ $t('budgets.title') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable
                :title="$t('budgets.title')"
                icon="fa-list-alt"
                :items="budgets"
                :filters="filters"
                :columns="columns"
                search-placeholder="budgets.search_placeholder"
                create-route="/apps/budgets/create"
                :create-text="$t('budgets.add')"
                :has-create-permission="hasAnyPermission(['budgets.create'])"
                :has-edit-permission="hasAnyPermission(['budgets.edit'])"
                :has-delete-permission="hasAnyPermission(['budgets.delete'])"
                edit-route-prefix="/apps/budgets"
                @delete="confirmDelete"
            >
                <template #filter>
                    <select v-model="filterFY" class="mtx-input mtx-input-sm w-32" @change="applyFilter">
                        <option value="">Semua Tahun</option>
                        <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.name }}</option>
                    </select>
                </template>
                <template #cell_department="{ item }">
                    {{ item.department ? item.department.name : '-' }}
                </template>
                <template #cell_account="{ item }">
                    {{ item.account.code }} - {{ item.account.name }}
                </template>
                <template #cell_budget_amount="{ item }">
                    Rp {{ Number(item.budget_amount).toLocaleString('id-ID') }}
                </template>
                <template #cell_realized_amount="{ item }">
                    Rp {{ Number(item.realized_amount).toLocaleString('id-ID') }}
                </template>
                <template #cell_usage_percent="{ item }">
                    {{ item.usage_percent }}%
                </template>
                <template #cell_remaining="{ item }">
                    <span :class="{'text-red-600': item.remaining < 0}">Rp {{ Number(item.remaining).toLocaleString('id-ID') }}</span>
                </template>
                <template #custom_actions="{ item }">
                    <button v-if="hasAnyPermission(['budgets.create'])" @click="refreshRealization(item.id)" class="text-indigo-600 hover:text-indigo-900 ml-2" :title="$t('budgets.refresh_realization')">
                        <i class="fa fa-sync"></i>
                    </button>
                </template>
            </DataTable>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, DataTable },
    props: { budgets: Object, filters: Object, fiscalYears: Array },
    setup(props) {
        const { t } = useI18n();
        const filterFY = ref(props.filters.fiscal_year_id || '');
        const columns = [
            { key: 'fiscal_year.name', label: 'budgets.col_fiscal_year' },
            { key: 'department', label: 'budgets.col_department' },
            { key: 'account', label: 'budgets.col_account' },
            { key: 'budget_amount', label: 'budgets.col_budget' },
            { key: 'realized_amount', label: 'budgets.col_realized' },
            { key: 'usage_percent', label: 'budgets.col_usage' },
            { key: 'remaining', label: 'budgets.col_remaining' }
        ];

        const applyFilter = () => {
            router.get('/apps/budgets', { ...props.filters, fiscal_year_id: filterFY.value, page: 1 }, { preserveState: true });
        };

        const confirmDelete = (id) => {
            Swal.fire({
                title: t('common.delete_confirm'),
                text: t('budgets.delete_confirm'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: t('common.delete'),
                cancelButtonText: t('common.cancel')
            }).then((result) => {
                if (result.isConfirmed) router.delete(`/apps/budgets/${id}`);
            });
        };

        const refreshRealization = (id) => {
            router.post(`/apps/budgets/${id}/update-realization`, {}, { preserveScroll: true });
        };

        return { columns, confirmDelete, filterFY, applyFilter, refreshRealization };
    }
}
</script>
