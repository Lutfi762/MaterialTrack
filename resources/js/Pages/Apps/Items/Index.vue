<template>
    <Head><title>Data Items/Material - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-box-seam me-1"></i> DATA ITEMS/MATERIAL</span>
<Link href="/apps/items/create" class="btn btn-primary btn-sm">                                <i class="fa fa-plus-circle me-2"></i> NEW
                            </Link>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 mb-3">
                                <select class="form-select" style="max-width: 220px;" v-model="filterDepartment" @change="onDepartmentChange">
                                    <option value="">Semua Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <select class="form-select" style="max-width: 220px;" v-model="filterCategory" @change="applyFilter">
                                    <option value="">Semua Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>

                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Lokasi</th>
                                        <th>Stok</th>
                                        <th>Min. Stok</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width:15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="items.data.length === 0">
                                        <td colspan="8" class="text-center">Tidak ada data.</td>
                                    </tr>
                                    <tr v-for="item in items.data" :key="item.id">
                                        <td><code>{{ item.code }}</code></td>
                                        <td>{{ item.name }}</td>
                                        <td>{{ item.category?.name ?? '-' }}</td>
                                        <td>{{ item.location?.name ?? '-' }}</td>
                                        <td>{{ item.current_stock }} {{ item.unit }}</td>
                                        <td>{{ item.min_stock }} {{ item.unit }}</td>
                                        <td>
                                            <span v-if="item.current_stock <= 0" class="badge bg-danger">Habis</span>
                                            <span v-else-if="item.current_stock <= item.min_stock" class="badge bg-warning text-dark">Menipis</span>
                                            <span v-else class="badge bg-success">Aman</span>
                                        </td>
                                        <td class="text-center">
                                            <Link v-if="hasAnyPermission(['items.edit'])" :href="`/apps/items/${item.id}/edit`" class="btn btn-success btn-sm me-2">
                                                <i class="fa fa-pencil-alt"></i>
                                            </Link>
                                            <button v-if="hasAnyPermission(['items.delete'])" class="btn btn-danger btn-sm" @click="handleDelete(item.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="items.links" align="end" />
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

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { items: Object, departments: Array, categories: Array, filters: Object },
    setup(props) {
        const filterDepartment = ref(props.filters?.department_id ?? '');
        const filterCategory = ref(props.filters?.category_id ?? '');

        const onDepartmentChange = () => {
            // Reset kategori setiap kali department berubah, karena daftar
            // kategori yang tersedia akan berubah sesuai department baru.
            filterCategory.value = '';
            applyFilter();
        };

        const applyFilter = () => {
            router.get('/apps/items', {
                department_id: filterDepartment.value || undefined,
                category_id: filterCategory.value || undefined,
            }, { preserveState: true });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete('Material ini akan dihapus permanen.')) {
                router.delete(`/apps/items/${id}`);
            }
        };

        return { filterDepartment, filterCategory, onDepartmentChange, applyFilter, handleDelete };
    },
};
</script>