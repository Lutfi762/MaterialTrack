<template>
    <Head><title>Delivery Orders - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto flex gap-6">
            <div class="w-1/3">
                <div class="mtx-card">
                    <div class="mtx-card-header"><span class="mtx-card-title">New Delivery Order</span></div>
                    <div class="mtx-card-body">
                        <form @submit.prevent="submit">
                            <div class="mtx-field"><label class="mtx-label">Customer *</label><select v-model="form.customer_id" class="mtx-input" required><option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
                            <div class="mtx-field"><label class="mtx-label">Vehicle</label><select v-model="form.fleet_vehicle_id" class="mtx-input"><option value="">-- Select --</option><option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.license_plate }} ({{ v.vehicle_type }})</option></select></div>
                            <div class="mtx-field"><label class="mtx-label">Driver</label><select v-model="form.driver_id" class="mtx-input"><option value="">-- Select --</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select></div>
                            <div class="mtx-field"><label class="mtx-label">Date *</label><input type="date" v-model="form.delivery_date" class="mtx-input" required></div>
                            <div class="mtx-field"><label class="mtx-label">Address *</label><textarea v-model="form.delivery_address" class="mtx-textarea" required></textarea></div>
                            <button type="submit" class="mtx-btn mtx-btn-primary w-full mt-4">Save Delivery</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="w-2/3 mtx-card p-4">
                <DataTable title="Deliveries" icon="fa-box" :items="deliveries" :columns="columns" :has-create-permission="false" :has-edit-permission="false" :has-delete-permission="false">
                    <template #cell_customer="{ item }">{{ item.customer.name }}</template>
                    <template #cell_vehicle="{ item }">{{ item.vehicle?.license_plate || 'Unassigned' }}</template>
                    <template #cell_driver="{ item }">{{ item.driver?.name || 'Unassigned' }}</template>
                    <template #cell_status="{ item }">
                        <select :value="item.status" @change="updateStatus(item.id, $event.target.value)" class="text-xs rounded border-gray-300">
                            <option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="in_transit">In Transit</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option>
                        </select>
                    </template>
                </DataTable>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head, DataTable }, props: { deliveries: Object, customers: Array, vehicles: Array, users: Array },
    setup() {
        const form = useForm({ customer_id: '', fleet_vehicle_id: '', driver_id: '', delivery_date: '', delivery_address: '', status: 'draft', notes: '' });
        const columns = [
            { key: 'do_number', label: 'DO Number' },
            { key: 'customer', label: 'Customer' },
            { key: 'delivery_date', label: 'Date' },
            { key: 'vehicle', label: 'Vehicle' },
            { key: 'driver', label: 'Driver' },
            { key: 'status', label: 'Status' }
        ];
        const submit = () => form.post('/apps/fleet/deliveries', { onSuccess: () => form.reset() });
        const updateStatus = (id, status) => router.put(`/apps/fleet/deliveries/${id}`, { status });
        return { form, columns, submit, updateStatus };
    }
}
</script>
