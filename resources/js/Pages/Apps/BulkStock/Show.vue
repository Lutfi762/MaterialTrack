<template>
    <Head :title="$t('bulk_stock.detail')" />
    <main class="p-6">
        <div class="max-w-5xl mx-auto">
            <div class="mtx-page-header">
                <div>
                    <h1 class="mtx-page-title">{{ $t('bulk_stock.detail') }}</h1>
                    <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('bulk_stock.heading'), url: '/apps/bulk-stock' }, { label: $t('bulk_stock.detail') }]" />
                </div>
                <Link href="/apps/bulk-stock" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 space-y-4">
                    <div class="mtx-card">
                        <div class="mtx-card-header">
                            <span class="mtx-card-title">{{ $t('bulk_stock.operation_info') }}</span>
                        </div>
                        <div class="mtx-card-body">
                            <table class="w-full text-sm divide-y divide-gray-200">
                                <tbody>
                                    <tr>
                                        <th class="text-left align-top py-2 pr-4 w-48 font-semibold text-gray-500">{{ $t('bulk_stock.type') }}</th>
                                        <td class="py-2">
                                            <span class="mtx-badge" :class="operation.type === 'in' ? 'mtx-badge-success' : 'mtx-badge-danger'">
                                                {{ operation.type === 'in' ? 'STOCK IN' : 'STOCK OUT' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('bulk_stock.col_by') }}</th>
                                        <td class="py-2">{{ operation.user?.name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('bulk_stock.col_date') }}</th>
                                        <td class="py-2">{{ formatDate(operation.created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('common.status') }}</th>
                                        <td class="py-2">
                                            <span class="mtx-badge" :class="statusBadge(operation.status)">{{ statusLabel(operation.status) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('common.notes') }}</th>
                                        <td class="py-2">{{ operation.notes || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mtx-card">
                        <div class="mtx-card-header">
                            <span class="mtx-card-title">{{ $t('bulk_stock.items_detail') }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <tr class="border-b border-gray-200">
                                        <th class="px-5 py-3 text-left font-semibold">#</th>
                                        <th class="px-5 py-3 text-left font-semibold">{{ $t('bulk_stock.material') }}</th>
                                        <th class="px-5 py-3 text-left font-semibold">{{ $t('bulk_stock.qty') }}</th>
                                        <th class="px-5 py-3 text-left font-semibold">{{ $t('bulk_stock.source') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(item, idx) in operation.items" :key="item.id">
                                        <td class="px-5 py-3 text-gray-500">{{ idx + 1 }}</td>
                                        <td class="px-5 py-3">
                                            <div class="font-medium text-gray-900">{{ item.item?.name }}</div>
                                            <div class="text-xs text-gray-400">{{ item.item?.code }}</div>
                                        </td>
                                        <td class="px-5 py-3 font-semibold">{{ item.qty }} {{ item.item?.unit }}</td>
                                        <td class="px-5 py-3">{{ item.source || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mtx-card self-start">
                    <div class="mtx-card-header">
                        <span class="mtx-card-title">{{ $t('common.summary') }}</span>
                    </div>
                    <div class="mtx-card-body space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">{{ $t('bulk_stock.total_items') }}</span>
                            <span class="font-semibold text-sm">{{ operation.items?.length || 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">{{ $t('bulk_stock.total_qty') }}</span>
                            <span class="font-semibold text-sm">{{ totalQty }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">{{ $t('bulk_stock.operation_type') }}</span>
                            <span class="font-semibold text-sm" :class="operation.type === 'in' ? 'text-green-600' : 'text-red-600'">
                                {{ operation.type === 'in' ? 'STOCK IN' : 'STOCK OUT' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';

export default {
    layout: LayoutApp,
    components: { Head, Link, Breadcrumb },
    props: { operation: Object },
    computed: {
        totalQty() {
            return this.operation.items?.reduce((sum, i) => sum + i.qty, 0) || 0;
        },
    },
    methods: {
        formatDate(d) {
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
        statusBadge(s) {
            return { completed: 'mtx-badge-success', pending: 'mtx-badge-warning', cancelled: 'mtx-badge-neutral' }[s] || 'mtx-badge-neutral';
        },
        statusLabel(s) {
            return { completed: 'Selesai', pending: 'Menunggu', cancelled: 'Dibatalkan' }[s] || s;
        },
    },
};
</script>
