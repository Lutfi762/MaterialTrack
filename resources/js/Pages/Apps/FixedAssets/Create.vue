<template>
    <Head><title>{{ $t('fixed_assets.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">{{ $t('fixed_assets.add') }}</span></div>
            <div class="mtx-card-body">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mtx-field"><label class="mtx-label">Code *</label><input v-model="form.code" class="mtx-input"></div>
                        <div class="mtx-field"><label class="mtx-label">Name *</label><input v-model="form.name" class="mtx-input"></div>
                        <div class="mtx-field"><label class="mtx-label">Category *</label>
                            <select v-model="form.category" class="mtx-input"><option value="building">Building</option><option value="vehicle">Vehicle</option><option value="machinery">Machinery</option><option value="equipment">Equipment</option><option value="furniture">Furniture</option><option value="other">Other</option></select>
                        </div>
                        <div class="mtx-field"><label class="mtx-label">Date *</label><input type="date" v-model="form.acquisition_date" class="mtx-input"></div>
                        <div class="mtx-field"><label class="mtx-label">Cost *</label><input type="number" v-model="form.acquisition_cost" class="mtx-input" min="0"></div>
                        <div class="mtx-field"><label class="mtx-label">Useful Life (months) *</label><input type="number" v-model="form.useful_life_months" class="mtx-input" min="1"></div>
                        <div class="mtx-field"><label class="mtx-label">Asset Account</label><select v-model="form.asset_account_id" class="mtx-input"><option :value="null">-</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} {{ a.name }}</option></select></div>
                        <div class="mtx-field"><label class="mtx-label">Dep Account</label><select v-model="form.depreciation_account_id" class="mtx-input"><option :value="null">-</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} {{ a.name }}</option></select></div>
                        <div class="mtx-field"><label class="mtx-label">Acc Dep Account</label><select v-model="form.accumulated_dep_account_id" class="mtx-input"><option :value="null">-</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} {{ a.name }}</option></select></div>
                    </div>
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
    layout: LayoutApp, components: { Head }, props: { departments: Array, accounts: Array },
    setup() {
        const form = useForm({
            code: '', name: '', description: '', category: 'building', acquisition_date: '',
            acquisition_cost: 0, useful_life_months: 12, salvage_value: 0, depreciation_method: 'straight_line',
            department_id: null, asset_account_id: null, depreciation_account_id: null, accumulated_dep_account_id: null, notes: ''
        });
        const submit = () => form.post('/apps/fixed-assets');
        return { form, submit };
    }
}
</script>
