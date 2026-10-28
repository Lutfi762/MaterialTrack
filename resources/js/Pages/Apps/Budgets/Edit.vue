<template>
    <Head><title>{{ $t('budgets.edit') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">{{ $t('budgets.edit') }}</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Amount *</label><input type="number" v-model="form.budget_amount" class="mtx-input" min="0"></div>
                    <div class="mtx-field"><label class="mtx-label">Notes</label><textarea v-model="form.notes" class="mtx-textarea"></textarea></div>
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
    layout: LayoutApp, components: { Head }, props: { budget: Object },
    setup(props) {
        const form = useForm({ budget_amount: props.budget.budget_amount, notes: props.budget.notes });
        const submit = () => form.put(`/apps/budgets/${props.budget.id}`);
        return { form, submit };
    }
}
</script>
