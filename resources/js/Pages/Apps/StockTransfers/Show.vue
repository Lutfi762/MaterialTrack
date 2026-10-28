<template>
    <Head>
        <title>{{ $t('stock_transfer.detail_heading') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-2xl mx-auto">
            <div class="mtx-card border-t-2 border-t-indigo-500">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-eye text-indigo-600" aria-hidden="true"></i> {{ $t('stock_transfer.detail_heading') }}</span>
                    <div class="flex gap-2">
                        <a :href="`/apps/stock-transfers/${transfer.id}/pdf`" target="_blank" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-file-pdf text-red-500" aria-hidden="true"></i> {{ $t('common.print') }}
                        </a>
                        <button v-if="transfer.status === 'pending' && hasAnyPermission(['stock_outs.approve'])"
                            @click="approve" class="mtx-btn mtx-btn-primary mtx-btn-sm">
                            <i class="fa fa-check" aria-hidden="true"></i> {{ $t('stock_transfer.approve') }}
                        </button>
                        <button v-if="transfer.status === 'pending' && hasAnyPermission(['stock_outs.approve'])"
                            @click="reject" class="mtx-btn mtx-btn-danger mtx-btn-sm">
                            <i class="fa fa-times" aria-hidden="true"></i> {{ $t('stock_transfer.reject') }}
                        </button>
                        <Link href="/apps/stock-transfers" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                        </Link>
                    </div>
                </div>
                <div class="mtx-card-body">
                    <div class="text-center mb-4">
                        <div class="inline-flex bg-blue-100 text-blue-700 text-[11px] font-bold tracking-wide px-4 py-1.5 rounded-full">{{ transfer.reference_no }}</div>
                        <div class="my-2">
                            <span class="text-4xl font-bold text-blue-700">{{ transfer.qty }}</span>
                            <span class="text-sm text-gray-500 ml-1">{{ transfer.unit }}</span>
                        </div>
                        <div class="text-[15px] text-gray-600">{{ transfer.item?.name ?? '-' }}</div>
                        <span class="mtx-badge mt-2" :class="statusClass(transfer.status)" style="font-size:13px;">{{ transfer.status }}</span>
                    </div>

                    <hr class="border-gray-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ $t('stock_transfer.from_dept') }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ transfer.from_department?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ $t('stock_transfer.to_dept') }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ transfer.to_department?.name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Kode Material</div>
                            <div class="text-sm font-semibold text-gray-800">{{ transfer.item?.code ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Dicatat Oleh</div>
                            <div class="text-sm font-semibold text-gray-800">{{ transfer.user?.name ?? '-' }}</div>
                        </div>
                        <div v-if="transfer.approver">
                            <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ $t('stock_transfer.approved_by') }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ transfer.approver?.name ?? '-' }}</div>
                        </div>
                        <div v-if="transfer.approved_at">
                            <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ $t('stock_transfer.approval_time') }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ transfer.approved_at }}</div>
                        </div>
                    </div>

                    <div v-if="transfer.notes" class="mt-4">
                        <div class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ $t('stock_transfer.notes') }}</div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-[13px] text-amber-800 leading-relaxed">{{ transfer.notes }}</div>
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
                pending: 'mtx-badge-warning',
                approved: 'mtx-badge-success',
                rejected: 'mtx-badge-danger',
            }[status] || 'mtx-badge-neutral';
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
