<template>
    <Head><title>{{ $t('custom_reports.title') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <h4 class="fw-bold mb-4"><i class="fa fa-file-alt me-2 text-primary"></i>{{ $t('custom_reports.heading') }}</h4>

                <div class="card border-0 shadow-sm rounded-xl mb-4">
                    <div class="card-body">
                        <form @submit.prevent="generateReport">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">{{ $t('custom_reports.report_type') }} <span class="text-danger">*</span></label>
                                    <select class="form-select" v-model="form.type" required>
                                        <option value="stock_in">{{ $t('custom_reports.type_stock_in') }}</option>
                                        <option value="stock_out">{{ $t('custom_reports.type_stock_out') }}</option>
                                        <option value="movements">{{ $t('custom_reports.type_movements') }}</option>
                                        <option value="items">{{ $t('custom_reports.type_items') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">{{ $t('custom_reports.date_from') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="form.date_from" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">{{ $t('custom_reports.date_to') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="form.date_to" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2" :disabled="generating">
                                        <i class="fa fa-cog me-1" :class="{ 'fa-spin': generating }"></i> {{ $t('custom_reports.generate') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">{{ $t('custom_reports.department') }}</label>
                                    <select class="form-select" v-model="form.department_id">
                                        <option :value="null">{{ $t('custom_reports.all_departments') }}</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">{{ $t('custom_reports.category') }}</label>
                                    <select class="form-select" v-model="form.category_id">
                                        <option :value="null">{{ $t('custom_reports.all_categories') }}</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">{{ $t('custom_reports.item') }}</label>
                                    <input type="text" class="form-control" v-model="form.item_id" :placeholder="$t('custom_reports.item_placeholder')">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-if="report_data" class="card border-0 shadow-sm rounded-xl">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fa fa-table me-1"></i> {{ $t('custom_reports.results') }}</h6>
                        <div>
                            <span class="badge bg-primary me-2">{{ report_data.summary.total_records || report_data.summary.total_items || 0 }} {{ $t('custom_reports.records') }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3" v-if="report_data.summary.total_qty !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value">{{ report_data.summary.total_qty }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.total_quantity') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.total_items !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value">{{ report_data.summary.total_items }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.total_items') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.approved !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value text-success">{{ report_data.summary.approved }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.approved') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.pending !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value text-warning">{{ report_data.summary.pending }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.pending') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.total_in !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value text-success">+{{ report_data.summary.total_in }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.total_in') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.total_out !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value text-danger">-{{ report_data.summary.total_out }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.total_out') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.total_stock !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value">{{ report_data.summary.total_stock }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.total_stock') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="report_data.summary.low_stock !== undefined">
                                <div class="report-summary-card">
                                    <span class="report-summary-value text-danger">{{ report_data.summary.low_stock }}</span>
                                    <span class="report-summary-label">{{ $t('custom_reports.low_stock') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th v-for="header in tableHeaders" :key="header">{{ header }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, idx) in report_data.records" :key="idx">
                                        <td v-for="(value, key) in row" :key="key">
                                            <span v-if="key === 'status'" class="badge" :class="{
                                                'bg-success': value === 'aman' || value === 'approved',
                                                'bg-warning text-dark': value === 'menipis' || value === 'pending',
                                                'bg-danger': value === 'habis' || value === 'rejected',
                                            }">{{ value }}</span>
                                            <span v-else-if="key === 'type'" class="badge" :class="{
                                                'bg-success': value === 'in',
                                                'bg-danger': value === 'out',
                                                'bg-info': value === 'opname' || value === 'adjustment',
                                            }">{{ value }}</span>
                                            <span v-else>{{ value }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="report_data.records.length === 0">
                                        <td :colspan="tableHeaders.length" class="text-center text-muted py-4">{{ $t('custom_reports.no_data') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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

<style scoped>
.report-summary-card {
    text-align: center;
    padding: 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.report-summary-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}
.report-summary-label {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-top: 4px;
}
</style>
