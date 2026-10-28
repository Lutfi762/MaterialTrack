<template>
    <Head><title>New Production Order - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">New Production Order</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Bill of Materials *</label>
                        <select v-model="form.bom_id" class="mtx-input">
                            <option v-for="b in boms" :key="b.id" :value="b.id">{{ b.code }} - {{ b.name }} (Prod: {{ b.item.name }})</option>
                        </select>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Planned Quantity *</label><input type="number" v-model="form.qty_planned" class="mtx-input" min="1"></div>
                    <div class="mtx-field"><label class="mtx-label">Start Date *</label><input type="date" v-model="form.start_date" class="mtx-input"></div>
                    <button type="submit" class="mtx-btn mtx-btn-primary mt-4" :disabled="form.processing">Save Order</button>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head }, props: { boms: Array },
    setup() {
        const form = useForm({ bom_id: '', qty_planned: 1, start_date: '' });
        return { form, submit: () => form.post('/apps/production-orders') };
    }
}
</script>
