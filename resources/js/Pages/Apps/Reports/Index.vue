<template>
    <Head>
        <title>Laporan - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fa fa-chart-bar me-1"></i> {{ $t('reports.heading') }}
                                </h6>
                            </div>
                            <div class="card-body">

                                <!-- Date Filter -->
                                <form @submit.prevent="applyFilter" class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">{{ $t('reports.date_from') }}</label>
                                        <input type="date" class="form-control" v-model="filters.date_from" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">{{ $t('reports.date_to') }}</label>
                                        <input type="date" class="form-control" v-model="filters.date_to" />
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fa fa-filter me-1"></i> {{ $t('common.filter') }}
                                        </button>
                                        <button type="button" class="btn btn-secondary" @click="resetFilter">
                                            <i class="fa fa-undo me-1"></i> {{ $t('common.reset') }}
                                        </button>
                                    </div>
                                </form>

                                <!-- KPI Cards -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="report-card bg-primary text-white">
                                            <div class="report-icon"><i class="fa fa-arrow-down"></i></div>
                                            <div class="report-data">
                                                <h3>{{ summary.stock_in_count }}</h3>
                                                <small>{{ $t('reports.stock_in_count') }}</small>
                                            </div>
                                            <div class="report-qty">{{ summary.stock_in_qty }} unit</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="report-card bg-danger text-white">
                                            <div class="report-icon"><i class="fa fa-arrow-up"></i></div>
                                            <div class="report-data">
                                                <h3>{{ summary.stock_out_count }}</h3>
                                                <small>{{ $t('reports.stock_out_count') }}</small>
                                            </div>
                                            <div class="report-qty">{{ summary.stock_out_qty }} unit</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="report-card bg-warning text-dark">
                                            <div class="report-icon"><i class="fa fa-clock"></i></div>
                                            <div class="report-data">
                                                <h3>{{ summary.pending_approval }}</h3>
                                                <small>{{ $t('reports.pending_approval') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="report-card bg-info text-white">
                                            <div class="report-icon"><i class="fa fa-clipboard-check"></i></div>
                                            <div class="report-data">
                                                <h3>{{ summary.opname_count }}</h3>
                                                <small>{{ $t('reports.opname_done') }}</small>
                                            </div>
                                            <div class="report-qty">
                                                <span class="text-success">+{{ summary.opname_surplus }}</span> /
                                                <span class="text-danger">-{{ summary.opname_defisit }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item Stats -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="report-card-sm border-primary">
                                            <i class="fa fa-boxes text-primary"></i>
                                            <div>
                                                <h4>{{ summary.total_items }}</h4>
                                                <small>{{ $t('reports.total_material') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="report-card-sm border-warning">
                                            <i class="fa fa-exclamation-triangle text-warning"></i>
                                            <div>
                                                <h4>{{ summary.low_stock_items }}</h4>
                                                <small>{{ $t('reports.low_stock') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="report-card-sm border-danger">
                                            <i class="fa fa-times-circle text-danger"></i>
                                            <div>
                                                <h4>{{ summary.out_of_stock }}</h4>
                                                <small>{{ $t('reports.out_of_stock') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Audit Trail -->
                                <div class="report-card-sm border-secondary mb-4">
                                    <i class="fa fa-history text-secondary"></i>
                                    <div>
                                        <h4>{{ summary.movement_count }}</h4>
                                        <small>{{ $t('reports.total_movements') }}</small>
                                    </div>
                                </div>

                                <!-- Quick Export Links -->
                                <hr />
                                <h6 class="fw-bold mb-3"><i class="fa fa-download me-1"></i> {{ $t('reports.export_download') }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <a href="/apps/stock-ins/export/excel" class="export-link text-success">
                                            <i class="fa fa-file-excel"></i>
                                            <span>Export Excel</span>
                                            <small>{{ $t('reports.material_in') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-ins/download-all/pdf" class="export-link text-danger">
                                            <i class="fa fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                            <small>{{ $t('reports.recap_material_in') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-outs/export/excel" class="export-link text-success">
                                            <i class="fa fa-file-excel"></i>
                                            <span>Export Excel</span>
                                            <small>{{ $t('reports.material_out') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-outs/download-all/pdf" class="export-link text-danger">
                                            <i class="fa fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                            <small>{{ $t('reports.recap_material_out') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-movements/export/excel" class="export-link text-success">
                                            <i class="fa fa-file-excel"></i>
                                            <span>Export Excel</span>
                                            <small>{{ $t('reports.audit_trail') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-movements/download-all/pdf" class="export-link text-danger">
                                            <i class="fa fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                            <small>{{ $t('reports.recap_audit_trail') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-opnames/export/excel" class="export-link text-success">
                                            <i class="fa fa-file-excel"></i>
                                            <span>Export Excel</span>
                                            <small>{{ $t('reports.opname_result') }}</small>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/apps/stock-opnames/download-all/pdf" class="export-link text-danger">
                                            <i class="fa fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                            <small>{{ $t('reports.recap_opname') }}</small>
                                        </a>
                                    </div>
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

<style>
.report-card {
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
}
.report-icon {
    font-size: 24px;
    opacity: 0.8;
}
.report-data h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
}
.report-data small {
    font-size: 12px;
    opacity: 0.85;
}
.report-qty {
    position: absolute;
    bottom: 8px;
    right: 14px;
    font-size: 11px;
    opacity: 0.8;
}

.report-card-sm {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: white;
    border-radius: 10px;
    border-left: 4px solid;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.report-card-sm i {
    font-size: 24px;
}
.report-card-sm h4 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
}
.report-card-sm small {
    font-size: 12px;
    color: #64748b;
}

.export-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 16px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}
.export-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-decoration: none;
}
.export-link i {
    font-size: 28px;
}
.export-link span {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
}
.export-link small {
    font-size: 11px;
    color: #64748b;
}
</style>
