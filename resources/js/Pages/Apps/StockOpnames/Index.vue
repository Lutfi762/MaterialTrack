<template>
    <Head><title>Stock Opname - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-clipboard-check me-1"></i> STOCK OPNAME</span>
                            <div class="d-flex gap-2">
                                <a v-if="hasAnyPermission(['stock_opnames.export', 'stock_opnames.index'])" :href="`/apps/stock-opnames/export/excel?period=${currentPeriod}`" class="btn btn-success btn-sm">
                                    <i class="fa fa-file-excel me-1"></i> Export Excel
                                </a>
                                <a v-if="hasAnyPermission(['stock_opnames.export', 'stock_opnames.index'])" :href="`/apps/stock-opnames/download-all/pdf?period=${currentPeriod}`" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-file-pdf me-1"></i> Download Semua PDF
                                </a>
                                <Link v-if="hasAnyPermission(['stock_opnames.create'])" :href="`/apps/stock-opnames/create?period=${currentPeriod}`" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus-circle me-2"></i> INPUT OPNAME
                                </Link>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <select v-model="selectedPeriod" @change="changePeriod" class="form-select form-select-sm">
                                        <option :value="currentPeriod" v-if="!availablePeriods.includes(currentPeriod)">
                                            {{ currentPeriod }} (belum ada data)
                                        </option>
                                        <option v-for="p in availablePeriods" :key="p" :value="p">{{ p }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ref</th>
                                            <th>Tanggal</th>
                                            <th>Material</th>
                                            <th>Kategori</th>
                                            <th class="text-end">Stok Sistem</th>
                                            <th class="text-end">Stok Fisik</th>
                                            <th class="text-center">Selisih</th>
                                            <th>Catatan</th>
                                            <th>Oleh</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="opnames.data.length === 0">
                                            <td colspan="10" class="text-center text-muted py-4">Belum ada data opname untuk periode ini.</td>
                                        </tr>
                                        <tr v-for="op in opnames.data" :key="op.id">
                                            <td class="text-nowrap">{{ op.reference_no }}</td>
                                            <td class="text-nowrap">{{ formatDate(op.date) }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ op.item?.name }}</div>
                                                <div class="text-muted small">{{ op.item?.code }}</div>
                                            </td>
                                            <td>{{ op.item?.category?.name ?? '-' }}</td>
                                            <td class="text-end">{{ op.system_stock }}</td>
                                            <td class="text-end">{{ op.physical_stock }}</td>
                                            <td class="text-center">
                                                <span v-if="op.difference === 0" class="badge bg-success">Sesuai</span>
                                                <span v-else-if="op.difference > 0" class="badge bg-info">+{{ op.difference }} (surplus)</span>
                                                <span v-else class="badge bg-danger">{{ op.difference }} (defisit)</span>
                                            </td>
                                            <td>{{ op.notes ?? '-' }}</td>
                                            <td>{{ op.user?.name ?? '-' }}</td>
                                            <td class="text-center">
                                                <a :href="`/apps/stock-opnames/${op.id}/pdf`" class="btn btn-outline-danger btn-sm" title="Download PDF">
                                                    <i class="fa fa-file-pdf"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <nav v-if="opnames.last_page > 1">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li v-for="link in opnames.links" :key="link.label"
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
        opnames: Object,
        availablePeriods: Array,
        currentPeriod: String,
    },
    data() {
        return {
            selectedPeriod: this.currentPeriod,
        };
    },
    methods: {
        changePeriod() {
            router.get('/apps/stock-opnames', { period: this.selectedPeriod }, { preserveState: true });
        },
        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
            });
        },
    },
};
</script>