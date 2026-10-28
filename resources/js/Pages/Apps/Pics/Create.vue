<template>
    <Head><title>Tambah PIC - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-id-badge text-indigo-600" aria-hidden="true"></i> {{ $t('pics.add_heading') }}</span>
                    <Link href="/apps/pics" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <p class="mtx-hint mb-4 -mt-2">{{ $t('pics.add_desc') }}</p>
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
                            <select class="mtx-select" v-model="form.department_id" :disabled="lockedDepartmentId !== null">
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                            <p v-if="lockedDepartmentId !== null" class="mtx-hint">
                                <i class="fa fa-lock" aria-hidden="true"></i> {{ $t('pics.department_auto') }}
                            </p>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>{{ $t('pics.save') }}</span>
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
    props: { departments: Array, lockedDepartmentId: [Number, null] },
    setup(props) {
        const form = useForm({
            name: '',
            nik: '',
            phone: '',
            email: '',
            department_id: props.lockedDepartmentId ?? (props.departments[0]?.id ?? null),
        });

        const submit = () => {
            form.post('/apps/pics', {
                onSuccess: () => Swal.fire({
                    title: $t('common.saved_success'),
                    text: 'PIC baru berhasil ditambahkan.',
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

