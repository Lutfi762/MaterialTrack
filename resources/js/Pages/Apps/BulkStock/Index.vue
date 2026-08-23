<template>
    <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6"><div class="mx-auto max-w-7xl">
        <Head :title="$t('bulk_stock.title')" />
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-on-surface">{{ $t('bulk_stock.heading') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('bulk_stock.heading') }]" />
            </div>
            <Link v-if="hasAnyPermission(['stock_ins.create'])" href="/apps/bulk-stock/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]">
                <i class="fa fa-plus me-1"></i> {{ $t('bulk_stock.add') }}
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-outline/20 text-sm"><thead class="bg-bg text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th v-for="label in [$t('bulk_stock.col_date'), $t('bulk_stock.col_type'), $t('bulk_stock.col_items'), $t('bulk_stock.col_by'), $t('common.status'), $t('common.notes')]" :key="label" class="whitespace-nowrap px-4 py-3 text-left">{{ label }}</th><th class="px-4 py-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline/20 text-on-surface"><tr v-for="op in operations.data" :key="op.id" class="transition-colors hover:bg-primary/5">
                                <td class="whitespace-nowrap px-4 py-3">{{ formatDate(op.created_at) }}</td><td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="op.type === 'in' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger'">
                                        {{ op.type === 'in' ? 'IN' : 'OUT' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ op.items_count ?? op.items?.length }} {{ $t('bulk_stock.items_label') }}</td><td class="px-4 py-3">{{ op.user?.name }}</td><td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusBadge(op.status)">{{ statusLabel(op.status) }}</span>
                                </td>
                                <td class="max-w-xs px-4 py-3">{{ op.notes || '-' }}</td><td class="px-4 py-3 text-center"><Link :href="`/apps/bulk-stock/${op.id}`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-primary hover:bg-primary/10" :title="$t('common.info')">
                                        <i class="fa fa-eye"></i>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="operations.data.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-on-surface-variant"><i class="fa fa-layer-group mb-2 block text-2xl"></i>
                                    {{ $t('bulk_stock.empty') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div></div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm text-on-surface-variant"><div>
                {{ $t('common.showing') }} {{ operations.from || 0 }} - {{ operations.to || 0 }} {{ $t('common.of') }} {{ operations.total }}
            </div>
            <Pagination v-if="operations.last_page > 1" :links="operations.links" />
        </div>
    </div></main>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Pagination from '@/Components/Pagination.vue';

export default {
    components: { Head, Link, Breadcrumb, Pagination },
    props: { operations: Object },
    methods: {
        formatDate(d) {
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
        statusBadge(s) {
            return { completed: 'bg-success/15 text-success', pending: 'bg-warning/15 text-warning', cancelled: 'bg-bg text-on-surface-variant' }[s] || 'bg-bg text-on-surface-variant';
        },
        statusLabel(s) {
            return { completed: 'Selesai', pending: 'Menunggu', cancelled: 'Dibatalkan' }[s] || s;
        },
    },
};
</script>
