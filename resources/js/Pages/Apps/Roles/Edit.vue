<template>
    <Head>
        <title>Edit Role - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-shield-halved text-indigo-600" aria-hidden="true"></i> {{ $t('roles.edit') }}</span>
                    <Link href="/apps/roles" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('roles.name') }}</label>
                            <input
                                class="mtx-input"
                                v-model="form.name"
                                :class="{ 'mtx-input-error': form.errors.name }"
                                type="text"
                                :placeholder="$t('roles.name_placeholder')"
                            >
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>

                        <div class="mtx-field border-t border-gray-100 pt-4">
                            <label class="mtx-label">Permissions</label>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-1">
                                <div
                                    class="mtx-checkbox-row"
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                >
                                    <input
                                        class="mtx-checkbox"
                                        type="checkbox"
                                        v-model="form.permissions"
                                        :value="permission.name"
                                        :id="`check-${permission.id}`"
                                    >
                                    <label class="mtx-checkbox-label" :for="`check-${permission.id}`">
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                            <p v-if="form.errors.permissions" class="mtx-error-text">
                                {{ form.errors.permissions }}
                            </p>
                        </div>

                        <div class="pt-2">
                            <button
                                class="mtx-btn mtx-btn-primary"
                                type="submit"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                <span v-else>UPDATE</span>
                            </button>
                        </div>
                    </form>
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

