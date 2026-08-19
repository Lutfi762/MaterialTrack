<template>
    <div>
        <Head :title="$t('bulk_stock.detail')" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('bulk_stock.detail') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('bulk_stock.heading'), url: '/apps/bulk-stock' }, { label: $t('bulk_stock.detail') }]" />
            </div>
            <Link href="/apps/bulk-stock" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
            </Link>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $t('bulk_stock.operation_info') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width:200px">{{ $t('bulk_stock.type') }}</th>
                                <td>
                                    <span class="badge" :class="operation.type === 'in' ? 'bg-success' : 'bg-danger'">
                                        {{ operation.type === 'in' ? 'STOCK IN' : 'STOCK OUT' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ $t('bulk_stock.col_by') }}</th>
                                <td>{{ operation.user?.name }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('bulk_stock.col_date') }}</th>
                                <td>{{ formatDate(operation.created_at) }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('common.status') }}</th>
                                <td>
                                    <span class="badge" :class="statusBadge(operation.status)">{{ statusLabel(operation.status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ $t('common.notes') }}</th>
                                <td>{{ operation.notes || '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $t('bulk_stock.items_detail') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ $t('bulk_stock.material') }}</th>
                                        <th>{{ $t('bulk_stock.qty') }}</th>
                                        <th>{{ $t('bulk_stock.source') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, idx) in operation.items" :key="item.id">
                                        <td>{{ idx + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ item.item?.name }}</div>
                                            <small class="text-muted">{{ item.item?.code }}</small>
                                        </td>
                                        <td class="fw-bold">{{ item.qty }} {{ item.item?.unit }}</td>
                                        <td>{{ item.source || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $t('common.summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ $t('bulk_stock.total_items') }}</span>
                            <span class="fw-bold">{{ operation.items?.length || 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ $t('bulk_stock.total_qty') }}</span>
                            <span class="fw-bold">{{ totalQty }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ $t('bulk_stock.operation_type') }}</span>
                            <span class="fw-bold" :class="operation.type === 'in' ? 'text-success' : 'text-danger'">
                                {{ operation.type === 'in' ? 'STOCK IN' : 'STOCK OUT' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';

export default {
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
            return { completed: 'bg-success', pending: 'bg-warning text-dark', cancelled: 'bg-secondary' }[s] || 'bg-secondary';
        },
        statusLabel(s) {
            return { completed: 'Selesai', pending: 'Menunggu', cancelled: 'Dibatalkan' }[s] || s;
        },
    },
};
</script>
