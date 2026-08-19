<template>
    <Head>
        <title>{{ $t('stock_opname.detail_heading') }} - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-warning">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-eye me-1"></i> {{ $t('stock_opname.detail_heading') }}</span>
                                <div class="d-flex gap-2">
                                    <a :href="`/apps/stock-opnames/${opname.id}/pdf`" target="_blank" class="btn btn-sm btn-outline-warning">
                                        <i class="fa fa-file-pdf me-1"></i> {{ $t('common.print') }}
                                    </a>
                                    <Link href="/apps/stock-opnames" class="btn btn-sm btn-secondary">
                                        <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                                    </Link>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="detail-header mb-4">
                                    <div class="ref-badge">{{ opname.reference_no }}</div>
                                    <div class="mb-2">
                                        <span v-if="opname.status === 'pending'" class="badge bg-warning text-dark">
                                            <i class="fa fa-clock me-1"></i>Menunggu Verifikasi
                                        </span>
                                        <span v-else class="badge bg-success">
                                            <i class="fa fa-check me-1"></i>Terverifikasi
                                        </span>
                                    </div>
                                    <div class="difference-display">
                                        <span class="difference-number" :class="differenceClass">
                                            {{ differenceText }}
                                        </span>
                                        <span class="difference-label">{{ $t('stock_opname.col_diff') }}</span>
                                    </div>
                                    <div class="item-name">{{ opname.item?.name ?? '-' }}</div>
                                </div>

                                <hr>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.period') }}</div>
                                        <div class="detail-value">{{ opname.period }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.date') }}</div>
                                        <div class="detail-value">{{ opname.date }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">Kode Material</div>
                                        <div class="detail-value">{{ opname.item?.code ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.col_category') }}</div>
                                        <div class="detail-value">{{ opname.item?.category?.name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.col_system_stock') }}</div>
                                        <div class="detail-value fw-bold">{{ opname.system_stock }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.col_physical_stock') }}</div>
                                        <div class="detail-value fw-bold">{{ opname.physical_stock }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.recorded_by') }}</div>
                                        <div class="detail-value">{{ opname.user?.name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">Verifikator</div>
                                        <div class="detail-value">{{ opname.verified_by?.name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_opname.recorded_at') }}</div>
                                        <div class="detail-value">{{ opname.created_at }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">Diverifikasi pada</div>
                                        <div class="detail-value">{{ opname.verified_at ? new Date(opname.verified_at).toLocaleString('id-ID') : '-' }}</div>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <form v-if="opname.status === 'pending' && hasAnyPermission(['stock_opnames.verify'])" @submit.prevent="verify">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-check-double me-1"></i> Verifikasi Opname
                                        </button>
                                    </form>
                                    <Link v-if="opname.status === 'pending' && hasAnyPermission(['stock_opnames.create'])" :href="`/apps/stock-opnames/${opname.id}/edit`" class="btn btn-warning">
                                        <i class="fa fa-edit me-1"></i> {{ $t('stock_opname.revision') }}
                                    </Link>
                                </div>

                                <div v-if="opname.notes" class="mt-4">
                                    <div class="detail-label">Catatan Tambahan</div>
                                    <div class="notes-box">{{ opname.notes }}</div>
                                </div>

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
        opname: Object,
    },
    computed: {
        difference() {
            return (this.opname.physical_stock ?? 0) - (this.opname.system_stock ?? 0);
        },
        differenceClass() {
            if (this.difference > 0) return 'text-info';
            if (this.difference < 0) return 'text-danger';
            return 'text-success';
        },
        differenceText() {
            const d = this.difference;
            if (d > 0) return '+' + d;
            return String(d);
        },
    },
    methods: {
        verify() {
            if (confirm(`Verifikasi opname ${this.opname.reference_no}? Stok sistem akan disesuaikan dari ${this.opname.system_stock} ke ${this.opname.physical_stock}.`)) {
                router.patch(`/apps/stock-opnames/${this.opname.id}/verify`);
            }
        },
    },
};
</script>

<style>
.border-top-warning {
    border-top: 3px solid #ffc107 !important;
}
.detail-header {
    text-align: center;
}
.ref-badge {
    display: inline-block;
    background: #fef3c7;
    color: #d97706;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 6px 16px;
    border-radius: 20px;
    margin-bottom: 12px;
}
.difference-display {
    margin: 8px 0;
}
.difference-number {
    font-size: 36px;
    font-weight: 700;
}
.difference-label {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}
.text-success { color: #16a34a !important; }
.text-info { color: #0ea5e9 !important; }
.text-danger { color: #dc2626 !important; }
.item-name {
    font-size: 15px;
    color: #475569;
}
.detail-label {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.detail-value {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}
.notes-box {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    color: #92400e;
    line-height: 1.6;
}
</style>
