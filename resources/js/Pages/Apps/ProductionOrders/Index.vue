<template>
    <Head><title>Production Orders - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable title="Production Orders" icon="fa-industry" :items="orders" :columns="columns" create-route="/apps/production-orders/create" create-text="New Order" :has-create-permission="hasAnyPermission(['production_orders.create'])" :has-edit-permission="false" :has-delete-permission="false">
                <template #cell_bom="{ item }">{{ item.bom.name }}</template>
                <template #cell_item="{ item }">{{ item.item.name }}</template>
                <template #cell_status="{ item }">
                    <span :class="{'bg-blue-100 text-blue-800': item.status==='planned', 'bg-green-100 text-green-800': item.status==='completed'}" class="px-2 py-1 text-xs font-semibold rounded-full">{{ item.status }}</span>
                </template>
                <template #custom_actions="{ item }">
                    <button v-if="item.status === 'planned' && hasAnyPermission(['production_orders.complete'])" @click="complete(item.id)" class="text-green-600 hover:text-green-900 ml-2" title="Mark as Completed"><i class="fa fa-check"></i></button>
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
    layout: LayoutApp, components: { Head, DataTable }, props: { orders: Object },
    setup() {
        const columns = [
            { key: 'order_number', label: 'Order No' },
            { key: 'bom', label: 'BOM' },
            { key: 'item', label: 'Product' },
            { key: 'qty_planned', label: 'Planned Qty' },
            { key: 'start_date', label: 'Start Date' },
            { key: 'status', label: 'Status' }
        ];
        const complete = (id) => {
            Swal.fire({ title: 'Complete Order?', text: 'Raw materials will be deducted and finished goods added to stock.', icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981', confirmButtonText: 'Complete' }).then((res) => {
                if (res.isConfirmed) router.post(`/apps/production-orders/${id}/complete`);
            });
        };
        return { columns, complete };
    }
}
</script>
