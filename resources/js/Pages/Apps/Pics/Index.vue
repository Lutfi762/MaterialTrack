<template>
    <Head><title>Data PIC - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div>
                            <h4 class="mb-1"><i class="fa fa-id-badge me-2 text-primary"></i>Data PIC</h4>
                            <p class="text-muted mb-0 small">Penanggung jawab pengambilan material per departemen</p>
                        </div>
                        <Link v-if="hasAnyPermission(['pics.create'])" href="/apps/pics/create" class="btn btn-primary btn-modern">
                            <i class="fa fa-plus-circle me-2"></i> Tambah PIC
                        </Link>
                    </div>
                </div>
            </div>

            <div class="row" v-if="pics.data.length > 0">
                <div class="col-md-4 mb-4" v-for="pic in pics.data" :key="pic.id">
                    <div class="pic-card">
                        <div class="pic-card-header">
                            <div class="pic-avatar">{{ initials(pic.name) }}</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ pic.name }}</div>
                                <span class="badge-dept">{{ pic.department?.name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="pic-card-body">
                            <div class="pic-info-row" v-if="pic.nik">
                                <i class="fa fa-id-card text-muted"></i> {{ pic.nik }}
                            </div>
                            <div class="pic-info-row" v-if="pic.phone">
                                <i class="fa fa-phone text-muted"></i> {{ pic.phone }}
                            </div>
                            <div class="pic-info-row" v-if="pic.email">
                                <i class="fa fa-envelope text-muted"></i> {{ pic.email }}
                            </div>
                            <div class="pic-info-row text-muted" v-if="!pic.nik && !pic.phone && !pic.email">
                                <i class="fa fa-circle-info"></i> Belum ada info kontak
                            </div>
                        </div>
                        <div class="pic-card-footer">
                            <Link v-if="hasAnyPermission(['pics.edit'])" :href="`/apps/pics/${pic.id}/edit`" class="btn btn-sm btn-outline-success flex-fill">
                                <i class="fa fa-pencil-alt me-1"></i> Edit
                            </Link>
                            <button v-if="hasAnyPermission(['pics.delete'])" class="btn btn-sm btn-outline-danger flex-fill" @click="handleDelete(pic.id)">
                                <i class="fa fa-trash me-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="empty-state" v-else>
                <i class="fa fa-id-badge"></i>
                <p class="mb-0 fw-semibold">Belum ada data PIC</p>
                <p class="text-muted small">Tambahkan PIC untuk mulai mencatat pengambilan material</p>
            </div>

            <Pagination v-if="pics.data.length > 0" :links="pics.links" align="center" />
        </div>
    </main>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { pics: Object },
    setup() {
        const initials = (name) => {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            return parts.length > 1
                ? (parts[0][0] + parts[1][0]).toUpperCase()
                : parts[0].substring(0, 2).toUpperCase();
        };

        const handleDelete = async (id) => {
            if (await confirmDelete('PIC ini akan dihapus permanen.')) {
                router.delete(`/apps/pics/${id}`);
            }
        };

        return { initials, handleDelete };
    },
};
</script>

<style scoped>
.page-header-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
    border-radius: 16px;
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 12px rgba(13, 110, 253, 0.06);
    border: 1px solid rgba(13, 110, 253, 0.08);
}
.btn-modern {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    border: none;
    transition: transform 0.15s ease;
}
.btn-modern:hover { transform: translateY(-2px); }
.pic-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eef1f6;
    overflow: hidden;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.pic-card:hover {
    box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12);
    transform: translateY(-3px);
}
.pic-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px;
    border-bottom: 1px solid #f0f2f7;
}
.pic-avatar {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
}
.badge-dept {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: #1d4ed8;
    background: #eaf1ff;
    padding: 2px 10px;
    border-radius: 20px;
    margin-top: 2px;
}
.pic-card-body {
    padding: 16px 20px;
    flex-grow: 1;
}
.pic-info-row {
    font-size: 13px;
    color: #495057;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pic-info-row:last-child { margin-bottom: 0; }
.pic-card-footer {
    padding: 14px 20px;
    border-top: 1px solid #f0f2f7;
    display: flex;
    gap: 8px;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    border: 1px dashed #dee2e6;
}
.empty-state i {
    font-size: 42px;
    color: #cbd5e1;
    margin-bottom: 12px;
    display: block;
}
</style>