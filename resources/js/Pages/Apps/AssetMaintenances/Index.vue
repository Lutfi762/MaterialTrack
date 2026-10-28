<template>
    <Head><title>Asset Maintenance - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable title="Asset Maintenance" icon="fa-wrench" :items="maintenances" :columns="columns" create-route="/apps/asset-maintenances/create" create-text="Add Maintenance" edit-route-prefix="/apps/asset-maintenances" :has-create-permission="hasAnyPermission(['asset_maintenances.create'])" :has-edit-permission="hasAnyPermission(['asset_maintenances.edit'])" :has-delete-permission="hasAnyPermission(['asset_maintenances.delete'])" @delete="confirmDelete">
                <template #cell_asset="{ item }">{{ item.fixed_asset.code }} - {{ item.fixed_asset.name }}</template>
                <template #cell_cost="{ item }">Rp {{ Number(item.cost).toLocaleString('id-ID') }}</template>
                <template #cell_status="{ item }">
                    <span :class="{'bg-yellow-100 text-yellow-800': item.status==='scheduled', 'bg-blue-100 text-blue-800': item.status==='in_progress', 'bg-green-100 text-green-800': item.status==='completed', 'bg-red-100 text-red-800': item.status==='cancelled'}" class="px-2 py-1 text-xs font-semibold rounded-full">{{ item.status }}</span>
                </template>
            </DataTable>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
export default {
    layout: LayoutApp, components: { Head, DataTable }, props: { maintenances: Object },
    setup() {
        const columns = [
            { key: 'asset', label: 'Asset' },
            { key: 'title', label: 'Title' },
            { key: 'maintenance_type', label: 'Type' },
            { key: 'scheduled_date', label: 'Scheduled' },
            { key: 'status', label: 'Status' },
            { key: 'cost', label: 'Cost' }
        ];
        const confirmDelete = (id) => {
            Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Delete' }).then((res) => {
                if (res.isConfirmed) router.delete(`/apps/asset-maintenances/${id}`);
            });
        };
        return { columns, confirmDelete };
    }
}
</script>