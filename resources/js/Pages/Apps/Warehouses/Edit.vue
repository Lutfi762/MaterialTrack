<template>
    <Head><title>{{ $t('warehouses.edit') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-warehouse text-indigo-600" aria-hidden="true"></i> {{ $t('warehouses.edit') }}</span>
                    <Link href="/apps/warehouses" class="mtx-btn mtx-btn-secondary mtx-btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}</Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('warehouses.name') }} <span class="mtx-required">*</span></label>
                            <input v-model="form.name" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }" required>
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('warehouses.code') }} <span class="mtx-required">*</span></label>
                            <input v-model="form.code" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.code }" required>
                            <p v-if="form.errors.code" class="mtx-error-text">{{ form.errors.code }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('warehouses.address') }}</label>
                            <textarea v-model="form.address" class="mtx-textarea" rows="3"></textarea>
                        </div>
                        <div class="mtx-field mtx-checkbox-row">
                            <input v-model="form.is_active" type="checkbox" class="mtx-checkbox" id="is_active">
                            <label class="mtx-checkbox-label" for="is_active">{{ $t('warehouses.active') }}</label>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">
                                <i class="fa fa-save" aria-hidden="true"></i> {{ $t('common.update') }}
                            </button>
                            <Link href="/apps/warehouses" class="mtx-btn mtx-btn-secondary">{{ $t('common.cancel') }}</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        warehouse: Object,
    },
    setup(props) {
        const form = useForm({
            name: props.warehouse.name,
            code: props.warehouse.code,
            address: props.warehouse.address || '',
            is_active: props.warehouse.is_active,
        });

        const submit = () => {
            form.put('/apps/warehouses/' + props.warehouse.id);
        };

        return { form, submit };
    },
};
</script>
