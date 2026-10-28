<template>
    <Head>
        <title>Tambah User - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-user-plus text-indigo-600" aria-hidden="true"></i> {{ $t('users.add') }}</span>
                    <Link href="/apps/users" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('users.name') }}</label>
                            <input
                                class="mtx-input"
                                v-model="form.name"
                                :class="{ 'mtx-input-error': form.errors.name }"
                                type="text"
                                :placeholder="$t('users.name_placeholder')"
                            >
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">Email</label>
                            <input
                                class="mtx-input"
                                v-model="form.email"
                                :class="{ 'mtx-input-error': form.errors.email }"
                                type="email"
                                placeholder="nama@email.com"
                            >
                            <p v-if="form.errors.email" class="mtx-error-text">{{ form.errors.email }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">Password</label>
                                <input
                                    class="mtx-input"
                                    v-model="form.password"
                                    :class="{ 'mtx-input-error': form.errors.password }"
                                    type="password"
                                    :placeholder="$t('users.password_placeholder')"
                                >
                                <p v-if="form.errors.password" class="mtx-error-text">{{ form.errors.password }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('users.password_confirm') }}</label>
                                <input
                                    class="mtx-input"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    :placeholder="$t('users.password_confirm_placeholder')"
                                >
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">Department</label>
                            <select
                                class="mtx-select"
                                v-model="form.department_id"
                                        :class="{ 'mtx-select-error': form.errors.department_id }"
                            >
                                <option :value="null">{{ $t('users.department_none') }}</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.department_id" class="mtx-error-text">{{ form.errors.department_id }}</p>
                        </div>

                        <div class="mtx-field border-t border-gray-100 pt-4">
                            <label class="mtx-label">Roles</label>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-1">
                                <div
                                    class="mtx-checkbox-row"
                                    v-for="role in roles"
                                    :key="role.id"
                                >
                                    <input
                                        class="mtx-checkbox"
                                        type="checkbox"
                                        v-model="form.roles"
                                        :value="role.name"
                                        :id="`role-${role.id}`"
                                    >
                                    <label class="mtx-checkbox-label" :for="`role-${role.id}`">{{ role.name }}</label>
                                </div>
                            </div>
                            <p v-if="form.errors.roles" class="mtx-error-text">{{ form.errors.roles }}</p>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                                <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                <span v-else>SIMPAN</span>
                            </button>
                            <button class="mtx-btn mtx-btn-secondary" type="button" @click="form.reset()">
                                RESET
                            </button>
                        </div>
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

    components: {
        Head,
        Link,
    },

    props: {
        departments: Array,
        roles: Array,
    },

    setup() {
        const form = useForm({
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            department_id: null,
            roles: [],
        });

        const submit = () => {
            form.post('/apps/users', {
                onSuccess: () => {
                    Swal.fire({
                        title: $t('common.saved_success'),
                        text: 'User berhasil ditambahkan.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                },
            });
        };

        return { form, submit };
    },
};
</script>

