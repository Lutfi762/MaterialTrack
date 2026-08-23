<template>
    <Head>
        <title>{{ $t('reports.title') }} - MaterialTrack</title>
    </Head>
    <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
        <div class="mx-auto max-w-7xl">
            <h2 class="mb-6 flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
                <i class="fa fa-chart-bar text-primary"></i> {{ $t('reports.heading') }}
            </h2>

            <!-- Date Filter -->
            <form @submit.prevent="applyFilter" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('reports.date_from') }}</label>
                    <input type="date" class="rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="filters.date_from" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('reports.date_to') }}</label>
                    <input type="date" class="rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="filters.date_to" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]">
                        <i class="fa fa-filter"></i> {{ $t('common.filter') }}
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg" @click="resetFilter">
                        <i class="fa fa-undo"></i> {{ $t('common.reset') }}
                    </button>
                </div>
            </form>

            <!-- KPI Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-outline/20 bg-surface p-5 shadow-sm">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/15 text-primary"><i class="fa fa-arrow-down"></i></div>
                    <div class="text-2xl font-bold text-on-surface">{{ summary.stock_in_count }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $t('reports.stock_in_count') }}</div>
                    <div class="mt-2 text-xs text-on-surface-variant">{{ summary.stock_in_qty }} {{ $t('reports.unit') }}</div>
                </div>
                <div class="rounded-xl border border-outline/20 bg-surface p-5 shadow-sm">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-danger/15 text-danger"><i class="fa fa-arrow-up"></i></div>
                    <div class="text-2xl font-bold text-on-surface">{{ summary.stock_out_count }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $t('reports.stock_out_count') }}</div>
                    <div class="mt-2 text-xs text-on-surface-variant">{{ summary.stock_out_qty }} {{ $t('reports.unit') }}</div>
                </div>
                <div class="rounded-xl border border-outline/20 bg-surface p-5 shadow-sm">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-warning/15 text-warning"><i class="fa fa-clock"></i></div>
                    <div class="text-2xl font-bold text-on-surface">{{ summary.pending_approval }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $t('reports.pending_approval') }}</div>
                </div>
                <div class="rounded-xl border border-outline/20 bg-surface p-5 shadow-sm">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/15 text-primary"><i class="fa fa-clipboard-check"></i></div>
                    <div class="text-2xl font-bold text-on-surface">{{ summary.opname_count }}</div>
                    <div class="text-sm text-on-surface-variant">{{ $t('reports.opname_done') }}</div>
                    <div class="mt-2 text-xs">
                        <span class="font-semibold text-success">+{{ summary.opname_surplus }}</span> /
                        <span class="font-semibold text-danger">-{{ summary.opname_defisit }}</span>
                    </div>
                </div>
            </div>

            <!-- Item Stats -->
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-3 rounded-xl border border-outline/20 border-l-4 border-l-primary bg-surface p-4 shadow-sm">
                    <i class="fa fa-boxes text-xl text-primary"></i>
                    <div>
                        <div class="text-xl font-bold text-on-surface">{{ summary.total_items }}</div>
                        <div class="text-xs text-on-surface-variant">{{ $t('reports.total_material') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-outline/20 border-l-4 border-l-warning bg-surface p-4 shadow-sm">
                    <i class="fa fa-exclamation-triangle text-xl text-warning"></i>
                    <div>
                        <div class="text-xl font-bold text-on-surface">{{ summary.low_stock_items }}</div>
                        <div class="text-xs text-on-surface-variant">{{ $t('reports.low_stock') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-outline/20 border-l-4 border-l-danger bg-surface p-4 shadow-sm">
                    <i class="fa fa-times-circle text-xl text-danger"></i>
                    <div>
                        <div class="text-xl font-bold text-on-surface">{{ summary.out_of_stock }}</div>
                        <div class="text-xs text-on-surface-variant">{{ $t('reports.out_of_stock') }}</div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-outline/20 border-l-4 border-l-outline bg-surface p-4 shadow-sm">
                <i class="fa fa-history text-xl text-on-surface-variant"></i>
                <div>
                    <div class="text-xl font-bold text-on-surface">{{ summary.movement_count }}</div>
                    <div class="text-xs text-on-surface-variant">{{ $t('reports.total_movements') }}</div>
                </div>
            </div>

            <!-- Quick Export Links -->
            <div class="rounded-xl border border-outline/20 bg-surface p-5 shadow-sm">
                <h6 class="mb-4 flex items-center gap-2 text-sm font-bold text-on-surface"><i class="fa fa-download"></i> {{ $t('reports.export_download') }}</h6>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <a href="/apps/stock-ins/export/excel" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-excel text-2xl text-success"></i>
                        <span class="text-sm font-semibold text-on-surface">Export Excel</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.material_in') }}</small>
                    </a>
                    <a href="/apps/stock-ins/download-all/pdf" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-pdf text-2xl text-danger"></i>
                        <span class="text-sm font-semibold text-on-surface">Download PDF</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.recap_material_in') }}</small>
                    </a>
                    <a href="/apps/stock-outs/export/excel" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-excel text-2xl text-success"></i>
                        <span class="text-sm font-semibold text-on-surface">Export Excel</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.material_out') }}</small>
                    </a>
                    <a href="/apps/stock-outs/download-all/pdf" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-pdf text-2xl text-danger"></i>
                        <span class="text-sm font-semibold text-on-surface">Download PDF</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.recap_material_out') }}</small>
                    </a>
                    <a href="/apps/stock-movements/export/excel" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-excel text-2xl text-success"></i>
                        <span class="text-sm font-semibold text-on-surface">Export Excel</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.audit_trail') }}</small>
                    </a>
                    <a href="/apps/stock-movements/download-all/pdf" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-pdf text-2xl text-danger"></i>
                        <span class="text-sm font-semibold text-on-surface">Download PDF</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.recap_audit_trail') }}</small>
                    </a>
                    <a href="/apps/stock-opnames/export/excel" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-excel text-2xl text-success"></i>
                        <span class="text-sm font-semibold text-on-surface">Export Excel</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.opname_result') }}</small>
                    </a>
                    <a href="/apps/stock-opnames/download-all/pdf" class="flex flex-col items-center gap-1 rounded-lg border border-outline/20 p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fa fa-file-pdf text-2xl text-danger"></i>
                        <span class="text-sm font-semibold text-on-surface">Download PDF</span>
                        <small class="text-xs text-on-surface-variant">{{ $t('reports.recap_opname') }}</small>
                    </a>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

export default {
    layout: LayoutApp,
    components: { Head },
    props: {
        summary: Object,
        filters: Object,
    },
    setup(props) {
        const filters = reactive({
            date_from: props.filters.date_from,
            date_to: props.filters.date_to,
        });

        const applyFilter = () => {
            router.get('/apps/reports', {
                date_from: filters.date_from,
                date_to: filters.date_to,
            }, { preserveState: true });
        };

        const resetFilter = () => {
            filters.date_from = new Date().toISOString().slice(0, 8) + '01';
            filters.date_to = new Date().toISOString().slice(0, 10);
            applyFilter();
        };

        return { filters, applyFilter, resetFilter };
    },
};
</script>
