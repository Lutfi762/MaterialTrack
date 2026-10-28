<template>
    <Head>
        <title>{{ $t('stock_out.detail') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-eye text-indigo-600" aria-hidden="true"></i> {{ $t('stock_out.detail_heading') }}</span>
                    <div class="flex gap-2">
                        <a :href="`/apps/stock-outs/${stockOut.id}/pdf`" target="_blank" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i> {{ $t('stock_out.download_pdf') }}
                        </a>
                        <Link v-if="hasAnyPermission(['stock_outs.edit'])" :href="`/apps/stock-outs/${stockOut.id}/edit`" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-pencil-alt" aria-hidden="true"></i> {{ $t('common.edit') }}
                        </Link>
                        <Link href="/apps/stock-outs" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                        </Link>
                    </div>
                </div>
                <div class="mtx-card-body">

                    <div class="detail-header mb-4">
                        <div class="ref-badge">{{ stockOut.reference_no }}</div>
                        <div class="qty-display">
                            <span class="qty-number">{{ stockOut.qty }}</span>
                            <span class="qty-unit">{{ stockOut.item?.unit ?? 'unit' }}</span>
                        </div>
                        <div class="item-name">{{ stockOut.item?.name ?? '-' }}</div>
                    </div>

                    <hr class="border-gray-200 mb-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="detail-label">{{ $t('stock_out.col_date') }}</div>
                            <div class="detail-value">{{ stockOut.date }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_out.col_material_code') }}</div>
                            <div class="detail-value">{{ stockOut.item?.code ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_out.col_department') }}</div>
                            <div class="detail-value">{{ stockOut.department?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_out.col_pic') }}</div>
                            <div class="detail-value">{{ stockOut.pic?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_out.col_purpose') }}</div>
                            <div class="detail-value">{{ stockOut.purpose ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="detail-label">{{ $t('stock_out.col_recorded_by') }}</div>
                            <div class="detail-value">{{ stockOut.user?.name ?? '-' }}</div>
                        </div>
                    </div>

                    <div v-if="stockOut.notes" class="mt-4">
                        <div class="detail-label">{{ $t('stock_out.col_additional_notes') }}</div>
                        <div class="notes-box">{{ stockOut.notes }}</div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        stockOut: Object,
    },
};
</script>

<style>
.detail-header {
    text-align: center;
}
.ref-badge {
    display: inline-block;
    background: #fee2e2;
    color: #dc2626;
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
    color: #dc2626;
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
