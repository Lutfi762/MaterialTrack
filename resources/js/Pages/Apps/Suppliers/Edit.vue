<template>
    <Head><title>{{ $t('suppliers.edit') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header"><span class="font-weight-bold">{{ $t('suppliers.edit') }}</span></div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.name') }} <span class="text-danger">*</span></label>
                                            <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }">
                                            <div class="invalid-feedback">{{ form.errors.name }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.code') }}</label>
                                            <input v-model="form.code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.code }">
                                            <div class="invalid-feedback">{{ form.errors.code }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.contact_person') }}</label>
                                            <input v-model="form.contact_person" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.email') }}</label>
                                            <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }">
                                            <div class="invalid-feedback">{{ form.errors.email }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.phone') }}</label>
                                            <input v-model="form.phone" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.lead_time_days') }}</label>
                                            <input v-model="form.lead_time_days" type="number" class="form-control" min="0">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-semibold">{{ $t('suppliers.rating') }}</label>
                                            <input v-model="form.rating" type="number" class="form-control" min="0" max="5" step="0.01">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('suppliers.address') }}</label>
                                        <textarea v-model="form.address" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('suppliers.notes') }}</label>
                                        <textarea v-model="form.notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input v-model="form.is_active" type="checkbox" class="form-check-input" id="is_active">
                                        <label class="form-check-label" for="is_active">{{ $t('suppliers.is_active') }}</label>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-warning" :disabled="form.processing">Update</button>
                                        <Link href="/apps/suppliers" class="btn btn-secondary">{{ $t('common.cancel') }}</Link>
                                    </div>
                                </form>
                            </div>
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
    props: { supplier: Object },
    setup(props) {
        const form = useForm({
            name: props.supplier.name,
            code: props.supplier.code ?? '',
            contact_person: props.supplier.contact_person ?? '',
            email: props.supplier.email ?? '',
            phone: props.supplier.phone ?? '',
            address: props.supplier.address ?? '',
            lead_time_days: props.supplier.lead_time_days ?? 7,
            rating: props.supplier.rating ?? 0,
            notes: props.supplier.notes ?? '',
            is_active: props.supplier.is_active ?? true,
        });
        const submit = () => form.put(`/apps/suppliers/${props.supplier.id}`);
        return { form, submit };
    }
}
</script>
