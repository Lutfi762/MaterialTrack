<template>
    <Head><title>Quality Control - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable title="Quality Control" icon="fa-check-circle" :items="qcs" :filters="filters" :columns="columns" search-placeholder="Search QC Number..." create-route="/apps/quality-controls/create" create-text="Add QC Record" :has-create-permission="hasAnyPermission(['quality_controls.create'])" :has-edit-permission="false" :has-delete-permission="hasAnyPermission(['quality_controls.delete'])" @delete="confirmDelete">
                <template #cell_item="{ item }">{{ item.item.name }}</template>
                <template #cell_status="{ item }">
                    <span :class="{'bg-green-100 text-green-800': item.status==='passed', 'bg-red-100 text-red-800': item.status==='failed', 'bg-yellow-100 text-yellow-800': item.status==='partial'}" class="px-2 py-1 text-xs font-semibold rounded-full">{{ item.status }}</span>
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
    layout: LayoutApp, components: { Head, DataTable }, props: { qcs: Object, filters: Object },
    setup() {
        const columns = [
            { key: 'qc_number', label: 'QC No' },
            { key: 'reference_number', label: 'Reference' },
            { key: 'item', label: 'Item' },
            { key: 'qty_inspected', label: 'Inspected' },
            { key: 'qty_passed', label: 'Passed' },
            { key: 'qty_rejected', label: 'Rejected' },
            { key: 'status', label: 'Status' }
        ];
        const confirmDelete = (id) => {
            Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Delete' }).then((res) => {
                if (res.isConfirmed) router.delete(`/apps/quality-controls/${id}`);
            });
        };
        return { columns, confirmDelete };
    }
}
</script>