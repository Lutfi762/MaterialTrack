<template>
    <Head>
        <title>Profil Saya - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                <div class="lg:col-span-2 space-y-4">
                    <!-- Profile Info -->
                    <div class="mtx-card">
                        <div class="mtx-card-header">
                            <span class="mtx-card-title"><i class="fa fa-user text-indigo-600" aria-hidden="true"></i> {{ $t('profile.heading') }}</span>
                        </div>
                        <div class="mtx-card-body">
                            <div v-if="profileSuccess" class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2.5 text-sm mb-4 flex items-center justify-between">
                                {{ profileSuccess }}
                                <button type="button" class="text-green-500 hover:text-green-700 text-lg leading-none" @click="profileSuccess = ''">&times;</button>
                            </div>

                            <form @submit.prevent="updateProfile">
                                <div class="mtx-field">
                                    <label for="name" class="mtx-label">{{ $t('profile.full_name') }}</label>
                                    <input id="name" type="text" class="mtx-input" :class="{ 'mtx-input-error': profileForm.errors.name }" v-model="profileForm.name" required />
                                    <p v-if="profileForm.errors.name" class="mtx-error-text">{{ profileForm.errors.name }}</p>
                                </div>

                                <div class="mtx-field">
                                    <label for="email" class="mtx-label">Email</label>
                                    <input id="email" type="email" class="mtx-input" :class="{ 'mtx-input-error': profileForm.errors.email }" v-model="profileForm.email" required />
                                    <p v-if="profileForm.errors.email" class="mtx-error-text">{{ profileForm.errors.email }}</p>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="profileForm.processing">
                                        <i class="fa fa-save" aria-hidden="true"></i>
                                        {{ profileForm.processing ? $t('common.saving') : $t('profile.save_changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                        <!-- Change Password -->
                    <div class="mtx-card">
                        <div class="mtx-card-header">
                            <span class="mtx-card-title"><i class="fa fa-lock text-amber-500" aria-hidden="true"></i> {{ $t('profile.change_password') }}</span>
                        </div>
                        <div class="mtx-card-body">
                            <div v-if="passwordSuccess" class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2.5 text-sm mb-4 flex items-center justify-between">
                                {{ passwordSuccess }}
                                <button type="button" class="text-green-500 hover:text-green-700 text-lg leading-none" @click="passwordSuccess = ''">&times;</button>
                            </div>

                            <form @submit.prevent="updatePassword">
                                <div class="mtx-field">
                                    <label for="current_password" class="mtx-label">{{ $t('profile.current_password') }}</label>
                                    <input id="current_password" type="password" class="mtx-input" :class="{ 'mtx-input-error': passwordForm.errors.current_password }" v-model="passwordForm.current_password" required />
                                    <p v-if="passwordForm.errors.current_password" class="mtx-error-text">{{ passwordForm.errors.current_password }}</p>
                                </div>

                                <div class="mtx-field">
                                    <label for="password" class="mtx-label">{{ $t('profile.new_password') }}</label>
                                    <input id="password" type="password" class="mtx-input" :class="{ 'mtx-input-error': passwordForm.errors.password }" v-model="passwordForm.password" required />
                                    <p v-if="passwordForm.errors.password" class="mtx-error-text">{{ passwordForm.errors.password }}</p>
                                    <p class="mtx-hint">{{ $t('users.password_placeholder') }}</p>
                                </div>

                                <div class="mtx-field">
                                    <label for="password_confirmation" class="mtx-label">{{ $t('profile.confirm_new_password') }}</label>
                                    <input id="password_confirmation" type="password" class="mtx-input" v-model="passwordForm.password_confirmation" required />
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="passwordForm.processing">
                                        <i class="fa fa-key" aria-hidden="true"></i>
                                        {{ passwordForm.processing ? $t('common.saving') : $t('profile.change_password') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="mtx-card">
                    <div class="mtx-card-body text-center">
                        <img
                            :src="`https://ui-avatars.com/api/?name=${$page.props.auth?.user?.name || 'User'}&background=7c3aed&color=ffffff&size=128`"
                            class="rounded-full mx-auto mb-3"
                            width="80"
                            height="80"
                            alt="Avatar"
                        />
                        <h5 class="text-base font-bold text-gray-900">{{ $page.props.auth?.user?.name }}</h5>
                        <p class="text-sm text-gray-500">{{ $page.props.auth?.user?.email }}</p>
                        <hr class="border-gray-100 my-3" />
                        <div class="text-left">
                            <small class="block text-xs text-gray-400">{{ $t('profile.department') }}</small>
                            <strong class="text-sm">{{ $page.props.auth?.user?.department?.name ?? '-' }}</strong>
                        </div>
                        <div class="text-left mt-2">
                            <small class="block text-xs text-gray-400">{{ $t('profile.role') }}</small>
                            <strong class="text-sm">{{ userRoleLabel }}</strong>
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
