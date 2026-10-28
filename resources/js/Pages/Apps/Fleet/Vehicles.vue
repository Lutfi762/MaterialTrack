<template>
    <Head><title>Fleet Vehicles - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto flex gap-6">
            <div class="w-1/3">
                <div class="mtx-card">
                    <div class="mtx-card-header"><span class="mtx-card-title">Add Vehicle</span></div>
                    <div class="mtx-card-body">
                        <form @submit.prevent="submit">
                            <div class="mtx-field"><label class="mtx-label">License Plate *</label><input v-model="form.license_plate" class="mtx-input uppercase" required></div>
                            <div class="mtx-field"><label class="mtx-label">Vehicle Type *</label><select v-model="form.vehicle_type" class="mtx-input" required><option value="Truck">Truck</option><option value="Pickup">Pickup</option><option value="Van">Van</option><option value="Motorcycle">Motorcycle</option></select></div>
                            <div class="mtx-field"><label class="mtx-label">Brand / Model *</label><input v-model="form.brand_model" class="mtx-input" required></div>
                            <div class="mtx-field"><label class="mtx-label">Capacity (Kg)</label><input type="number" v-model="form.capacity_kg" class="mtx-input"></div>
                            <button type="submit" class="mtx-btn mtx-btn-primary w-full mt-4">Save Vehicle</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="w-2/3 mtx-card p-4">
                <DataTable title="Vehicles Directory" icon="fa-truck" :items="vehicles" :columns="columns" :has-create-permission="false" :has-edit-permission="false" :has-delete-permission="false">
                    <template #cell_status="{ item }">
                        <select :value="item.status" @change="updateStatus(item.id, $event.target.value)" class="text-xs rounded border-gray-300" :class="{'bg-green-100': item.status==='available', 'bg-blue-100': item.status==='in_use', 'bg-red-100': item.status==='maintenance', 'bg-gray-100': item.status==='retired'}">
                            <option value="available">Available</option><option value="in_use">In Use</option><option value="maintenance">Maintenance</option><option value="retired">Retired</option>
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
    layout: LayoutApp, components: { Head, DataTable }, props: { vehicles: Object },
    setup() {
        const form = useForm({ license_plate: '', vehicle_type: 'Truck', brand_model: '', capacity_kg: null, status: 'available' });
        const columns = [
            { key: 'license_plate', label: 'Plate' },
            { key: 'vehicle_type', label: 'Type' },
            { key: 'brand_model', label: 'Brand/Model' },
            { key: 'capacity_kg', label: 'Cap (Kg)' },
            { key: 'status', label: 'Status' }
        ];
        const submit = () => form.post('/apps/fleet/vehicles', { onSuccess: () => form.reset() });
        const updateStatus = (id, status) => router.put(`/apps/fleet/vehicles/${id}`, { status });
        return { form, columns, submit, updateStatus };
    }
}
</script>
