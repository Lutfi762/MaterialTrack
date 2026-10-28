<template>
    <Head><title>Schedule Maintenance - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">Schedule Maintenance</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Asset *</label><select v-model="form.fixed_asset_id" class="mtx-input"><option v-for="a in assets" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option></select></div>
                    <div class="mtx-field"><label class="mtx-label">Title *</label><input v-model="form.title" class="mtx-input"></div>
                    <div class="mtx-field"><label class="mtx-label">Type *</label><select v-model="form.maintenance_type" class="mtx-input"><option value="preventive">Preventive</option><option value="corrective">Corrective</option></select></div>
                    <div class="mtx-field"><label class="mtx-label">Scheduled Date *</label><input type="date" v-model="form.scheduled_date" class="mtx-input"></div>
                    <div class="mtx-field"><label class="mtx-label">Estimated Cost</label><input type="number" v-model="form.cost" class="mtx-input" min="0"></div>
                    <div class="mtx-field"><label class="mtx-label">Performed By (Mechanic/Vendor)</label><input v-model="form.performed_by" class="mtx-input"></div>
                    <button type="submit" class="mtx-btn mtx-btn-primary mt-4" :disabled="form.processing">Save</button>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head }, props: { assets: Array },
    setup() {
        const form = useForm({ fixed_asset_id: '', title: '', maintenance_type: 'preventive', scheduled_date: '', cost: 0, performed_by: '' });
        return { form, submit: () => form.post('/apps/asset-maintenances') };
    }
}
</script>