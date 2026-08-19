<template>
    <Head>
        <title>Roles - MaterialTrack</title>
    </Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header">
                            <span class="font-weight-bold">
                                <i class="fa fa-shield-alt"></i> {{ $t('roles.heading') }}
                            </span>
                        </div>
                        <div class="card-body">

                            <form @submit.prevent="handleSearch">
                                <div class="input-group mb-3">
                                    <Link
                                        v-if="hasAnyPermission(['roles.create'])"
                                        href="/apps/roles/create"
                                        class="btn btn-primary input-group-text"
                                    >
                                        <i class="fa fa-plus-circle me-2"></i> NEW
                                    </Link>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="search"
                                        :placeholder="$t('roles.search_placeholder')"
                                    />
                                    <button class="btn btn-primary input-group-text" type="submit">
                                        <i class="fa fa-search me-2"></i> SEARCH
                                    </button>
                                </div>
                            </form>

                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ $t('roles.col_name') }}</th>
                                        <th style="width:50%">{{ $t('roles.col_permissions') }}</th>
                                        <th style="width:20%" class="text-center">{{ $t('common.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="roles.data.length === 0">
                                        <td colspan="3" class="text-center">{{ $t('common.no_data') }}</td>
                                    </tr>
                                    <tr v-for="(role, index) in roles.data" :key="index">
                                        <td>
                                            {{ role.name }}
                                            <span v-if="isLockedRole(role.name)" class="badge bg-secondary ms-2">
                                                <i class="fa fa-lock me-1"></i> {{ $t('roles.locked') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                v-for="(permission, i) in role.permissions"
                                                :key="i"
                                                class="badge bg-primary shadow border-0 ms-2 mb-2"
                                            >
                                                {{ permission.name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <template v-if="!isLockedRole(role.name)">
                                                <Link
                                                    v-if="hasAnyPermission(['roles.edit'])"
                                                    :href="`/apps/roles/${role.id}/edit`"
                                                    class="btn btn-success btn-sm me-2"
                                                >
                                                    <i class="fa fa-pencil-alt me-1"></i> EDIT
                                                </Link>
                                                <button
                                                    v-if="hasAnyPermission(['roles.delete'])"
                                                    class="btn btn-danger btn-sm"
                                                    @click="handleDelete(role.id)"
                                                >
                                                    <i class="fa fa-trash"></i> DELETE
                                                </button>
                                            </template>
                                            <span v-else class="text-muted small">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <Pagination :links="roles.links" align="end" />

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';

const LOCKED_ROLES = ['department_user'];

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
        Pagination,
    },

    props: {
        roles: Object,
    },

    setup() {
        const search = ref(
            (new URL(document.location)).searchParams.get('q') ?? ''
        );

        const handleSearch = () => {
            router.get('/apps/roles', {
                q: search.value,
            }, {
                preserveState: true,
                replace: true,
            });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete($t('roles.delete_confirm'))) {
                router.delete(`/apps/roles/${id}`);
            }
        };

        const isLockedRole = (roleName) => LOCKED_ROLES.includes(roleName);

        return {
            search,
            handleSearch,
            handleDelete,
            isLockedRole,
        };
    },
};
</script>
