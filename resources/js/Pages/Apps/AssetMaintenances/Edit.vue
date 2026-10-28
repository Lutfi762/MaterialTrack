<template>
    <Head><title>Update Maintenance - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">Update Maintenance</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Status *</label><select v-model="form.status" class="mtx-input"><option value="scheduled">Scheduled</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
                    <div class="mtx-field" v-if="form.status === 'completed'"><label class="mtx-label">Completed Date</label><input type="date" v-model="form.completed_date" class="mtx-input"></div>
                    <div class="mtx-field"><label class="mtx-label">Actual Cost</label><input type="number" v-model="form.cost" class="mtx-input" min="0"></div>
                    <div class="mtx-field"><label class="mtx-label">Performed By</label><input v-model="form.performed_by" class="mtx-input"></div>
                    <div class="mtx-field"><label class="mtx-label">Notes/Description</label><textarea v-model="form.description" class="mtx-textarea"></textarea></div>
                    <button type="submit" class="mtx-btn mtx-btn-primary mt-4" :disabled="form.processing">Update</button>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head }, props: { maintenance: Object },
    setup(props) {
        const form = useForm({ status: props.maintenance.status, completed_date: props.maintenance.completed_date, cost: props.maintenance.cost, performed_by: props.maintenance.performed_by, description: props.maintenance.description });
        return { form, submit: () => form.put(`/apps/asset-maintenances/${props.maintenance.id}`) };
    }
}
</script>