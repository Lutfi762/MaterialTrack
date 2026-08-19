<template>
    <Head>
        <title>Profil Saya - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Profile Info -->
                        <div class="card border-0 rounded-3 shadow mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fa fa-user me-1"></i> {{ $t('profile.heading') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div v-if="profileSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ profileSuccess }}
                                    <button type="button" class="btn-close" @click="profileSuccess = ''"></button>
                                </div>

                                <form @submit.prevent="updateProfile">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-bold">{{ $t('profile.full_name') }}</label>
                                        <input
                                            id="name"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': profileForm.errors.name }"
                                            v-model="profileForm.name"
                                            required
                                        />
                                        <div v-if="profileForm.errors.name" class="invalid-feedback">{{ profileForm.errors.name }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-bold">Email</label>
                                        <input
                                            id="email"
                                            type="email"
                                            class="form-control"
                                            :class="{ 'is-invalid': profileForm.errors.email }"
                                            v-model="profileForm.email"
                                            required
                                        />
                                        <div v-if="profileForm.errors.email" class="invalid-feedback">{{ profileForm.errors.email }}</div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary" :disabled="profileForm.processing">
                                            <i class="fa fa-save me-1"></i>
                                            <span v-if="profileForm.processing">{{ $t('common.saving') }}</span>
                                            <span v-else>{{ $t('profile.save_changes') }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="m-0 font-weight-bold text-warning">
                                    <i class="fa fa-lock me-1"></i> {{ $t('profile.change_password') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div v-if="passwordSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ passwordSuccess }}
                                    <button type="button" class="btn-close" @click="passwordSuccess = ''"></button>
                                </div>

                                <form @submit.prevent="updatePassword">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label fw-bold">{{ $t('profile.current_password') }}</label>
                                        <input
                                            id="current_password"
                                            type="password"
                                            class="form-control"
                                            :class="{ 'is-invalid': passwordForm.errors.current_password }"
                                            v-model="passwordForm.current_password"
                                            required
                                        />
                                        <div v-if="passwordForm.errors.current_password" class="invalid-feedback">{{ passwordForm.errors.current_password }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-bold">{{ $t('profile.new_password') }}</label>
                                        <input
                                            id="password"
                                            type="password"
                                            class="form-control"
                                            :class="{ 'is-invalid': passwordForm.errors.password }"
                                            v-model="passwordForm.password"
                                            required
                                        />
                                        <div v-if="passwordForm.errors.password" class="invalid-feedback">{{ passwordForm.errors.password }}</div>
                                        <small class="text-muted">{{ $t('users.password_placeholder') }}</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label fw-bold">{{ $t('profile.confirm_new_password') }}</label>
                                        <input
                                            id="password_confirmation"
                                            type="password"
                                            class="form-control"
                                            v-model="passwordForm.password_confirmation"
                                            required
                                        />
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning" :disabled="passwordForm.processing">
                                            <i class="fa fa-key me-1"></i>
                                            <span v-if="passwordForm.processing">{{ $t('common.saving') }}</span>
                                            <span v-else>{{ $t('profile.change_password') }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="col-md-4">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-body text-center">
                                <img
                                    :src="`https://ui-avatars.com/api/?name=${$page.props.auth?.user?.name || 'User'}&background=7c3aed&color=ffffff&size=128`"
                                    class="rounded-circle mb-3"
                                    width="80"
                                    height="80"
                                    alt="Avatar"
                                />
                                <h5 class="font-weight-bold">{{ $page.props.auth?.user?.name }}</h5>
                                <p class="text-muted small">{{ $page.props.auth?.user?.email }}</p>
                                <hr />
                                <div class="text-start">
                                    <small class="text-muted d-block">{{ $t('profile.department') }}</small>
                                    <strong>{{ $page.props.auth?.user?.department?.name ?? '-' }}</strong>
                                </div>
                                <div class="text-start mt-2">
                                    <small class="text-muted d-block">{{ $t('profile.role') }}</small>
                                    <strong>{{ userRoleLabel }}</strong>
                                </div>
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
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

export default {
    layout: LayoutApp,
    components: { Head },
    setup() {
        const page = usePage();
        const user = page.props.auth?.user;

        const profileForm = useForm({
            name: user?.name || '',
            email: user?.email || '',
        });

        const passwordForm = useForm({
            current_password: '',
            password: '',
            password_confirmation: '',
        });

        const profileSuccess = ref('');
        const passwordSuccess = ref('');

        const userRoleLabel = computed(() => {
            const roles = user?.roles ?? [];
            if (roles.includes('admin')) return 'Administrator';
            if (roles.includes('department_user')) return 'Department User';
            return 'User';
        });

        const updateProfile = () => {
            profileSuccess.value = '';
            profileForm.put('/user/profile-information', {
                preserveScroll: true,
                onSuccess: () => {
                    profileSuccess.value = $t('profile.update_success');
                },
            });
        };

        const updatePassword = () => {
            passwordSuccess.value = '';
            passwordForm.put('/user/password', {
                preserveScroll: true,
                onSuccess: () => {
                    passwordSuccess.value = $t('profile.password_change_success');
                    passwordForm.reset();
                },
            });
        };

        return {
            profileForm,
            passwordForm,
            profileSuccess,
            passwordSuccess,
            userRoleLabel,
            updateProfile,
            updatePassword,
        };
    },
};
</script>
