<template>
    <Head>
        <title>{{ $t('stock_opname.detail_heading') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-eye text-indigo-600" aria-hidden="true"></i> {{ $t('stock_opname.detail_heading') }}</span>
                    <div class="flex gap-2">
                        <a :href="`/apps/stock-opnames/${opname.id}/pdf`" target="_blank" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i> {{ $t('common.print') }}
                        </a>
                        <Link href="/apps/stock-opnames" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                        </Link>
                    </div>
                </div>
                <div class="mtx-card-body">

                    <div class="detail-header mb-4">
                        <div class="ref-badge">{{ opname.reference_no }}</div>
                        <div class="mb-2">
                            <span v-if="opname.status === 'pending'" class="mtx-badge mtx-badge-warning">
                                <i class="fa fa-clock" aria-hidden="true"></i>Menunggu Verifikasi
                            </span>
                            <span v-else class="mtx-badge mtx-badge-success">
                                <i class="fa fa-check" aria-hidden="true"></i>Terverifikasi
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

                    <hr class="border-gray-200 mb-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.period') }}</div>
                            <div class="detail-value">{{ opname.period }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.date') }}</div>
                            <div class="detail-value">{{ opname.date }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Kode Material</div>
                            <div class="detail-value">{{ opname.item?.code ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.col_category') }}</div>
                            <div class="detail-value">{{ opname.item?.category?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.col_system_stock') }}</div>
                            <div class="detail-value font-bold">{{ opname.system_stock }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.col_physical_stock') }}</div>
                            <div class="detail-value font-bold">{{ opname.physical_stock }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.recorded_by') }}</div>
                            <div class="detail-value">{{ opname.user?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Verifikator</div>
                            <div class="detail-value">{{ opname.verified_by?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_opname.recorded_at') }}</div>
                            <div class="detail-value">{{ opname.created_at }}</div>
                        </div>
                        <div>
                            <div class="detail-label">Diverifikasi pada</div>
                            <div class="detail-value">{{ opname.verified_at ? new Date(opname.verified_at).toLocaleString('id-ID') : '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <form v-if="opname.status === 'pending' && hasAnyPermission(['stock_opnames.verify'])" @submit.prevent="verify">
                            <button type="submit" class="mtx-btn mtx-btn-primary">
                                <i class="fa fa-check-double" aria-hidden="true"></i> Verifikasi Opname
                            </button>
                        </form>
                        <Link v-if="opname.status === 'pending' && hasAnyPermission(['stock_opnames.create'])" :href="`/apps/stock-opnames/${opname.id}/edit`" class="mtx-btn mtx-btn-secondary">
                            <i class="fa fa-edit" aria-hidden="true"></i> {{ $t('stock_opname.revision') }}
                        </Link>
                    </div>

                    <div v-if="opname.notes" class="mt-4">
                        <div class="detail-label">Catatan Tambahan</div>
                        <div class="notes-box">{{ opname.notes }}</div>
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
