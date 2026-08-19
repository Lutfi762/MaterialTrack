<template>
    <Head><title>Department - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-building me-1"></i> {{ $t('departments.heading') }}</span>
                            <Link v-if="hasAnyPermission(['departments.create'])" href="/apps/departments/create" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus-circle me-2"></i> NEW
                            </Link>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ $t('common.code') }}</th>
                                        <th>{{ $t('common.name') }}</th>
                                        <th>{{ $t('common.description') }}</th>
                                        <th>{{ $t('departments.col_pics') }}</th>
                                        <th>{{ $t('common.status') }}</th>
                                        <th class="text-center" style="width:15%">{{ $t('common.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="departments.data.length === 0">
                                        <td colspan="6" class="text-center">{{ $t('common.no_data') }}</td>
                                    </tr>
                                    <tr v-for="dept in departments.data" :key="dept.id">
                                        <td><code>{{ dept.code ?? '-' }}</code></td>
                                        <td>{{ dept.name }}</td>
                                        <td>{{ dept.description ?? '-' }}</td>
                                        <td>{{ dept.pics_count }}</td>
                                        <td>
                                            <span v-if="dept.status === 'active'" class="badge bg-success">{{ $t('common.active') }}</span>
                                            <span v-else class="badge bg-secondary">{{ $t('common.inactive') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <Link v-if="hasAnyPermission(['departments.edit'])" :href="`/apps/departments/${dept.id}/edit`" class="btn btn-success btn-sm me-2">
                                                <i class="fa fa-pencil-alt"></i>
                                            </Link>
                                            <button v-if="hasAnyPermission(['departments.delete'])" class="btn btn-danger btn-sm" @click="handleDelete(dept.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="departments.links" align="end" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { departments: Object },
    setup() {
        const isLoading = ref(false);

        const handleDelete = async (id) => {
            if (await confirmDelete($t('departments.delete_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/departments/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => {
                        isLoading.value = false;
                    }
                });
            }
        };
        return { handleDelete, isLoading };
    },
};
</script>
