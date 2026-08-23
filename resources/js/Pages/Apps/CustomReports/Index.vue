<template>
    <Head><title>{{ $t('custom_reports.title') }} - MaterialTrack</title></Head>
    <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
        <div class="mx-auto max-w-7xl">
            <h2 class="mb-6 flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
                <i class="fa fa-file-alt text-primary"></i> {{ $t('custom_reports.heading') }}
            </h2>

            <form @submit.prevent="generateReport" class="mb-6 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('custom_reports.report_type') }} <span class="text-danger">*</span></label>
                        <select class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="form.type" required>
                            <option value="stock_in">{{ $t('custom_reports.type_stock_in') }}</option>
                            <option value="stock_out">{{ $t('custom_reports.type_stock_out') }}</option>
                            <option value="movements">{{ $t('custom_reports.type_movements') }}</option>
                            <option value="items">{{ $t('custom_reports.type_items') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('custom_reports.date_from') }} <span class="text-danger">*</span></label>
                        <input type="date" class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="form.date_from" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('custom_reports.date_to') }} <span class="text-danger">*</span></label>
                        <input type="date" class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="form.date_to" required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]" :disabled="generating">
                            <i class="fa fa-cog" :class="{ 'fa-spin': generating }"></i> {{ $t('custom_reports.generate') }}
                        </button>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('custom_reports.department') }}</label>
                        <select class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="form.department_id">
                            <option :value="null">{{ $t('custom_reports.all_departments') }}</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('custom_reports.category') }}</label>
                        <select class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="form.category_id">
                            <option :value="null">{{ $t('custom_reports.all_categories') }}</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant">{{ $t('custom_reports.item') }}</label>
                        <input type="text" class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="form.item_id" :placeholder="$t('custom_reports.item_placeholder')">
                    </div>
                </div>
            </form>

            <div v-if="report_data" class="rounded-xl border border-outline/20 bg-surface shadow-sm">
                <div class="flex items-center justify-between border-b border-outline/20 px-5 py-4">
                    <h6 class="flex items-center gap-2 text-sm font-bold text-on-surface"><i class="fa fa-table"></i> {{ $t('custom_reports.results') }}</h6>
                    <span class="rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-primary">{{ report_data.summary.total_records || report_data.summary.total_items || 0 }} {{ $t('custom_reports.records') }}</span>
                </div>
                <div class="p-5">
                    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.total_qty !== undefined">
                            <span class="block text-2xl font-bold text-on-surface">{{ report_data.summary.total_qty }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.total_quantity') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.total_items !== undefined">
                            <span class="block text-2xl font-bold text-on-surface">{{ report_data.summary.total_items }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.total_items') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.approved !== undefined">
                            <span class="block text-2xl font-bold text-success">{{ report_data.summary.approved }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.approved') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.pending !== undefined">
                            <span class="block text-2xl font-bold text-warning">{{ report_data.summary.pending }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.pending') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.total_in !== undefined">
                            <span class="block text-2xl font-bold text-success">+{{ report_data.summary.total_in }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.total_in') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.total_out !== undefined">
                            <span class="block text-2xl font-bold text-danger">-{{ report_data.summary.total_out }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.total_out') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.total_stock !== undefined">
                            <span class="block text-2xl font-bold text-on-surface">{{ report_data.summary.total_stock }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.total_stock') }}</span>
                        </div>
                        <div class="rounded-lg border border-outline/20 bg-bg p-4 text-center" v-if="report_data.summary.low_stock !== undefined">
                            <span class="block text-2xl font-bold text-danger">{{ report_data.summary.low_stock }}</span>
                            <span class="mt-1 block text-xs text-on-surface-variant">{{ $t('custom_reports.low_stock') }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-outline/20 text-sm">
                            <thead class="bg-bg text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th v-for="header in tableHeaders" :key="header" class="whitespace-nowrap px-4 py-3 text-left">{{ header }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline/20 text-on-surface">
                                <tr v-for="(row, idx) in report_data.records" :key="idx" class="transition-colors hover:bg-primary/5">
                                    <td v-for="(value, key) in row" :key="key" class="whitespace-nowrap px-4 py-3">
                                        <span v-if="key === 'status'" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="{
                                            'bg-success/15 text-success': value === 'aman' || value === 'approved',
                                            'bg-warning/15 text-warning': value === 'menipis' || value === 'pending',
                                            'bg-danger/15 text-danger': value === 'habis' || value === 'rejected',
                                        }">{{ value }}</span>
                                        <span v-else-if="key === 'type'" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="{
                                            'bg-success/15 text-success': value === 'in',
                                            'bg-danger/15 text-danger': value === 'out',
                                            'bg-primary/15 text-primary': value === 'opname' || value === 'adjustment',
                                        }">{{ value }}</span>
                                        <span v-else>{{ value }}</span>
                                    </td>
                                </tr>
                                <tr v-if="report_data.records.length === 0">
                                    <td :colspan="tableHeaders.length" class="px-4 py-12 text-center text-on-surface-variant">{{ $t('custom_reports.no_data') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

export default {
    layout: LayoutApp,
    components: { Head },
    props: {
        departments: Array,
        categories: Array,
        report_data: { type: Object, default: null },
        report_type: { type: String, default: null },
        filters: { type: Object, default: null },
    },
    setup(props) {
        const generating = ref(false);

        const form = reactive({
            type: props.filters?.type || 'stock_in',
            date_from: props.filters?.date_from || new Date().toISOString().slice(0, 8) + '01',
            date_to: props.filters?.date_to || new Date().toISOString().slice(0, 10),
            department_id: props.filters?.department_id || null,
            category_id: props.filters?.category_id || null,
            item_id: props.filters?.item_id || null,
        });

        const tableHeadersMap = {
            stock_in: ['ID', 'Tanggal', 'Material', 'Kode', 'Satuan', 'Qty', 'Sumber', 'Catatan'],
            stock_out: ['ID', 'Tanggal', 'Material', 'Kode', 'Satuan', 'Qty', 'Dept', 'Keperluan', 'Status'],
            movements: ['ID', 'Tanggal', 'Material', 'Kode', 'Tipe', 'Qty', 'Stok Sebelum', 'Stok Sesudah'],
            items: ['ID', 'Kode', 'Nama', 'Kategori', 'Satuan', 'Stok', 'Min Stok', 'Status'],
        };

        const tableHeaders = ref(tableHeadersMap[props.report_type] || []);

        const generateReport = () => {
            generating.value = true;
            router.post('/apps/custom-reports/generate', form, {
                onFinish: () => { generating.value = false; },
                preserveState: true,
            });
        };

        return { form, generating, generateReport, tableHeaders };
    },
};
</script>
