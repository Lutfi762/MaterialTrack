<template>
    <Head><title>Department - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-building me-1"></i> DATA DEPARTMENT</span>
                            <Link v-if="hasAnyPermission(['departments.create'])" href="/apps/departments/create" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus-circle me-2"></i> NEW
                            </Link>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah PIC</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width:15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="departments.data.length === 0">
                                        <td colspan="6" class="text-center">Tidak ada data.</td>
                                    </tr>
                                    <tr v-for="dept in departments.data" :key="dept.id">
                                        <td><code>{{ dept.code ?? '-' }}</code></td>
                                        <td>{{ dept.name }}</td>
                                        <td>{{ dept.description ?? '-' }}</td>
                                        <td>{{ dept.pics_count }}</td>
                                        <td>
                                            <span v-if="dept.status === 'active'" class="badge bg-success">Aktif</span>
                                            <span v-else class="badge bg-secondary">Non Aktif</span>
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
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { departments: Object },
    setup() {
        const handleDelete = async (id) => {
            if (await confirmDelete('Department ini akan dihapus permanen.')) {
                router.delete(`/apps/departments/${id}`);
            }
        };
        return { handleDelete };
    },
};
</script>