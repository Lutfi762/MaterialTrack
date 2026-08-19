<template>
    <Head><title>{{ $t('categories.edit') }} - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow border-top-purple">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-folder me-1"></i> {{ $t('categories.edit') }}</span>
                            <Link href="/apps/categories" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('categories.name') }}</label>
                                    <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text">
                                    <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('categories.code_prefix') }}</label>
                                    <input
                                        class="form-control"
                                        v-model="form.code_prefix"
                                        @input="form.code_prefix = form.code_prefix.toUpperCase()"
                                        :class="{ 'is-invalid': form.errors.code_prefix }"
                                        type="text"
                                        maxlength="10"
                                    >
                                    <small class="text-muted">{{ $t('categories.code_prefix_desc') }}</small>
                                    <div v-if="form.errors.code_prefix" class="invalid-feedback d-block">{{ form.errors.code_prefix }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('categories.description') }}</label>
                                    <textarea class="form-control" v-model="form.description" rows="3"></textarea>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('categories.departments_access') }}</label>
                                    <br>
                                    <div class="form-check form-check-inline" v-for="dept in departments" :key="dept.id">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            v-model="form.department_ids"
                                            :value="dept.id"
                                            :id="`dept-${dept.id}`"
                                        >
                                        <label class="form-check-label" :for="`dept-${dept.id}`">{{ dept.name }}</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        {{ $t('categories.departments_access_desc') }}
                                    </small>
                                </div>

                                <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                    <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                    <span v-else>{{ $t('common.update') }}</span>
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
import LayoutApp from '../../../Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { category: Object, departments: Array },
    setup(props) {
        const { t } = useI18n();

        const form = useForm({
            name: props.category.name,
            code_prefix: props.category.code_prefix ?? '',
            description: props.category.description ?? '',
            department_ids: props.category.departments?.map(d => d.id) ?? [],
        });

        const submit = () => {
            form.put(`/apps/categories/${props.category.id}`, {
                onSuccess: () => Swal.fire({ title: t('common.success'), text: t('categories.updated_success'), icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, submit };
    },
};
</script>

<style>
.border-top-purple { border-top: 3px solid #6f42c1 !important; }
</style>