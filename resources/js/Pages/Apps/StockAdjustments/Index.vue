<template>
    <Head><title>Penyesuaian Stok - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-balance-scale me-1"></i> Penyesuaian Stok</span>
                            <div class="d-flex gap-2">
                                <a v-if="hasAnyPermission(['activity_logs.index'])" href="/apps/activity-logs" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa fa-history me-1"></i> Audit Trail
                                </a>
                                <Link v-if="hasAnyPermission(['stock_adjustments.create'])" href="/apps/stock-adjustments/create" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus-circle me-2"></i> Ajukan Penyesuaian
                                </Link>
                            </div>
                        </div>
                        <div class="card-body">
                            <div v-if="pendingCount > 0 && hasAnyPermission(['stock_adjustments.approve'])" class="alert alert-warning py-2">
                                <i class="fa fa-clock me-1"></i> {{ pendingCount }} penyesuaian menunggu persetujuan.
                            </div>

                            <form @submit.prevent="applyFilter" class="row g-2 mb-3">
                                <div class="col-md-2">
                                    <select v-model="filters.status" class="form-select form-select-sm">
                                        <option value="">Semua Status</option>
                                        <option value="pending">Menunggu</option>
                                        <option value="approved">Disetujui</option>
                                        <option value="rejected">Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select v-model="filters.item_id" class="form-select form-select-sm">
                                        <option value="">Semua Item</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">{{ item.code }} - {{ item.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" v-model="filters.date_from" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" v-model="filters.date_to" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter me-1"></i> Filter</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="resetFilter"><i class="fa fa-undo"></i></button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ref</th>
                                            <th>Tanggal</th>
                                            <th>Material</th>
                                            <th class="text-end">Qty</th>
                                            <th>Alasan</th>
                                            <th>Diajukan</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="adjustments.data.length === 0">
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada penyesuaian stok.</td>
                                        </tr>
                                        <tr v-for="adj in adjustments.data" :key="adj.id">
                                            <td class="text-nowrap">{{ adj.reference_no }}</td>
                                            <td class="text-nowrap">{{ formatDate(adj.created_at) }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ adj.item?.name }}</div>
                                                <div class="text-muted small">{{ adj.item?.code }}</div>
                                            </td>
                                            <td class="text-end">
                                                <span :class="adj.qty > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'">
                                                    {{ adj.qty > 0 ? '+' + adj.qty : adj.qty }}
                                                </span>
                                                <span class="text-muted small"> {{ adj.item?.unit }}</span>
                                            </td>
                                            <td style="max-width: 240px;">
                                                <span class="d-inline-block text-truncate" style="max-width: 240px;" :title="adj.reason">{{ adj.reason }}</span>
                                            </td>
                                            <td>{{ adj.user?.name ?? '-' }}</td>
                                            <td>
                                                <span v-if="adj.status === 'pending'" class="badge bg-warning text-dark">Menunggu</span>
                                                <span v-else-if="adj.status === 'approved'" class="badge bg-success">Disetujui</span>
                                                <span v-else class="badge bg-danger">Ditolak</span>
                                                <div v-if="adj.approver" class="small text-muted mt-1">oleh {{ adj.approver.name }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div v-if="adj.status === 'pending'" class="btn-group btn-group-sm">
                                                    <form v-if="hasAnyPermission(['stock_adjustments.approve'])" @submit.prevent="approve(adj)">
                                                        <button class="btn btn-outline-success" title="Setujui"><i class="fa fa-check"></i></button>
                                                    </form>
                                                    <form v-if="hasAnyPermission(['stock_adjustments.approve'])" @submit.prevent="reject(adj)">
                                                        <button class="btn btn-outline-danger" title="Tolak"><i class="fa fa-times"></i></button>
                                                    </form>
                                                    <form v-if="hasAnyPermission(['stock_adjustments.create'])" @submit.prevent="destroy(adj)">
                                                        <button class="btn btn-outline-secondary" title="Hapus"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </div>
                                                <span v-else class="text-muted small">-</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <nav v-if="adjustments.last_page > 1">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li v-for="link in adjustments.links" :key="link.label"
                                            class="page-item" :class="{ active: link.active, disabled: !link.url }">
                                            <Link class="page-link" :href="link.url ?? '#'" v-html="link.label" />
                                        </li>
                                    </ul>
                                </nav>
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
import { Head, Link, router } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        adjustments: Object,
        items: Array,
        filters: Object,
        pendingCount: Number,
    },
    data() {
        return {
            filters: { ...this.filters },
        };
    },
    methods: {
        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
            });
        },
        applyFilter() {
            router.get('/apps/stock-adjustments', this.filters, { preserveState: true });
        },
        resetFilter() {
            this.filters = { status: '', item_id: '', date_from: '', date_to: '' };
            this.applyFilter();
        },
        approve(adj) {
            if (confirm(`Setujui penyesuaian ${adj.reference_no} (${adj.qty > 0 ? '+' : ''}${adj.qty})? Stok akan diperbarui.`)) {
                router.patch(`/apps/stock-adjustments/${adj.id}/approve`);
            }
        },
        reject(adj) {
            if (confirm(`Tolak penyesuaian ${adj.reference_no}?`)) {
                router.patch(`/apps/stock-adjustments/${adj.id}/reject`);
            }
        },
        destroy(adj) {
            if (confirm(`Hapus penyesuaian ${adj.reference_no}?`)) {
                router.delete(`/apps/stock-adjustments/${adj.id}`);
            }
        },
    },
};
</script>
