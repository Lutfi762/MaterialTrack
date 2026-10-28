<template>
    <Head><title>Projects - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable title="Projects" icon="fa-project-diagram" :items="projects" :filters="filters" :columns="columns" search-placeholder="Search Project..." create-route="/apps/projects/create" create-text="New Project" :has-create-permission="hasAnyPermission(['projects.create'])" :has-edit-permission="false" :has-delete-permission="hasAnyPermission(['projects.delete'])" @delete="confirmDelete">
                <template #cell_code="{ item }">
                    <Link :href="`/apps/projects/${item.id}`" class="text-blue-600 font-bold hover:underline">{{ item.code }}</Link>
                </template>
                <template #cell_budget="{ item }">Rp {{ Number(item.budget).toLocaleString('id-ID') }}</template>
                <template #cell_status="{ item }">
                    <span :class="{'bg-gray-100 text-gray-800': item.status==='planning', 'bg-blue-100 text-blue-800': item.status==='in_progress', 'bg-yellow-100 text-yellow-800': item.status==='on_hold', 'bg-green-100 text-green-800': item.status==='completed'}" class="px-2 py-1 text-xs font-semibold rounded-full">{{ item.status.replace('_',' ') }}</span>
                </template>
            </DataTable>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp, components: { Head, DataTable, Link }, props: { projects: Object, filters: Object },
    setup() {
        const columns = [
            { key: 'code', label: 'Code' },
            { key: 'name', label: 'Project Name' },
            { key: 'start_date', label: 'Start Date' },
            { key: 'budget', label: 'Budget' },
            { key: 'status', label: 'Status' }
        ];
        const confirmDelete = (id) => {
            Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Delete' }).then((res) => {
                if (res.isConfirmed) router.delete(`/apps/projects/${id}`);
            });
        };
        return { columns, confirmDelete };
    }
}
</script>
