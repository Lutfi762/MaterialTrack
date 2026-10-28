<template>
    <Head><title>Bill of Materials - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable title="Bill of Materials" icon="fa-box" :items="boms" :filters="filters" :columns="columns" search-placeholder="Search BOM name..." create-route="/apps/boms/create" create-text="Add BOM" :has-create-permission="hasAnyPermission(['boms.create'])" :has-delete-permission="hasAnyPermission(['boms.delete'])" @delete="confirmDelete">
                <template #cell_item="{ item }">{{ item.item.name }}</template>
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
    layout: LayoutApp, components: { Head, DataTable }, props: { boms: Object, filters: Object },
    setup() {
        const columns = [
            { key: 'code', label: 'Code' },
            { key: 'name', label: 'BOM Name' },
            { key: 'item', label: 'Finished Good' }
        ];
        const confirmDelete = (id) => {
            Swal.fire({ title: 'Delete BOM?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Delete' }).then((res) => {
                if (res.isConfirmed) router.delete(`/apps/boms/${id}`);
            });
        };
        return { columns, confirmDelete };
    }
}
</script>
