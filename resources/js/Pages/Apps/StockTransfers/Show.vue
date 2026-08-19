<template>
    <Head>
        <title>{{ $t('stock_transfer.detail_heading') }} - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-primary">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-eye me-1"></i> {{ $t('stock_transfer.detail_heading') }}</span>
                                <div class="d-flex gap-2">
                                    <a :href="`/apps/stock-transfers/${transfer.id}/pdf`" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-file-pdf me-1"></i> {{ $t('common.print') }}
                                    </a>
                                    <button v-if="transfer.status === 'pending' && hasAnyPermission(['stock_outs.approve'])"
                                        @click="approve" class="btn btn-sm btn-success">
                                        <i class="fa fa-check me-1"></i> {{ $t('stock_transfer.approve') }}
                                    </button>
                                    <button v-if="transfer.status === 'pending' && hasAnyPermission(['stock_outs.approve'])"
                                        @click="reject" class="btn btn-sm btn-danger">
                                        <i class="fa fa-times me-1"></i> {{ $t('stock_transfer.reject') }}
                                    </button>
                                    <Link href="/apps/stock-transfers" class="btn btn-sm btn-secondary">
                                        <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                                    </Link>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="detail-header mb-4">
                                    <div class="ref-badge">{{ transfer.reference_no }}</div>
                                    <div class="qty-display">
                                        <span class="qty-number">{{ transfer.qty }}</span>
                                        <span class="qty-unit">{{ transfer.unit }}</span>
                                    </div>
                                    <div class="item-name">{{ transfer.item?.name ?? '-' }}</div>
                                    <span class="badge mt-2" :class="statusClass(transfer.status)" style="font-size:13px;">{{ transfer.status }}</span>
                                </div>

                                <hr>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_transfer.from_dept') }}</div>
                                        <div class="detail-value">{{ transfer.from_department?.name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_transfer.to_dept') }}</div>
                                        <div class="detail-value">{{ transfer.to_department?.name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">Kode Material</div>
                                        <div class="detail-value">{{ transfer.item?.code ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">Dicatat Oleh</div>
                                        <div class="detail-value">{{ transfer.user?.name ?? '-' }}</div>
                                    </div>
                                    <div v-if="transfer.approver" class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_transfer.approved_by') }}</div>
                                        <div class="detail-value">{{ transfer.approver?.name ?? '-' }}</div>
                                    </div>
                                    <div v-if="transfer.approved_at" class="col-md-6">
                                        <div class="detail-label">{{ $t('stock_transfer.approval_time') }}</div>
                                        <div class="detail-value">{{ transfer.approved_at }}</div>
                                    </div>
                                </div>

                                <div v-if="transfer.notes" class="mt-4">
                                    <div class="detail-label">{{ $t('stock_transfer.notes') }}</div>
                                    <div class="notes-box">{{ transfer.notes }}</div>
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
        transfer: Object,
    },
    methods: {
        statusClass(status) {
            return {
                pending: 'bg-warning text-dark',
                approved: 'bg-success',
                rejected: 'bg-danger',
            }[status] || 'bg-secondary';
        },
        approve() {
            if (confirm(this.$t('stock_transfer.approve'))) {
                router.post(`/apps/stock-transfers/${this.transfer.id}/approve`);
            }
        },
        reject() {
            if (confirm(this.$t('stock_transfer.reject'))) {
                router.post(`/apps/stock-transfers/${this.transfer.id}/reject`);
            }
        },
    },
};
</script>

<style>
.border-top-primary {
    border-top: 3px solid #0d6efd !important;
}
.detail-header {
    text-align: center;
}
.ref-badge {
    display: inline-block;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 6px 16px;
    border-radius: 20px;
    margin-bottom: 12px;
}
.qty-display {
    margin: 8px 0;
}
.qty-number {
    font-size: 36px;
    font-weight: 700;
    color: #1d4ed8;
}
.qty-unit {
    font-size: 14px;
    color: #64748b;
    margin-left: 4px;
}
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
