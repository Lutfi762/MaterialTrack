<template>
    <Head><title>Users - MaterialTrack</title></Head>
    <div class="users-page">
        <div class="page-header-card mb-4">
            <div>
                <h4 class="mb-1"><i class="fa fa-users me-2 text-primary"></i>{{ $t('users.heading') }}</h4>
                <p class="text-muted mb-0 small">{{ $t('users.subtitle') }}</p>
            </div>
            <Link v-if="hasAnyPermission(['users.create'])" href="/apps/users/create" class="btn-modern btn-modern-primary">
                <i class="fa fa-user-plus me-2"></i>{{ $t('users.add') }}
            </Link>
        </div>

        <div class="search-bar mb-4">
            <i class="fa fa-search text-muted"></i>
            <input v-model="search" @keyup.enter="handleSearch" type="text" :placeholder="$t('users.search_placeholder')" :disabled="isLoading">
            <button class="btn-modern btn-modern-primary" @click="handleSearch" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                <span v-else>{{ $t('common.search') }}</span>
            </button>
        </div>

        <div class="row g-3" v-if="users.data.length > 0">
            <div class="col-md-6 col-lg-4" v-for="u in users.data" :key="u.id">
                <div class="user-card">
                    <div class="user-avatar">{{ initials(u.name) }}</div>
                    <div class="user-info">
                        <div class="fw-bold">{{ u.name }}</div>
                        <div class="text-muted small">{{ u.email }}</div>
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            <span v-if="u.department" class="badge-dept">{{ u.department.name }}</span>
                            <span v-for="role in u.roles" :key="role.id" class="badge-role">{{ role.name }}</span>
                        </div>
                    </div>
                    <div class="user-actions">
                        <Link v-if="hasAnyPermission(['users.edit'])" :href="`/apps/users/${u.id}/edit`" class="act-btn act-btn-success">
                            <i class="fa fa-pencil-alt"></i>
                        </Link>
                        <button v-if="hasAnyPermission(['users.delete'])" class="act-btn act-btn-danger" @click="handleDelete(u.id)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <i class="fa fa-users"></i>
            <p class="mb-0 fw-semibold">{{ $t('users.empty') }}</p>
        </div>

        <Pagination v-if="users.data.length > 0" :links="users.links" align="center" />
    </div>
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

<style scoped>
.page-header-card {
    background: white; border-radius: 16px; padding: 22px 26px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.btn-modern {
    display: inline-flex; align-items: center; border-radius: 10px;
    padding: 10px 18px; font-weight: 600; font-size: 13px;
    text-decoration: none; border: none; transition: transform 0.15s ease;
}
.btn-modern:hover { transform: translateY(-2px); text-decoration: none; color: white; }
.btn-modern-primary { background: #3b82f6; color: white; box-shadow: 0 4px 12px rgba(59,130,246,0.25); }

.search-bar {
    background: white; border-radius: 14px; padding: 10px 16px;
    display: flex; align-items: center; gap: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.search-bar input { flex: 1; border: none; outline: none; font-size: 13px; }

.user-card {
    background: white; border-radius: 14px; padding: 18px;
    display: flex; align-items: flex-start; gap: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06); height: 100%;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.user-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.08); transform: translateY(-2px); }
.user-avatar {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;
}
.user-info { flex: 1; min-width: 0; }
.badge-dept { font-size: 10px; font-weight: 600; color: #1d4ed8; background: #eaf1ff; padding: 3px 9px; border-radius: 20px; }
.badge-role { font-size: 10px; font-weight: 600; color: #7c3aed; background: #f3e8ff; padding: 3px 9px; border-radius: 20px; }
.user-actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
.act-btn {
    width: 30px; height: 30px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    border: none; text-decoration: none; font-size: 12px;
}
.act-btn-success { background: #d1fae5; color: #059669; }
.act-btn-danger { background: #fee2e2; color: #dc2626; }
.empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #dee2e6; }
.empty-state i { font-size: 42px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>
