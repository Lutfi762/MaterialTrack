<template>
    <Head><title>Users - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <div class="mtx-page-header">
                <div>
                    <h1 class="mtx-page-title"><i class="fa fa-users text-indigo-600" aria-hidden="true"></i> {{ $t('users.heading') }}</h1>
                    <p class="mtx-hint">{{ $t('users.subtitle') }}</p>
                </div>
                <Link v-if="hasAnyPermission(['users.create'])" href="/apps/users/create" class="mtx-btn mtx-btn-primary">
                    <i class="fa fa-user-plus" aria-hidden="true"></i> {{ $t('users.add') }}
                </Link>
            </div>

            <div class="flex items-center gap-2 mb-4 bg-white border border-gray-200 rounded-lg px-3 py-2">
                <i class="fa fa-search text-gray-400" aria-hidden="true"></i>
                <input v-model="search" @keyup.enter="handleSearch" type="text" class="flex-1 border-0 outline-none text-sm" :placeholder="$t('users.search_placeholder')" :disabled="isLoading">
                <button class="mtx-btn mtx-btn-primary mtx-btn-sm" @click="handleSearch" :disabled="isLoading">
                    <span v-if="isLoading">{{ $t('common.saving') }}</span>
                    <span v-else>{{ $t('common.search') }}</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" v-if="users.data.length > 0">
                <div class="mtx-card mtx-card-body flex items-start gap-3" v-for="u in users.data" :key="u.id">
                    <div class="h-11 w-11 shrink-0 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">{{ initials(u.name) }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900">{{ u.name }}</div>
                        <div class="text-sm text-gray-500">{{ u.email }}</div>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span v-if="u.department" class="mtx-badge mtx-badge-info">{{ u.department.name }}</span>
                            <span v-for="role in u.roles" :key="role.id" class="mtx-badge mtx-badge-neutral">{{ role.name }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 shrink-0">
                        <Link v-if="hasAnyPermission(['users.edit'])" :href="`/apps/users/${u.id}/edit`" class="mtx-icon-btn">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </Link>
                        <button v-if="hasAnyPermission(['users.delete'])" class="mtx-icon-btn mtx-icon-btn-danger" @click="handleDelete(u.id)">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="mtx-card text-center py-12">
                <i class="fa fa-users text-4xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 mb-0 font-semibold text-gray-700">{{ $t('users.empty') }}</p>
            </div>

            <Pagination v-if="users.data.length > 0" :links="users.links" align="center" />
        </div>
    </main>
</template>

<script>
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    components: { Head, Link, Pagination },
    props: { users: Object },
    setup() {
        const search = ref((new URL(document.location)).searchParams.get('q') ?? '');
        const isLoading = ref(false);

        const handleSearch = () => {
            isLoading.value = true;
            router.get('/apps/users', { q: search.value }, {
                preserveState: true,
                replace: true,
                onFinish: () => { isLoading.value = false; }
            });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete($t('users.delete_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/users/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => {
                        isLoading.value = false;
                    }
                });
            }
        };

        const initials = (name) => {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : parts[0].substring(0, 2).toUpperCase();
        };

        return { search, handleSearch, handleDelete, initials, isLoading };
    },
};
</script>

