<template>
    <Head><title>Lokasi Gudang - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-location-dot me-1"></i> LOKASI GUDANG</span>
                            <Link v-if="hasAnyPermission(['locations.create'])" href="/apps/locations/create" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus-circle me-2"></i> NEW
                            </Link>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Lokasi</th>
                                        <th>Zona Gudang</th>
                                        <th class="text-center" style="width:15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="locations.data.length === 0">
                                        <td colspan="4" class="text-center">Belum ada data lokasi gudang.</td>
                                    </tr>
                                    <tr v-for="(loc, i) in locations.data" :key="loc.id">
                                        <td>{{ i + 1 }}</td>
                                        <td>{{ loc.name }}</td>
                                        <td>{{ loc.warehouse_zone ?? '-' }}</td>
                                        <td class="text-center">
                                            <Link v-if="hasAnyPermission(['locations.edit'])" :href="`/apps/locations/${loc.id}/edit`" class="btn btn-success btn-sm me-2">
                                                <i class="fa fa-pencil-alt"></i>
                                            </Link>
                                            <button v-if="hasAnyPermission(['locations.delete'])" class="btn btn-danger btn-sm" @click="handleDelete(loc.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="locations.links" align="end" />
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
    props: { locations: Object },
    setup() {
        const isLoading = ref(false);

        const handleDelete = async (id) => {
            if (await confirmDelete('Lokasi gudang ini akan dihapus permanen.')) {
                isLoading.value = true;
                router.delete(`/apps/locations/${id}`, {
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