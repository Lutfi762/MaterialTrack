<template>
    <Head><title>Record QC - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">Record Quality Control</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Reference Number (PO/GR No)</label><input v-model="form.reference_number" class="mtx-input" placeholder="e.g. GR-0001"></div>
                    <div class="mtx-field"><label class="mtx-label">Item *</label><select v-model="form.item_id" class="mtx-input"><option v-for="i in items" :key="i.id" :value="i.id">{{ i.code }} - {{ i.name }}</option></select></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mtx-field"><label class="mtx-label">Qty Inspected *</label><input type="number" v-model="form.qty_inspected" class="mtx-input" min="1"></div>
                        <div class="mtx-field"><label class="mtx-label">Qty Passed *</label><input type="number" v-model="form.qty_passed" class="mtx-input" min="0"></div>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Inspector Name</label><input v-model="form.inspector_name" class="mtx-input"></div>
                    <div class="mtx-field"><label class="mtx-label">Notes</label><textarea v-model="form.notes" class="mtx-textarea"></textarea></div>
                    <button type="submit" class="mtx-btn mtx-btn-primary mt-4" :disabled="form.processing">Save Record</button>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head }, props: { items: Array },
    setup() {
        const form = useForm({ reference_number: '', item_id: '', qty_inspected: 1, qty_passed: 1, inspector_name: '', notes: '' });
        return { form, submit: () => form.post('/apps/quality-controls') };
    }
}
</script>