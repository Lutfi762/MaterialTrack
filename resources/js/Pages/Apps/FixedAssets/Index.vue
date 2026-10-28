<template>
    <Head><title>{{ $t('fixed_assets.title') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable
                :title="$t('fixed_assets.title')"
                icon="fa-building"
                :items="fixedAssets"
                :filters="filters"
                :columns="columns"
                search-placeholder="fixed_assets.search_placeholder"
                create-route="/apps/fixed-assets/create"
                :create-text="$t('fixed_assets.add')"
                :has-create-permission="hasAnyPermission(['fixed_assets.create'])"
                :has-edit-permission="hasAnyPermission(['fixed_assets.edit'])"
                :has-delete-permission="hasAnyPermission(['fixed_assets.delete'])"
                edit-route-prefix="/apps/fixed-assets"
                @delete="confirmDelete"
            >
                <template #filter>
                    <select v-model="filterStatus" class="mtx-input mtx-input-sm w-32" @change="applyFilter">
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="disposed">Disposed</option>
                        <option value="fully_depreciated">Fully Depreciated</option>
                    </select>
                </template>
                <template #cell_current_book_value="{ item }">
                    Rp {{ Number(item.current_book_value).toLocaleString('id-ID') }}
                </template>
                <template #cell_status="{ item }">
                    <span :class="{'bg-green-100 text-green-800': item.status === 'active', 'bg-red-100 text-red-800': item.status === 'disposed', 'bg-yellow-100 text-yellow-800': item.status === 'fully_depreciated'}" class="px-2 py-1 text-xs font-semibold rounded-full">
                        {{ item.status }}
                    </span>
                </template>
                <template #custom_actions="{ item }">
                    <button v-if="item.status === 'active' && hasAnyPermission(['fixed_assets.create'])" @click="runDepreciate(item.id)" class="text-indigo-600 hover:text-indigo-900 ml-2" :title="$t('fixed_assets.depreciate_confirm')">
                        <i class="fa fa-calculator"></i>
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
    props: { fixedAssets: Object, filters: Object },
    setup(props) {
        const { t } = useI18n();
        const filterStatus = ref(props.filters.status || '');
        const columns = [
            { key: 'code', label: 'fixed_assets.col_code' },
            { key: 'name', label: 'fixed_assets.col_name' },
            { key: 'category', label: 'fixed_assets.col_category' },
            { key: 'current_book_value', label: 'fixed_assets.col_book_value' },
            { key: 'status', label: 'fixed_assets.col_status' }
        ];

        const applyFilter = () => {
            router.get('/apps/fixed-assets', { ...props.filters, status: filterStatus.value, page: 1 }, { preserveState: true });
        };

        const confirmDelete = (id) => {
            Swal.fire({
                title: t('common.delete_confirm'),
                text: t('fixed_assets.delete_confirm'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: t('common.delete'),
                cancelButtonText: t('common.cancel')
            }).then((result) => {
                if (result.isConfirmed) router.delete(`/apps/fixed-assets/${id}`);
            });
        };

        const runDepreciate = (id) => {
            Swal.fire({
                title: t('fixed_assets.depreciate_confirm'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Ya',
                cancelButtonText: t('common.cancel')
            }).then((result) => {
                if (result.isConfirmed) router.post(`/apps/fixed-assets/${id}/depreciate`);
            });
        };

        return { columns, confirmDelete, filterStatus, applyFilter, runDepreciate };
    }
}
</script>
