<template>
    <div>
        <Head :title="$t('bulk_stock.title')" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('bulk_stock.heading') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('bulk_stock.heading') }]" />
            </div>
            <Link v-if="hasAnyPermission(['stock_ins.create'])" href="/apps/bulk-stock/create" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> {{ $t('bulk_stock.add') }}
            </Link>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ $t('bulk_stock.col_date') }}</th>
                                <th>{{ $t('bulk_stock.col_type') }}</th>
                                <th>{{ $t('bulk_stock.col_items') }}</th>
                                <th>{{ $t('bulk_stock.col_by') }}</th>
                                <th>{{ $t('common.status') }}</th>
                                <th>{{ $t('common.notes') }}</th>
                                <th>{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="op in operations.data" :key="op.id">
                                <td>{{ formatDate(op.created_at) }}</td>
                                <td>
                                    <span class="badge" :class="op.type === 'in' ? 'bg-success' : 'bg-danger'">
                                        {{ op.type === 'in' ? 'IN' : 'OUT' }}
                                    </span>
                                </td>
                                <td>{{ op.items_count ?? op.items?.length }} {{ $t('bulk_stock.items_label') }}</td>
                                <td>{{ op.user?.name }}</td>
                                <td>
                                    <span class="badge" :class="statusBadge(op.status)">{{ statusLabel(op.status) }}</span>
                                </td>
                                <td>{{ op.notes || '-' }}</td>
                                <td>
                                    <Link :href="`/apps/bulk-stock/${op.id}`" class="btn btn-sm btn-outline-info" :title="$t('common.info')">
                                        <i class="fa fa-eye"></i>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="operations.data.length === 0">
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa fa-layer-group fa-2x mb-2 d-block"></i>
                                    {{ $t('bulk_stock.empty') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                {{ $t('common.showing') }} {{ operations.from || 0 }} - {{ operations.to || 0 }} {{ $t('common.of') }} {{ operations.total }}
            </div>
            <nav v-if="operations.last_page > 1">
                <ul class="pagination pagination-sm mb-0">
                    <li v-for="link in operations.links" :key="link.label" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                        <Link class="page-link" :href="link.url ?? '#'" v-html="link.label" />
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';

export default {
    components: { Head, Link, Breadcrumb },
    props: { operations: Object },
    methods: {
        formatDate(d) {
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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
