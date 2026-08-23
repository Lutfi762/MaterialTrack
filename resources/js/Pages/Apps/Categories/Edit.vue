<template>
    <Head><title>{{ $t('categories.edit') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-folder text-indigo-600" aria-hidden="true"></i> {{ $t('categories.edit') }}</span>
                    <Link href="/apps/categories" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('categories.name') }}</label>
                            <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }" v-model="form.name" type="text">
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('categories.code_prefix') }}</label>
                            <input
                                class="mtx-input"
                                :class="{ 'mtx-input-error': form.errors.code_prefix }"
                                v-model="form.code_prefix"
                                @input="form.code_prefix = form.code_prefix.toUpperCase()"
                                type="text"
                                maxlength="10"
                            >
                            <p class="mtx-hint">{{ $t('categories.code_prefix_desc') }}</p>
                            <p v-if="form.errors.code_prefix" class="mtx-error-text">{{ form.errors.code_prefix }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('categories.description') }}</label>
                            <textarea class="mtx-textarea" v-model="form.description" rows="3"></textarea>
                        </div>

                        <div class="mtx-field border-t border-gray-100 pt-4">
                            <label class="mtx-label">{{ $t('categories.departments_access') }}</label>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-1">
                                <div class="mtx-checkbox-row" v-for="dept in departments" :key="dept.id">
                                    <input
                                        class="mtx-checkbox"
                                        type="checkbox"
                                        v-model="form.department_ids"
                                        :value="dept.id"
                                        :id="`dept-${dept.id}`"
                                    >
                                    <label class="mtx-checkbox-label" :for="`dept-${dept.id}`">{{ dept.name }}</label>
                                </div>
                            </div>
                            <p class="mtx-hint">{{ $t('categories.departments_access_desc') }}</p>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>{{ $t('common.update') }}</span>
                        </button>
                    </form>
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