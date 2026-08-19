<template>
    <Head><title>Edit Department - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-building me-1"></i> {{ $t('departments.edit') }}</span>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('departments.name') }} <span class="text-danger">*</span></label>
                                        <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }">
                                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('departments.code') }}</label>
                                        <input v-model="form.code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.code }">
                                        <div class="invalid-feedback">{{ form.errors.code }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('common.status') }}</label>
                                        <select v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }">
                                            <option value="active">{{ $t('common.active') }}</option>
                                            <option value="inactive">{{ $t('common.inactive') }}</option>
                                        </select>
                                        <div class="invalid-feedback">{{ form.errors.status }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('common.description') }}</label>
                                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-warning" :disabled="form.processing">
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                            Update
                                        </button>
                                        <Link href="/apps/departments" class="btn btn-secondary">{{ $t('common.cancel') }}</Link>
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
    props: { department: Object },
    setup(props) {
        const form = useForm({
            name: props.department.name,
            code: props.department.code ?? '',
            description: props.department.description ?? '',
            status: props.department.status ?? 'active',
        });
        const submit = () => form.put(`/apps/departments/${props.department.id}`);
        return { form, submit };
    }
}
</script>
