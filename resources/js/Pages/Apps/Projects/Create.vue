<template>
    <Head><title>New Project - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">New Project</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Name *</label><input v-model="form.name" class="mtx-input"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mtx-field"><label class="mtx-label">Start Date *</label><input type="date" v-model="form.start_date" class="mtx-input"></div>
                        <div class="mtx-field"><label class="mtx-label">End Date</label><input type="date" v-model="form.end_date" class="mtx-input"></div>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Budget</label><input type="number" v-model="form.budget" class="mtx-input" min="0"></div>
                    <div class="mtx-field"><label class="mtx-label">Description</label><textarea v-model="form.description" class="mtx-textarea"></textarea></div>
                    <button type="submit" class="mtx-btn mtx-btn-primary mt-4" :disabled="form.processing">Create Project</button>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head },
    setup() {
        const form = useForm({ name: '', start_date: '', end_date: '', budget: 0, description: '' });
        return { form, submit: () => form.post('/apps/projects') };
    }
}
</script>
