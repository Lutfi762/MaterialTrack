<template>
    <Head>
        <title>Edit User - MaterialTrack</title>
    </Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow border-top-purple">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-user-edit me-1"></i> EDIT USER</span>
                            <Link href="/apps/users" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </Link>
                        </div>
                        <div class="card-body">

                            <form @submit.prevent="submit">

                                <div class="mb-3">
                                    <label class="fw-bold">Nama</label>
                                    <input
                                        class="form-control"
                                        v-model="form.name"
                                        :class="{ 'is-invalid': form.errors.name }"
                                        type="text"
                                    >
                                    <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">Email</label>
                                    <input
                                        class="form-control"
                                        v-model="form.email"
                                        :class="{ 'is-invalid': form.errors.email }"
                                        type="email"
                                    >
                                    <div v-if="form.errors.email" class="invalid-feedback d-block">{{ form.errors.email }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Password Baru</label>
                                        <input
                                            class="form-control"
                                            v-model="form.password"
                                            :class="{ 'is-invalid': form.errors.password }"
                                            type="password"
                                            placeholder="Kosongkan jika tidak diubah"
                                        >
                                        <div v-if="form.errors.password" class="invalid-feedback d-block">{{ form.errors.password }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Konfirmasi Password Baru</label>
                                        <input
                                            class="form-control"
                                            v-model="form.password_confirmation"
                                            type="password"
                                            placeholder="Kosongkan jika tidak diubah"
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">Department</label>
                                    <select
                                        class="form-select"
                                        v-model="form.department_id"
                                        :class="{ 'is-invalid': form.errors.department_id }"
                                    >
                                        <option :value="null">- Tidak ada / Admin -</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.department_id" class="invalid-feedback d-block">{{ form.errors.department_id }}</div>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <label class="fw-bold">Roles</label>
                                    <br>
                                    <div
                                        class="form-check form-check-inline"
                                        v-for="role in roles"
                                        :key="role.id"
                                    >
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            v-model="form.roles"
                                            :value="role.name"
                                            :id="`role-${role.id}`"
                                        >
                                        <label class="form-check-label" :for="`role-${role.id}`">{{ role.name }}</label>
                                    </div>
                                    <div v-if="form.errors.roles" class="invalid-feedback d-block">{{ form.errors.roles }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                            <span v-if="form.processing">Menyimpan...</span>
                                            <span v-else>UPDATE</span>
                                        </button>
                                    </div>
                                </div>
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

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
    },

    props: {
        user: Object,
        departments: Array,
        roles: Array,
    },

    setup(props) {
        const form = useForm({
            name: props.user.name,
            email: props.user.email,
            password: '',
            password_confirmation: '',
            department_id: props.user.department_id,
            roles: props.user.roles.map(r => r.name),
        });

        const submit = () => {
            form.put(`/apps/users/${props.user.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'User berhasil diperbarui.',
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

<style>
.border-top-purple {
    border-top: 3px solid #6f42c1 !important;
}
</style>