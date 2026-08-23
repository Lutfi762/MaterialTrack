<template>
    <Head><title>Dashboard - MaterialTrack</title></Head>

    <div class="dashboard-page">

        <!-- Header: Greeting + Filters + Actions -->
        <div class="dash-header">
            <div class="header-top">
                <div class="header-left">
                    <h1 class="dash-title">{{ greeting }}, {{ userName }}</h1>
                    <p class="dash-subtitle">{{ todayFormatted }} · <span class="live-time">{{ liveClock }}</span></p>
                </div>
                <div class="header-actions">
                    <Link href="/apps/items/create" class="action-btn btn-material">
                        <i class="fa fa-plus"></i>
                        {{ $t('dashboard.quick_actions.add_material') }}
                    </Link>
                    <Link href="/apps/stock-ins/create" class="action-btn btn-secondary">
                        <i class="fa fa-arrow-down"></i>
                        {{ $t('dashboard.actions.stock_in') }}
                    </Link>
                    <Link href="/apps/stock-outs/create" class="action-btn btn-primary">
                        <i class="fa fa-arrow-up"></i>
                        {{ $t('dashboard.actions.stock_out') }}
                    </Link>
                </div>
            </div>
            <div class="header-filters">
                <div v-if="is_admin" class="filter-group department-filter">
                    <i class="fa fa-building filter-icon"></i>
                    <button
                        ref="departmentTrigger"
                        type="button"
                        class="filter-select department-trigger"
                        :aria-expanded="departmentMenuOpen"
                        aria-haspopup="listbox"
                        @click="departmentMenuOpen = !departmentMenuOpen"
                        @keydown.escape="departmentMenuOpen = false"
                    >
                        {{ selectedDepartmentName }}
                        <i class="fa fa-chevron-down department-chevron" aria-hidden="true"></i>
                    </button>
                    <div v-if="departmentMenuOpen" class="department-menu" role="listbox">
                        <button type="button" class="department-option" :class="{ active: selectedDepartment === null }" @click="selectDepartment(null)">{{ $t('dashboard.filter.all_departments') }}</button>
                        <button v-for="dept in departments" :key="dept.id" type="button" class="department-option" :class="{ active: selectedDepartment === dept.id }" @click="selectDepartment(dept.id)">{{ dept.name }}</button>
                    </div>
                </div>
                <div class="filter-separator">
                    <span class="filter-label">{{ $t('dashboard.period.label') }}</span>
                </div>
                <div class="filter-group">
                    <i class="fa fa-calendar filter-icon"></i>
                    <input type="date" class="filter-date" v-model="filterDateFrom" @change="applyDateFilter">
                    <span class="filter-dash">—</span>
                    <input type="date" class="filter-date" v-model="filterDateTo" @change="applyDateFilter">
                </div>
                <button class="filter-reset" @click="resetDateFilter">
                    <i class="fa fa-undo"></i>
                </button>
            </div>
        </div>

        <!-- Pending Approval Banner -->
        <div v-if="!is_admin && my_pending_approvals > 0" class="pending-banner">
            <div class="pending-icon"><i class="fa fa-clock"></i></div>
            <div class="pending-text">
                <strong>{{ $t('dashboard.pending_approval', { count: my_pending_approvals }) }}</strong>
            </div>
            <Link href="/apps/stock-outs?status=pending" class="pending-link">
                {{ $t('dashboard.view_all') }} <i class="fa fa-arrow-right"></i>
            </Link>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card" v-for="(kpi, idx) in kpiCards" :key="idx" :class="kpi.alert ? 'kpi-alert' : ''">
                <div class="kpi-header">
                    <div class="kpi-icon" :class="kpi.iconClass">
                        <i :class="kpi.icon"></i>
                    </div>
                    <span class="kpi-trend" :class="kpi.trendClass">
                        <i v-if="kpi.trendIcon" :class="kpi.trendIcon"></i>{{ kpi.trend }}
                    </span>
                </div>
                <span class="kpi-label">{{ kpi.label }}</span>
                <div class="kpi-value">{{ kpi.value }}</div>
                <div class="kpi-footer">
                    <span class="status-dot" :class="kpi.dotClass"></span>
                    {{ kpi.footer }}
                </div>
            </div>
        </div>

        <!-- Row: Period Comparison + Alerts -->
        <div class="row-2-1">
            <!-- Period Comparison -->
            <div class="period-comparison">
                <div class="period-card">
                    <div class="period-header">
                        <div class="period-icon icon-green"><i class="fa fa-arrow-down"></i></div>
                        <div>
                            <div class="period-label">{{ $t('dashboard.period.stock_in_this_month') }}</div>
                            <div class="period-value">{{ period_comparison.stock_in.this_month }}</div>
                        </div>
                    </div>
                    <div class="period-footer">
                        <span v-if="period_comparison.stock_in.change > 0" class="change-badge change-up">
                            <i class="fa fa-arrow-up"></i> +{{ period_comparison.stock_in.change }}%
                        </span>
                        <span v-else-if="period_comparison.stock_in.change < 0" class="change-badge change-down">
                            <i class="fa fa-arrow-down"></i> {{ period_comparison.stock_in.change }}%
                        </span>
                        <span v-else class="change-badge change-flat">
                            <i class="fa fa-minus"></i> 0%
                        </span>
                        <span class="change-label">{{ $t('dashboard.period.vs_last_month', { count: period_comparison.stock_in.last_month }) }}</span>
                    </div>
                </div>
                <div class="period-card">
                    <div class="period-header">
                        <div class="period-icon icon-red"><i class="fa fa-arrow-up"></i></div>
                        <div>
                            <div class="period-label">{{ $t('dashboard.period.stock_out_this_month') }}</div>
                            <div class="period-value">{{ period_comparison.stock_out.this_month }}</div>
                        </div>
                    </div>
                    <div class="period-footer">
                        <span v-if="period_comparison.stock_out.change > 0" class="change-badge change-up">
                            <i class="fa fa-arrow-up"></i> +{{ period_comparison.stock_out.change }}%
                        </span>
                        <span v-else-if="period_comparison.stock_out.change < 0" class="change-badge change-down">
                            <i class="fa fa-arrow-down"></i> {{ period_comparison.stock_out.change }}%
                        </span>
                        <span v-else class="change-badge change-flat">
                            <i class="fa fa-minus"></i> 0%
                        </span>
                        <span class="change-label">{{ $t('dashboard.period.vs_last_month', { count: period_comparison.stock_out.last_month }) }}</span>
                    </div>
                </div>
            </div>

            <!-- Expiry Alert OR Recent Movements -->
            <div v-if="expiring_items && expiring_items.length > 0" class="card expiry-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="section-icon icon-amber"><i class="fa fa-clock"></i></div>
                        <div>
                            <h3 class="card-title">{{ $t('dashboard.expiry.title') }}</h3>
                            <p class="card-subtitle">{{ $t('dashboard.expiry.subtitle', { count: expiring_items.length }) }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ $t('dashboard.expiry.col_material') }}</th>
                                    <th>{{ $t('dashboard.expiry.col_code') }}</th>
                                    <th>{{ $t('dashboard.expiry.col_qty') }}</th>
                                    <th>{{ $t('dashboard.expiry.col_expiry') }}</th>
                                    <th>{{ $t('dashboard.expiry.col_days_left') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in expiring_items" :key="item.id">
                                    <td>
                                        <div class="material-info">
                                            <div class="material-icon icon-amber"><i class="fa fa-box"></i></div>
                                            <span class="material-name">{{ item.item_name }}</span>
                                        </div>
                                    </td>
                                    <td><code class="item-code">{{ item.item_code }}</code></td>
                                    <td>{{ item.qty }} {{ item.unit }}</td>
                                    <td>{{ item.expiry_date }}</td>
                                    <td>
                                        <span class="status-badge" :class="item.days_left <= 7 ? 'badge-red' : 'badge-orange'">
                                            {{ item.days_left }} {{ $t('dashboard.expiry.days') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else-if="!is_admin && recent_movements.length" class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('dashboard.recent_movements.title') }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ $t('dashboard.recent_movements.time') }}</th>
                                    <th>{{ $t('dashboard.recent_movements.item') }}</th>
                                    <th>{{ $t('dashboard.recent_movements.type') }}</th>
                                    <th>{{ $t('dashboard.recent_movements.quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in recent_movements" :key="m.id">
                                    <td>{{ m.created_at }}</td>
                                    <td>{{ m.item_name }}</td>
                                    <td>
                                        <span class="status-badge" :class="m.type === 'in' ? 'badge-green' : 'badge-red'">
                                            {{ m.type === 'in' ? $t('dashboard.recent_movements.in') : $t('dashboard.recent_movements.out') }}
                                        </span>
                                    </td>
                                    <td>{{ m.qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else class="card empty-alert-card">
                <div class="empty-state">
                    <i class="fa fa-check-circle"></i>
                    <p>{{ $t('dashboard.kpi.all_healthy') }}</p>
                </div>
            </div>
        </div>

        <!-- Row: AI Insight + Quick Actions -->
        <div class="row-equal">
            <!-- AI Insight -->
            <div class="card ai-insight-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="ai-icon"><i class="fa fa-robot"></i></div>
                        <div>
                            <h3 class="card-title">{{ $t('dashboard.ai_insights.title') }}</h3>
                            <p class="card-subtitle">{{ $t('dashboard.ai_insights.subtitle') }}</p>
                        </div>
                    <div class="chart-period-toggle" role="group" aria-label="Periode grafik">
                        <button type="button" :class="{ active: chartPeriod === 'month' }" @click="chartPeriod = 'month'">Bulan</button>
                        <button type="button" :class="{ active: chartPeriod === 'year' }" @click="chartPeriod = 'year'">Tahun</button>
                    </div>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="restock_suggestions && restock_suggestions.length > 0">
                        <div class="restock-label">{{ $t('dashboard.restock.label') }}</div>
                        <div v-for="item in restock_suggestions" :key="item.id" class="restock-item">
                            <div class="restock-info">
                                <div class="restock-name">{{ item.name }}</div>
                                <div class="restock-meta">
                                    {{ $t('dashboard.restock.stock') }} {{ item.current_stock }} {{ item.unit }}
                                    · {{ $t('dashboard.restock.min') }} {{ item.min_stock }}
                                    <span v-if="item.days_left !== null"> · {{ $t('dashboard.restock.days_left', { count: item.days_left }) }}</span>
                                </div>
                            </div>
                            <div class="restock-qty">+{{ item.reorder_qty }} {{ item.unit }}</div>
                        </div>
                    </div>
                    <div v-else class="restock-empty">
                        <i class="fa fa-check-circle" style="color: #10b981;"></i>
                        <div>
                            <div class="restock-empty-title">{{ $t('dashboard.restock.status_label') }}</div>
                            <div class="restock-empty-text">{{ $t('dashboard.restock.all_safe') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <Link v-if="hasAnyPermission(['ai_assistant.use'])" href="/apps/ai-assistant" class="ai-btn">
                        <i class="fa fa-robot"></i>
                        {{ $t('dashboard.ai_insights.open_assistant') }}
                        <i class="fa fa-arrow-right"></i>
                    </Link>
                    <span v-else class="ai-disabled">{{ $t('dashboard.ai_insights.permission_required') }}</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card quick-actions-card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('dashboard.quick_actions.title') }}</h3>
                </div>
                <div class="card-body">
                    <Link href="/apps/items/create" class="quick-action-item">
                        <div class="qa-icon icon-blue"><i class="fa fa-plus"></i></div>
                        <div class="qa-content">
                            <div class="qa-label">{{ $t('dashboard.quick_actions.add_material') }}</div>
                            <div class="qa-desc">{{ $t('dashboard.quick_actions.add_material_desc') }}</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>
                    <Link href="/apps/stock-ins/create" class="quick-action-item">
                        <div class="qa-icon icon-green"><i class="fa fa-arrow-down"></i></div>
                        <div class="qa-content">
                            <div class="qa-label">{{ $t('dashboard.quick_actions.stock_in') }}</div>
                            <div class="qa-desc">{{ $t('dashboard.quick_actions.stock_in_desc') }}</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>
                    <Link href="/apps/stock-outs/create" class="quick-action-item">
                        <div class="qa-icon icon-orange"><i class="fa fa-arrow-up"></i></div>
                        <div class="qa-content">
                            <div class="qa-label">{{ $t('dashboard.quick_actions.stock_out') }}</div>
                            <div class="qa-desc">{{ $t('dashboard.quick_actions.stock_out_desc') }}</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>
                    <Link href="/apps/stock-movements" class="quick-action-item">
                        <div class="qa-icon icon-purple"><i class="fa fa-file-lines"></i></div>
                        <div class="qa-content">
                            <div class="qa-label">{{ $t('dashboard.quick_actions.reports') }}</div>
                            <div class="qa-desc">{{ $t('dashboard.quick_actions.reports_desc') }}</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row-charts">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('dashboard.charts.stock_movement') }}</h3>
                </div>
                <div class="card-body">
                    <apexchart type="area" height="260" :options="chartMovementOptions" :series="chartMovementSeries" />
                </div>
            </div>
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('dashboard.charts.stock_status') }}</h3>
                </div>
                <div class="card-body">
                    <apexchart v-if="hasStockData" type="donut" height="260" :options="chartStatusOptions" :series="stock_status.data" />
                    <div v-else class="empty-state">
                        <i class="fa fa-inbox"></i>
                        <p>{{ $t('dashboard.tables.no_data') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row-equal">
            <!-- Low Stock -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('dashboard.tables.low_stock_alert') }}</h3>
                    <Link href="/apps/items" class="view-all-link">
                        {{ $t('dashboard.tables.view_all') }} <i class="fa fa-arrow-right"></i>
                    </Link>
                </div>
                <div class="card-body">
                    <div v-if="low_stock_items && low_stock_items.length > 0" class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ $t('dashboard.tables.material') }}</th>
                                    <th>{{ $t('dashboard.tables.code') }}</th>
                                    <th>{{ $t('dashboard.tables.stock_level') }}</th>
                                    <th>{{ $t('dashboard.tables.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in low_stock_items.slice(0, 5)" :key="item.id">
                                    <td>
                                        <div class="material-info">
                                            <div class="material-icon" :class="item.current_stock <= 0 ? 'icon-red' : 'icon-orange'">
                                                <i class="fa fa-box"></i>
                                            </div>
                                            <span class="material-name">{{ item.name }}</span>
                                        </div>
                                    </td>
                                    <td><code class="item-code">{{ item.code }}</code></td>
                                    <td>
                                        <div class="stock-progress">
                                            <div class="progress-bar">
                                                <div class="progress-fill" :class="item.current_stock <= 0 ? 'fill-red' : 'fill-orange'"
                                                     :style="{ width: stockPercent(item) + '%' }"></div>
                                            </div>
                                            <span class="stock-text">{{ item.current_stock }} / {{ item.min_stock }} {{ item.unit }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge" :class="item.current_stock <= 0 ? 'badge-red' : 'badge-orange'">
                                            {{ item.current_stock <= 0 ? $t('dashboard.tables.out_of_stock') : $t('dashboard.tables.low_stock') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="empty-state">
                        <i class="fa fa-check-circle"></i>
                        <p>{{ $t('dashboard.tables.all_healthy') }}</p>
                    </div>
                </div>
            </div>

            <!-- Top Materials -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('dashboard.tables.top_materials') }}</h3>
                    <span class="card-badge">{{ $t('dashboard.tables.last_7_days') }}</span>
                </div>
                <div class="card-body">
                    <div v-if="hasTopData" class="usage-list">
                        <div v-for="(name, idx) in product.slice(0, 5)" :key="idx" class="usage-item">
                            <div class="usage-rank">{{ idx + 1 }}</div>
                            <div class="usage-info">
                                <div class="usage-name">{{ name }}</div>
                                <div class="usage-bar">
                                    <div class="usage-fill" :style="{ width: (total[idx] / Math.max(...total) * 100) + '%' }"></div>
                                </div>
                            </div>
                            <div class="usage-value">{{ total[idx] }} {{ $t('dashboard.tables.units') }}</div>
                        </div>
                    </div>
                    <div v-else class="empty-state">
                        <i class="fa fa-chart-bar"></i>
                        <p>{{ $t('dashboard.tables.no_usage') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouse Overview -->
        <div class="card warehouse-card">
            <div class="card-header">
                <h3 class="card-title">{{ $t('dashboard.warehouse.title') }}</h3>
                <Link href="/apps/locations" class="view-all-link">
                    {{ $t('dashboard.warehouse.manage_locations') }} <i class="fa fa-arrow-right"></i>
                </Link>
            </div>
            <div class="card-body">
                <div class="warehouse-stats">
                    <div class="warehouse-stat">
                        <div class="stat-icon icon-blue"><i class="fa fa-warehouse"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">{{ $t('dashboard.warehouse.total_locations') }}</div>
                            <div class="stat-value">{{ animatedLocations }}</div>
                        </div>
                    </div>
                    <div class="warehouse-stat">
                        <div class="stat-icon icon-green"><i class="fa fa-boxes-stacked"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">{{ $t('dashboard.warehouse.active_storage') }}</div>
                            <div class="stat-value">{{ stock_status.data[0] || 0 }}</div>
                        </div>
                    </div>
                    <div class="warehouse-stat">
                        <div class="stat-icon icon-purple"><i class="fa fa-layer-group"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">{{ $t('dashboard.warehouse.categories') }}</div>
                            <div class="stat-value">{{ animatedCategories }}</div>
                        </div>
                    </div>
                    <div class="warehouse-stat">
                        <div class="stat-icon icon-orange"><i class="fa fa-gauge-high"></i></div>
                        <div class="stat-content">
                            <div class="stat-label">{{ $t('dashboard.warehouse.utilization') }}</div>
                            <div class="stat-value">{{ utilizationPercent }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stitch stock condition and warehouse capacity panels -->
        <div class="dashboard-bottom-grid">
            <div class="card stock-condition-card">
                <div class="card-header"><h3 class="card-title">Kondisi Stok</h3></div>
                <div class="card-body stock-condition-body">
                    <div class="stock-donut" :style="{ background: stockConditionGradient }">
                        <div class="stock-donut-center"><span>Total</span><strong>100%</strong></div>
                    </div>
                    <div class="stock-condition-list">
                        <div v-for="status in stockConditionItems" :key="status.label" class="stock-condition-item">
                            <span><i class="condition-dot" :class="status.className"></i>{{ status.label }} ({{ status.percent }}%)</span>
                            <strong>{{ status.value }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card capacity-card">
                <div class="card-header"><h3 class="card-title"><i class="fa fa-warehouse capacity-title-icon"></i> Kapasitas Gudang</h3></div>
                <div class="card-body capacity-list">
                    <div v-for="warehouse in warehouseCapacity" :key="warehouse.name" class="capacity-item">
                        <div><strong>{{ warehouse.name }}</strong><span :class="warehouse.className">{{ warehouse.percent }}%</span></div>
                        <div class="capacity-track"><i :class="warehouse.className" :style="{ width: warehouse.percent + '%' }"></i></div>
                        <small>{{ warehouse.note }}</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },

    props: {
        sales_date:         Array,
        grand_total:        Array,
        material_out_today: Object,
        material_in_today:  Object,
        product:            Array,
        total:              Array,
        stock_status:       Object,
        low_stock_items:    Array,
        low_stock_count:    Number,
        expiring_items:     Array,
        restock_suggestions: Array,
        period_comparison:  Object,
        departments:        Array,
        current_department_id: [Number, null],
        is_admin:           Boolean,
        total_inventory_value: { type: Number, default: 0 },
        recent_movements:       { type: Array, default: () => [] },
        my_pending_approvals:   { type: Number, default: 0 },
        date_from:            String,
        date_to:              String,
    },

    data() {
        return {
            animatedTotalMaterials: 0,
            animatedAvailableStock: 0,
            animatedLowStock: 0,
            animatedMonthlyOut: 0,
            animatedLocations: 0,
            animatedCategories: 0,
            liveClock: '',
            clockInterval: null,
            selectedDepartment: this.current_department_id,
            filterDateFrom: this.date_from,
            filterDateTo: this.date_to,
            chartPeriod: 'month',
            departmentMenuOpen: false,
        };
    },

    computed: {
        userName() {
            const page = usePage();
            return page.props.auth?.user?.name ?? 'User';
        },
        greeting() {
            const hour = new Date().getHours();
            if (hour < 11) return this.$t('dashboard.greeting.morning');
            if (hour < 15) return this.$t('dashboard.greeting.afternoon');
            if (hour < 18) return this.$t('dashboard.greeting.evening');
            return this.$t('dashboard.greeting.night');
        },
        todayFormatted() {
            return new Date().toLocaleDateString('en-US', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
            });
        },
        selectedDepartmentName() {
            return this.departments.find((department) => department.id === this.selectedDepartment)?.name
                ?? this.$t('dashboard.filter.all_departments');
        },
        hasStockData() {
            return this.stock_status?.data?.some(v => v > 0);
        },
        hasTopData() {
            return this.total?.length > 0 && this.total.some(v => v > 0);
        },
        totalMaterials() {
            return (this.stock_status?.data || []).reduce((a, b) => a + b, 0);
        },
        availableStock() {
            return this.stock_status?.data[0] || 0;
        },
        utilizationPercent() {
            const total = this.totalMaterials;
            if (total === 0) return 0;
            return Math.round((this.availableStock / total) * 100);
        },
        kpiCards() {
            return [
                {
                    label: this.$t('dashboard.kpi.total_materials'),
                    value: this.animatedTotalMaterials,
                    icon: 'fa fa-box',
                    iconClass: 'icon-blue',
                    trend: '+12%', trendIcon: 'fa fa-arrow-trend-up', trendClass: 'trend-up',
                    dotClass: 'dot-blue',
                    footer: this.$t('dashboard.kpi.active_items'),
                },
                {
                    label: this.$t('dashboard.kpi.available_stock'),
                    value: this.animatedAvailableStock,
                    icon: 'fa fa-check-circle',
                    iconClass: 'icon-green',
                    trend: '+5.4%', trendIcon: 'fa fa-arrow-trend-up', trendClass: 'trend-up',
                    dotClass: 'dot-green',
                    footer: this.$t('dashboard.kpi.safe_level'),
                },
                {
                    label: this.$t('dashboard.kpi.low_stock_alert'),
                    value: this.animatedLowStock,
                    icon: this.low_stock_count > 0 ? 'fa fa-exclamation-triangle' : 'fa fa-check-circle',
                    iconClass: this.low_stock_count > 0 ? 'icon-orange' : 'icon-green',
                    trend: this.low_stock_count > 0 ? 'Kritis' : 'Aman', trendIcon: '', trendClass: this.low_stock_count > 0 ? 'trend-critical' : 'trend-up',
                    dotClass: this.low_stock_count > 0 ? 'dot-orange' : 'dot-green',
                    footer: this.low_stock_count > 0 ? this.$t('dashboard.kpi.requires_attention') : this.$t('dashboard.kpi.all_healthy'),
                    alert: this.low_stock_count > 0,
                },
                {
                    label: this.$t('dashboard.kpi.monthly_stock_out'),
                    value: this.animatedMonthlyOut,
                    icon: 'fa fa-arrow-trend-up',
                    iconClass: 'icon-purple',
                    trend: '-2.1%', trendIcon: 'fa fa-arrow-trend-down', trendClass: 'trend-down',
                    dotClass: 'dot-purple',
                    footer: this.material_out_today.total_transaction + ' ' + this.$t('dashboard.kpi.transactions_today'),
                },
                {
                    label: this.$t('dashboard.kpi.inventory_value'),
                    value: 'Rp ' + this.formatPrice(this.total_inventory_value),
                    icon: 'fa fa-coins',
                    iconClass: 'icon-indigo',
                    trend: '+8.2%', trendIcon: 'fa fa-arrow-trend-up', trendClass: 'trend-up',
                    dotClass: 'dot-indigo',
                    footer: this.$t('dashboard.kpi.total_stock_value'),
                },
            ];
        },
        chartMovementSeries() {
            return [{ name: 'Stock Out', data: this.grand_total ?? [] }];
        },
        stockConditionItems() {
            const total = this.totalMaterials || 1;
            const critical = this.low_stock_count || 0;
            const safe = Math.max(total - critical, 0);
            const safePercent = Math.round((safe / total) * 100);
            const criticalPercent = Math.round((critical / total) * 100);
            return [
                { label: 'Aman', percent: safePercent, value: safe, className: 'condition-safe' },
                { label: 'Menengah', percent: 0, value: 0, className: 'condition-medium' },
                { label: 'Kritis', percent: criticalPercent, value: critical, className: 'condition-critical' },
            ];
        },
        stockConditionGradient() {
            const [safe, medium] = this.stockConditionItems;
            return `conic-gradient(var(--color-primary) 0 ${safe.percent}%, var(--color-warning) ${safe.percent}% ${safe.percent + medium.percent}%, var(--color-danger) ${safe.percent + medium.percent}% 100%)`;
        },
        warehouseCapacity() {
            const utilization = this.utilizationPercent;
            return [
                { name: 'Gudang Utama', percent: utilization, note: `Sisa kapasitas: ${100 - utilization}%`, className: utilization > 85 ? 'capacity-danger' : 'capacity-primary' },
                { name: 'Penyimpanan Aktif', percent: Math.min(this.availableStock ? 65 : 0, 100), note: `${this.availableStock} item tersimpan`, className: 'capacity-secondary' },
                { name: 'Area Perlu Perhatian', percent: Math.min((this.low_stock_count || 0) * 10, 100), note: `${this.low_stock_count || 0} item perlu restock`, className: 'capacity-danger' },
            ];
        },
        chartMovementOptions() {
            return {
                chart: {
                    type: 'bar',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                colors: ['var(--color-primary)'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '46%' } },
                fill: { opacity: 1 },
                stroke: { width: 0 },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: this.sales_date ?? [],
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#64748b' } }
                },
                grid: { strokeDashArray: 3, borderColor: '#e5e7eb', padding: 0 },
                tooltip: { theme: 'light', y: { formatter: (v) => v + ' units' } }
            };
        },
        chartStatusOptions() {
            return {
                chart: { toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
                labels: this.stock_status?.labels ?? [],
                colors: ['#10b981', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom', fontSize: '12px',
                    markers: { width: 8, height: 8, radius: 2 },
                    itemMargin: { horizontal: 12, vertical: 8 }
                },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: { show: true, label: 'Total Items', fontSize: '14px', color: '#1e293b' }
                            }
                        }
                    }
                },
                tooltip: { theme: 'light' }
            };
        },
    },

    methods: {
        stockPercent(item) {
            if (!item.min_stock || item.min_stock === 0) return item.current_stock > 0 ? 100 : 0;
            return Math.min(Math.max((item.current_stock / item.min_stock) * 100, 4), 100);
        },
        formatPrice(value) {
            if (!value) return '0';
            return new Intl.NumberFormat('id-ID').format(Math.round(value));
        },
        animateCounter(target, key, duration = 1200) {
            const startTime = performance.now();
            const step = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                this[key] = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        },
        updateClock() {
            this.liveClock = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },
        initStaggerAnimation() {
            const elements = document.querySelectorAll('[data-stagger]');
            elements.forEach((el, idx) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(16px)';
                setTimeout(() => {
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, idx * 70);
            });
        },
        onDepartmentChange() {
            this.$inertia.get('/apps/dashboard', {
                department_id: this.selectedDepartment || undefined,
            }, { preserveState: true });
        },
        selectDepartment(departmentId) {
            this.selectedDepartment = departmentId;
            this.departmentMenuOpen = false;
            this.onDepartmentChange();
        },
        closeDepartmentMenu(event) {
            if (!this.$el.querySelector('.department-filter')?.contains(event.target)) this.departmentMenuOpen = false;
        },
        applyDateFilter() {
            const params = new URLSearchParams();
            if (this.filterDateFrom) params.set('date_from', this.filterDateFrom);
            if (this.filterDateTo) params.set('date_to', this.filterDateTo);
            this.$inertia.get('/apps/dashboard?' + params.toString(), {}, { preserveState: true });
        },
        resetDateFilter() {
            this.filterDateFrom = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-01';
            this.filterDateTo = new Date().toISOString().split('T')[0];
            this.applyDateFilter();
        }
    },

    mounted() {
        this.animateCounter(this.totalMaterials, 'animatedTotalMaterials');
        this.animateCounter(this.availableStock, 'animatedAvailableStock');
        this.animateCounter(this.low_stock_count ?? 0, 'animatedLowStock');
        this.animateCounter(this.grand_total?.reduce((a, b) => a + b, 0) || 0, 'animatedMonthlyOut');
        this.animateCounter(this.stock_status?.data?.length || 0, 'animatedLocations', 1000);
        this.animateCounter(12, 'animatedCategories', 1000);

        this.updateClock();
        this.clockInterval = setInterval(this.updateClock, 1000);
        document.addEventListener('click', this.closeDepartmentMenu);
        this.$nextTick(() => { this.initStaggerAnimation(); });
    },

    beforeUnmount() {
        if (this.clockInterval) clearInterval(this.clockInterval);
        document.removeEventListener('click', this.closeDepartmentMenu);
    },
};
</script>

<style scoped>
/* ============================================
   DESIGN SYSTEM — consistent tokens
   ============================================ */
.dashboard-page {
    background: #f8fafc;
    min-height: 100vh;
    padding: 24px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* ---- Shared Card ---- */
.card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
.card-header {
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title { font-size: 15px; font-weight: 600; color: #0f172a; margin: 0; }
.card-subtitle { font-size: 12px; color: #94a3b8; margin: 2px 0 0; }
.card-body { padding: 18px 22px; }
.card-footer { padding: 14px 22px; border-top: 1px solid #f1f5f9; }
.card-badge {
    font-size: 11px; padding: 3px 10px; background: #f1f5f9;
    color: #475569; border-radius: 6px; font-weight: 500;
}
.header-content { display: flex; align-items: center; gap: 12px; }

/* ============================================
   HEADER
   ============================================ */
.dash-header {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px 26px;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}
.header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 16px;
}
.header-left { flex: 1; min-width: 260px; }
.dash-title { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0 0 4px; letter-spacing: -0.02em; }
.dash-subtitle { font-size: 13px; color: #64748b; margin: 0; }
.live-time { font-variant-numeric: tabular-nums; color: #475569; font-weight: 500; }
.header-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

.action-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; border: 1px solid #e2e8f0; background: white;
    color: #334155; transition: all 0.15s ease;
}
.action-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-decoration: none; }
.btn-primary { background: #6366f1; border-color: #6366f1; color: white; }
.btn-primary:hover { background: #4f46e5; color: white; }
.btn-secondary { background: white; color: #475569; }
.btn-outline-red { background: white; border-color: #fecaca; color: #dc2626; }
.btn-outline-red:hover { background: #fef2f2; border-color: #fca5a5; }

/* Filters row */
.header-filters {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.filter-group {
    display: flex; align-items: center; gap: 6px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 0 10px; height: 36px;
}
.department-filter { position: relative; z-index: 20; }
.filter-icon { color: #94a3b8; font-size: 12px; }
.filter-select {
    border: none; background: transparent; font-size: 13px; font-weight: 500;
    color: #334155; outline: none; padding: 6px 0; font-family: inherit;
}
.department-trigger { display: flex; align-items: center; gap: 10px; cursor: pointer; text-align: left; }
.department-chevron { font-size: 10px; color: #94a3b8; }
.department-menu {
    position: absolute; top: calc(100% + 8px); left: 0; z-index: 100;
    min-width: 220px; max-height: 280px; overflow-y: auto; padding: 6px;
    background: var(--color-surface, #fff); border: 1px solid var(--color-outline, #e2e8f0);
    border-radius: 10px; box-shadow: 0 16px 32px rgba(15, 23, 42, 0.18);
}
.department-option { display: block; width: 100%; padding: 9px 10px; border: 0; border-radius: 6px; background: transparent; color: var(--color-on-surface, #334155); font: inherit; font-size: 13px; text-align: left; cursor: pointer; }
.department-option:hover, .department-option.active { background: color-mix(in srgb, var(--color-primary, #6366f1) 12%, transparent); color: var(--color-primary, #4f46e5); }
.filter-date {
    border: none; background: transparent; font-size: 13px; font-weight: 500;
    color: #334155; outline: none; width: 120px; font-family: inherit;
}
.filter-dash { color: #cbd5e1; margin: 0 2px; }
.filter-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; }
.filter-separator { display: flex; align-items: center; }
.filter-reset {
    width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e2e8f0;
    background: white; color: #64748b; display: flex; align-items: center;
    justify-content: center; cursor: pointer; transition: all 0.15s ease; font-size: 12px;
}
.filter-reset:hover { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }

/* ============================================
   PENDING BANNER
   ============================================ */
.pending-banner {
    display: flex; align-items: center; gap: 12px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
    padding: 12px 18px; margin-bottom: 20px;
}
.pending-icon { color: #f59e0b; font-size: 16px; flex-shrink: 0; }
.pending-text { flex: 1; font-size: 13px; color: #92400e; }
.pending-link {
    font-size: 13px; font-weight: 600; color: #d97706;
    text-decoration: none; display: flex; align-items: center; gap: 4px;
}
.pending-link:hover { color: #b45309; text-decoration: underline; }

/* ============================================
   KPI GRID
   ============================================ */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.kpi-card {
    background: white; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 18px 20px; transition: all 0.2s ease;
}
.kpi-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
.kpi-card.kpi-alert { background: linear-gradient(to bottom, #fffbeb, white); border-color: #fde68a; }
.kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.kpi-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.kpi-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
}
.kpi-value { font-size: 30px; font-weight: 700; color: #0f172a; margin-bottom: 10px; line-height: 1; letter-spacing: -0.02em; }
.kpi-footer { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; }
.status-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ============================================
   LAYOUT ROWS
   ============================================ */
.row-2-1 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.row-equal {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.row-charts {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

/* ============================================
   PERIOD COMPARISON
   ============================================ */
.period-comparison {
    display: flex; flex-direction: column; gap: 12px;
}
.period-card {
    background: white; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 16px 18px; transition: all 0.2s ease;
}
.period-card:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.period-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.period-icon {
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;
}
.period-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
.period-value { font-size: 26px; font-weight: 700; color: #0f172a; line-height: 1; margin-top: 2px; }
.period-footer {
    display: flex; align-items: center; gap: 8px;
    padding-top: 10px; border-top: 1px solid #f1f5f9;
}
.change-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 600;
}
.change-up { background: #fef2f2; color: #dc2626; }
.change-down { background: #f0fdf4; color: #16a34a; }
.change-flat { background: #f1f5f9; color: #64748b; }
.change-label { font-size: 11px; color: #94a3b8; }

/* ============================================
   COLOR ICONS
   ============================================ */
.icon-blue { background: #eff6ff; color: #3b82f6; }
.icon-green { background: #f0fdf4; color: #10b981; }
.icon-orange { background: #fff7ed; color: #f97316; }
.icon-purple { background: #faf5ff; color: #a855f7; }
.icon-indigo { background: #eef2ff; color: #6366f1; }
.icon-red { background: #fef2f2; color: #ef4444; }
.icon-amber { background: #fffbeb; color: #f59e0b; }
.dot-blue { background: #3b82f6; }
.dot-green { background: #10b981; }
.dot-orange { background: #f97316; }
.dot-purple { background: #a855f7; }
.dot-indigo { background: #6366f1; }

/* ============================================
   SECTION ICON (small colored square)
   ============================================ */
.section-icon {
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
}

/* ============================================
   EXPIRY CARD
   ============================================ */
.expiry-card { border-color: #fde68a; background: linear-gradient(to bottom, #fffbeb, white); }
.expiry-card:hover { box-shadow: 0 4px 16px rgba(245,158,11,0.08); }

/* ============================================
   DATA TABLE
   ============================================ */
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th {
    text-align: left; padding: 10px 12px; font-size: 11px; font-weight: 600;
    color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;
    border-bottom: 1px solid #e2e8f0;
}
.data-table tbody td {
    padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #475569;
}
.data-table tbody tr:last-child td { border-bottom: none; }
.material-info { display: flex; align-items: center; gap: 8px; }
.material-icon {
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
}
.material-name { font-weight: 500; color: #0f172a; }
.item-code {
    font-size: 11px; padding: 2px 7px; background: #f1f5f9;
    color: #64748b; border-radius: 4px; font-family: 'SF Mono', 'Fira Code', monospace;
}
.status-badge { display: inline-block; padding: 3px 9px; border-radius: 5px; font-size: 11px; font-weight: 600; }
.badge-red { background: #fef2f2; color: #dc2626; }
.badge-orange { background: #fff7ed; color: #ea580c; }
.badge-green { background: #f0fdf4; color: #16a34a; }
.stock-progress { display: flex; flex-direction: column; gap: 3px; }
.progress-bar { width: 100%; height: 5px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
.fill-red { background: linear-gradient(90deg, #f87171, #ef4444); }
.fill-orange { background: linear-gradient(90deg, #fbbf24, #f97316); }
.stock-text { font-size: 11px; color: #94a3b8; font-variant-numeric: tabular-nums; }

/* ============================================
   AI INSIGHT
   ============================================ */
.ai-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; flex-shrink: 0;
}
.restock-label {
    font-size: 11px; font-weight: 600; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;
}
.restock-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 0; border-bottom: 1px solid #f1f5f9;
}
.restock-item:last-child { border-bottom: none; }
.restock-name { font-size: 13px; font-weight: 600; color: #0f172a; }
.restock-meta { font-size: 11px; color: #64748b; margin-top: 2px; }
.restock-qty {
    padding: 3px 9px; background: #fef2f2; color: #dc2626;
    border-radius: 5px; font-size: 12px; font-weight: 600; white-space: nowrap;
}
.restock-empty {
    display: flex; gap: 10px; align-items: flex-start; padding: 4px 0;
}
.restock-empty i { font-size: 18px; margin-top: 1px; flex-shrink: 0; }
.restock-empty-title { font-size: 13px; font-weight: 600; color: #0f172a; }
.restock-empty-text { font-size: 12px; color: #64748b; margin-top: 2px; }
.ai-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 11px; background: linear-gradient(135deg, #a855f7, #7c3aed);
    color: white; border-radius: 8px; text-decoration: none;
    font-size: 13px; font-weight: 600; transition: all 0.2s ease;
}
.ai-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(168,85,247,0.3); text-decoration: none; color: white; }
.ai-disabled { display: block; text-align: center; font-size: 12px; color: #94a3b8; padding: 10px; }

/* ============================================
   QUICK ACTIONS
   ============================================ */
.quick-action-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px; border-radius: 8px; text-decoration: none;
    transition: background 0.15s ease; margin-bottom: 4px;
}
.quick-action-item:last-child { margin-bottom: 0; }
.quick-action-item:hover { background: #f8fafc; text-decoration: none; }
.qa-icon {
    width: 38px; height: 38px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.qa-content { flex: 1; }
.qa-label { font-size: 13px; font-weight: 600; color: #0f172a; }
.qa-desc { font-size: 11px; color: #64748b; margin-top: 1px; }
.qa-arrow { color: #cbd5e1; font-size: 12px; }

/* ============================================
   USAGE LIST (Top Materials)
   ============================================ */
.usage-list { display: flex; flex-direction: column; gap: 10px; }
.usage-item { display: flex; align-items: center; gap: 10px; }
.usage-rank {
    width: 26px; height: 26px; border-radius: 6px; background: #f8fafc;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #475569; flex-shrink: 0;
}
.usage-info { flex: 1; }
.usage-name { font-size: 13px; font-weight: 500; color: #0f172a; margin-bottom: 5px; }
.usage-bar { height: 5px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.usage-fill { height: 100%; background: linear-gradient(90deg, #6366f1, #8b5cf6); border-radius: 3px; }
.usage-value { font-size: 12px; font-weight: 600; color: #475569; font-variant-numeric: tabular-nums; white-space: nowrap; }

/* ============================================
   VIEW ALL LINK
   ============================================ */
.view-all-link {
    display: flex; align-items: center; gap: 4px;
    font-size: 12px; color: #6366f1; text-decoration: none; font-weight: 500;
}
.view-all-link:hover { color: #4f46e5; text-decoration: none; }

/* ============================================
   WAREHOUSE
   ============================================ */
.warehouse-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.warehouse-stat {
    display: flex; align-items: center; gap: 12px;
    padding: 14px; background: #f8fafc; border-radius: 10px;
}
.stat-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
}
.stat-content { flex: 1; }
.stat-label { font-size: 11px; color: #64748b; font-weight: 500; margin-bottom: 3px; }
.stat-value { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1; }

/* ============================================
   EMPTY STATE
   ============================================ */
.empty-state {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 40px 16px; color: #94a3b8;
}
.empty-state i { font-size: 36px; margin-bottom: 10px; opacity: 0.5; }
.empty-state p { margin: 0; font-size: 13px; }
.empty-alert-card { display: flex; align-items: center; justify-content: center; min-height: 200px; }

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1200px) {
    .kpi-grid { grid-template-columns: repeat(3, 1fr); }
    .row-charts { grid-template-columns: 1fr; }
}
@media (max-width: 992px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .row-2-1, .row-equal { grid-template-columns: 1fr; }
    .warehouse-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .dashboard-page { padding: 16px; }
    .header-top { flex-direction: column; }
    .header-actions { width: 100%; }
    .action-btn { flex: 1; justify-content: center; }
    .header-filters { flex-direction: column; align-items: stretch; }
    .filter-group { width: 100%; }
    .filter-select, .filter-date { flex: 1; }
    .kpi-grid { grid-template-columns: 1fr; }
    .warehouse-stats { grid-template-columns: 1fr; }
}

/* Stitch dashboard surface layer. Existing classes keep all data bindings intact. */
.dashboard-page {
    --dash-muted: var(--color-on-surface-variant);
    --dash-line: color-mix(in srgb, var(--color-outline) 28%, transparent);
    --dash-soft: color-mix(in srgb, var(--color-primary) 7%, var(--color-surface));
    background: var(--color-bg);
    color: var(--color-on-surface);
    font-family: var(--font-sans);
}
.dash-header, .card, .kpi-card, .period-comparison, .period-card {
    background: var(--color-surface);
    border-color: var(--dash-line);
    box-shadow: 0 1px 2px color-mix(in srgb, var(--color-on-surface) 5%, transparent);
}
.dash-header { border-radius: 16px; padding: 24px; }
.dash-title, .card-title, .kpi-value, .period-value, .stat-value, .usage-name { color: var(--color-on-surface); }
.dash-subtitle, .live-time, .card-subtitle, .kpi-label, .kpi-footer, .period-label, .change-label, .stat-label, .usage-value, .usage-desc { color: var(--dash-muted); }
.header-filters, .card-header, .card-footer { border-color: var(--dash-line); }
.action-btn, .filter-reset { border-color: var(--dash-line); background: var(--color-surface); color: var(--color-on-surface); }
.action-btn:focus-visible, .filter-reset:focus-visible, .filter-select:focus-visible, .filter-date:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
.btn-primary { background: var(--color-primary); border-color: var(--color-primary); }
.btn-primary:hover { background: var(--color-primary-2); }
.btn-secondary:hover, .filter-reset:hover { background: var(--dash-soft); border-color: color-mix(in srgb, var(--color-primary) 30%, transparent); color: var(--color-primary); }
.btn-outline-red { color: var(--color-danger); border-color: color-mix(in srgb, var(--color-danger) 34%, transparent); }
.btn-outline-red:hover { background: color-mix(in srgb, var(--color-danger) 8%, var(--color-surface)); }
.filter-group, .usage-rank, .warehouse-stat { background: var(--dash-soft); border-color: var(--dash-line); }
.filter-select, .filter-date { color: var(--color-on-surface); }
.pending-banner { background: color-mix(in srgb, var(--color-warning) 10%, var(--color-surface)); border-color: color-mix(in srgb, var(--color-warning) 35%, transparent); }
.pending-icon, .pending-link { color: var(--color-warning); }
.pending-text { color: var(--color-on-surface); }
.kpi-card { border-radius: 16px; }
.kpi-card:hover, .card:hover { border-color: color-mix(in srgb, var(--color-primary) 30%, var(--dash-line)); box-shadow: 0 12px 28px color-mix(in srgb, var(--color-on-surface) 9%, transparent); }
.kpi-card.kpi-alert { background: linear-gradient(145deg, color-mix(in srgb, var(--color-warning) 10%, var(--color-surface)), var(--color-surface)); border-color: color-mix(in srgb, var(--color-warning) 34%, transparent); }
.usage-fill { background: linear-gradient(90deg, var(--color-primary), var(--color-primary-2)); }
.view-all-link { color: var(--color-primary); }
.view-all-link:hover { color: var(--color-primary-2); }
.dark .dashboard-page { --dash-soft: color-mix(in srgb, var(--color-primary) 10%, var(--color-surface)); }

/* Stitch bento details */
.header-actions .btn-material { border-color: color-mix(in srgb, var(--color-primary) 35%, transparent); color: var(--color-primary); }
.header-actions .btn-primary { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-2)); color: white; }
.kpi-header { align-items: flex-start; }
.kpi-trend { display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; line-height: 1; }
.trend-up { color: var(--color-primary); background: color-mix(in srgb, var(--color-primary) 13%, transparent); }
.trend-down, .trend-critical { color: var(--color-danger); background: color-mix(in srgb, var(--color-danger) 12%, transparent); }
.kpi-label { display: block; margin-top: 16px; text-transform: uppercase; letter-spacing: .08em; font-size: 11px; font-weight: 700; }
.kpi-value { margin-top: 4px; }
.chart-period-toggle { display: inline-flex; padding: 3px; border: 1px solid var(--dash-line); border-radius: 8px; background: var(--dash-soft); }
.chart-period-toggle button { border: 0; border-radius: 6px; background: transparent; color: var(--dash-muted); padding: 5px 9px; font: inherit; font-size: 11px; font-weight: 700; }
.chart-period-toggle button.active { color: white; background: var(--color-primary); }
.chart-period-toggle button:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
.dashboard-bottom-grid { display: grid; grid-template-columns: minmax(280px, .8fr) minmax(0, 1.2fr); gap: 20px; margin-top: 20px; }
.stock-condition-body { display: grid; grid-template-columns: 148px minmax(0, 1fr); gap: 24px; align-items: center; }
.stock-donut { width: 136px; height: 136px; border-radius: 50%; display: grid; place-items: center; }
.stock-donut-center { width: 100px; height: 100px; border-radius: 50%; background: var(--color-surface); display: flex; flex-direction: column; align-items: center; justify-content: center; }
.stock-donut-center span, .capacity-item small { color: var(--dash-muted); font-size: 11px; }
.stock-donut-center strong { color: var(--color-on-surface); font-size: 18px; }
.stock-condition-list, .capacity-list { display: grid; gap: 13px; }
.stock-condition-item, .capacity-item > div:first-child { display: flex; justify-content: space-between; gap: 10px; font-size: 12px; color: var(--dash-muted); }
.stock-condition-item strong, .capacity-item strong { color: var(--color-on-surface); }
.condition-dot { display: inline-block; width: 9px; height: 9px; margin-right: 7px; border-radius: 50%; }
.condition-safe, .capacity-primary { background: var(--color-primary); color: var(--color-primary); }
.condition-medium, .capacity-secondary { background: var(--color-warning); color: var(--color-warning); }
.condition-critical, .capacity-danger { background: var(--color-danger); color: var(--color-danger); }
.capacity-item { display: grid; gap: 7px; }
.capacity-track { height: 8px; border-radius: 999px; overflow: hidden; background: var(--dash-soft); }
.capacity-track i { display: block; height: 100%; border-radius: inherit; }
.capacity-item > div:first-child span { font-weight: 700; }
.capacity-title-icon { color: var(--color-primary); margin-right: 7px; }
@media (max-width: 768px) { .dashboard-bottom-grid, .stock-condition-body { grid-template-columns: 1fr; } .stock-donut { margin: auto; } }
</style>
