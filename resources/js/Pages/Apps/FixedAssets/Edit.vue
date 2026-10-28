<template>
    <Head><title>{{ $t('fixed_assets.edit') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto mtx-card">
            <div class="mtx-card-header"><span class="mtx-card-title">{{ $t('fixed_assets.edit') }}</span></div>
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
                        <div class="mtx-field"><label class="mtx-label">Status *</label><select v-model="form.status" class="mtx-input"><option value="active">Active</option><option value="disposed">Disposed</option><option value="fully_depreciated">Fully Depreciated</option></select></div>
                        <div class="mtx-field"><label class="mtx-label">Asset Account</label><select v-model="form.asset_account_id" class="mtx-input"><option :value="null">-</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} {{ a.name }}</option></select></div>
                    </div>
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
    layout: LayoutApp, components: { Head }, props: { fixedAsset: Object, departments: Array, accounts: Array },
    setup(props) {
        const form = useForm({
            code: props.fixedAsset.code, name: props.fixedAsset.name, description: props.fixedAsset.description, category: props.fixedAsset.category, acquisition_date: props.fixedAsset.acquisition_date,
            acquisition_cost: props.fixedAsset.acquisition_cost, useful_life_months: props.fixedAsset.useful_life_months, salvage_value: props.fixedAsset.salvage_value, depreciation_method: props.fixedAsset.depreciation_method,
            department_id: props.fixedAsset.department_id, asset_account_id: props.fixedAsset.asset_account_id, depreciation_account_id: props.fixedAsset.depreciation_account_id, accumulated_dep_account_id: props.fixedAsset.accumulated_dep_account_id, notes: props.fixedAsset.notes,
            status: props.fixedAsset.status
        });
        const submit = () => form.put(`/apps/fixed-assets/${props.fixedAsset.id}`);
        return { form, submit };
    }
}
</script>
