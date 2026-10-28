<template>
    <Head><title>Edit PIC - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-id-badge text-indigo-600" aria-hidden="true"></i> {{ $t('pics.edit_heading') }}</span>
                    <Link href="/apps/pics" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <p class="mtx-hint mb-4 -mt-2">{{ pic.name }}</p>
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('pics.full_name') }}</label>
                            <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }" v-model="form.name" type="text">
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('pics.nik') }}</label>
                                <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.nik }" v-model="form.nik" type="text">
                                <p v-if="form.errors.nik" class="mtx-error-text">{{ form.errors.nik }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('pics.phone') }}</label>
                                <input class="mtx-input" v-model="form.phone" type="text">
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('pics.email') }}</label>
                            <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.email }" v-model="form.email" type="email">
                            <p v-if="form.errors.email" class="mtx-error-text">{{ form.errors.email }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">Departemen</label>
                            <select class="mtx-select" v-model="form.department_id" :disabled="departments.length <= 1">
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>Update PIC</span>
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

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { pic: Object, departments: Array },
    setup(props) {
        const form = useForm({
            name: props.pic.name,
            nik: props.pic.nik ?? '',
            phone: props.pic.phone ?? '',
            email: props.pic.email ?? '',
            department_id: props.pic.department_id,
        });

        const submit = () => {
            form.put(`/apps/pics/${props.pic.id}`, {
                onSuccess: () => Swal.fire({
                    title: $t('common.saved_success'),
                    text: 'Data PIC diperbarui.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                }),
            });
        };

        return { form, submit };
    },
};
</script>

