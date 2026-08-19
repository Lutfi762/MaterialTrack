<template>
    <Head><title>{{ $t('warehouses.edit') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-xl">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold"><i class="fa fa-warehouse me-1"></i> {{ $t('warehouses.edit') }}</span>
                            <Link href="/apps/warehouses" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('warehouses.name') }} <span class="text-danger">*</span></label>
                                    <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text" required>
                                    <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('warehouses.code') }} <span class="text-danger">*</span></label>
                                    <input class="form-control" v-model="form.code" :class="{ 'is-invalid': form.errors.code }" type="text" required>
                                    <div v-if="form.errors.code" class="invalid-feedback d-block">{{ form.errors.code }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('warehouses.address') }}</label>
                                    <textarea class="form-control" v-model="form.address" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" v-model="form.is_active" id="isActive">
                                        <label class="form-check-label" for="isActive">{{ $t('warehouses.active') }}</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <i class="fa fa-save me-1"></i> {{ $t('common.update') }}
                                </button>
                            </form>
                        </div>
                    </div>
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
