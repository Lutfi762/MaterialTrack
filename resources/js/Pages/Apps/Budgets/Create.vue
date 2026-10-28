<template>
    <Head><title>{{ $t('budgets.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">{{ $t('budgets.add') }}</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Fiscal Year *</label>
                        <select v-model="form.fiscal_year_id" class="mtx-input" :class="{'mtx-input-error': form.errors.fiscal_year_id}"><option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.name }}</option></select>
                        <p v-if="form.errors.fiscal_year_id" class="mtx-error-text">{{ form.errors.fiscal_year_id }}</p>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Department</label>
                        <select v-model="form.department_id" class="mtx-input"><option :value="null">-</option><option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option></select>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Account *</label>
                        <select v-model="form.chart_of_account_id" class="mtx-input" :class="{'mtx-input-error': form.errors.chart_of_account_id}"><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option></select>
                        <p v-if="form.errors.chart_of_account_id" class="mtx-error-text">{{ form.errors.chart_of_account_id }}</p>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Amount *</label><input type="number" v-model="form.budget_amount" class="mtx-input" min="0"></div>
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
    layout: LayoutApp, components: { Head }, props: { fiscalYears: Array, departments: Array, accounts: Array },
    setup() {
        const form = useForm({ fiscal_year_id: '', department_id: null, chart_of_account_id: '', budget_amount: 0, notes: '' });
        const submit = () => form.post('/apps/budgets');
        return { form, submit };
    }
}
</script>
