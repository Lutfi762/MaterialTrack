<template>
    <Head>
        <title>Edit Role - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-purple">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-shield-halved me-1"></i> {{ $t('roles.edit') }}</span>
                                <Link href="/apps/roles" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                                </Link>
                            </div>
                            <div class="card-body">

                                <form @submit.prevent="submit">

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('roles.name') }}</label>
                                        <input
                                            class="form-control"
                                            v-model="form.name"
                                            :class="{ 'is-invalid': form.errors.name }"
                                            type="text"
                                            :placeholder="$t('roles.name_placeholder')"
                                        >
                                        <div v-if="form.errors.name" class="invalid-feedback d-block">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <label class="fw-bold">Permissions</label>
                                        <br>
                                        <div
                                            class="form-check form-check-inline"
                                            v-for="permission in permissions"
                                            :key="permission.id"
                                        >
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                v-model="form.permissions"
                                                :value="permission.name"
                                                :id="`check-${permission.id}`"
                                            >
                                            <label class="form-check-label" :for="`check-${permission.id}`">
                                                {{ permission.name }}
                                            </label>
                                        </div>
                                        <div v-if="form.errors.permissions" class="invalid-feedback d-block">
                                            {{ form.errors.permissions }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary shadow-sm rounded-sm"
                                                type="submit"
                                                :disabled="form.processing"
                                            >
                                                <span v-if="form.processing">{{ $t('common.saving') }}</span>
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
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
    },

    props: {
        permissions: Array,
        role: Object,
    },

    setup(props) {
        const form = useForm({
            name: props.role.name,
            permissions: props.role.permissions.map(p => p.name),
        });

        const submit = () => {
            form.put(`/apps/roles/${props.role.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: $t('common.saved_success'),
                        text: 'Role berhasil diperbarui.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                },
            });
        };

        return {
            form,
            submit,
        };
    },
}
</script>

<style>
.border-top-purple {
    border-top: 3px solid #6f42c1 !important;
}
</style>
