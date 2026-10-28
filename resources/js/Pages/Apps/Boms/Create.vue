<template>
    <Head><title>Create BOM - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-4xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">Create Bill of Materials</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mtx-field"><label class="mtx-label">Code *</label><input v-model="form.code" class="mtx-input"></div>
                        <div class="mtx-field"><label class="mtx-label">Name *</label><input v-model="form.name" class="mtx-input"></div>
                        <div class="mtx-field"><label class="mtx-label">Finished Good Item *</label><select v-model="form.item_id" class="mtx-input"><option v-for="i in items" :key="i.id" :value="i.id">{{ i.code }} - {{ i.name }}</option></select></div>
                    </div>
                    <div class="mt-6 border-t pt-4">
                        <h4 class="font-bold mb-2">Raw Materials</h4>
                        <div v-for="(row, idx) in form.items" :key="idx" class="flex gap-2 mb-2">
                            <select v-model="row.item_id" class="mtx-input flex-1"><option v-for="i in items" :key="i.id" :value="i.id">{{ i.code }} - {{ i.name }}</option></select>
                            <input type="number" v-model="row.quantity" class="mtx-input w-32" step="0.01" min="0.01" placeholder="Qty">
                            <button type="button" @click="form.items.splice(idx, 1)" class="mtx-btn mtx-btn-secondary mtx-btn-sm text-red-600"><i class="fa fa-times"></i></button>
                        </div>
                        <button type="button" @click="form.items.push({item_id: '', quantity: 1})" class="mtx-btn mtx-btn-secondary mtx-btn-sm mt-2">+ Add Material</button>
                    </div>
                    <button type="submit" class="mtx-btn mtx-btn-primary mt-6" :disabled="form.processing">Save BOM</button>
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
        const form = useForm({ code: '', name: '', item_id: '', items: [] });
        return { form, submit: () => form.post('/apps/boms') };
    }
}
</script>
