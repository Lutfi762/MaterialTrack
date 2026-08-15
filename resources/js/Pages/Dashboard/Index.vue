<template>
    <Head><title>Dashboard - MaterialTrack</title></Head>

    <div class="dashboard-page">

        <!-- Header Section -->
        <div class="dash-header">
            <div class="header-left">
                <h1 class="dash-title">{{ greeting }}, {{ userName }}</h1>
                <p class="dash-subtitle">{{ todayFormatted }} · <span class="live-time">{{ liveClock }}</span></p>
            </div>
            <div class="header-actions">
                <Link href="/apps/stock-ins/create" class="action-btn btn-secondary">
                    <i class="fa fa-arrow-down"></i>
                    Stock In
                </Link>
                <Link href="/apps/stock-outs/create" class="action-btn btn-primary">
                    <i class="fa fa-arrow-up"></i>
                    Stock Out
                </Link>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid fade-in-stagger">
            <div class="kpi-card" data-stagger="0">
                <div class="kpi-header">
                    <span class="kpi-label">Total Materials</span>
                    <div class="kpi-icon icon-blue">
                        <i class="fa fa-box"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ animatedTotalMaterials }}</div>
                <div class="kpi-footer">
                    <span class="status-dot dot-blue"></span>
                    Active items in inventory
                </div>
            </div>

            <div class="kpi-card" data-stagger="1">
                <div class="kpi-header">
                    <span class="kpi-label">Available Stock</span>
                    <div class="kpi-icon icon-green">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ animatedAvailableStock }}</div>
                <div class="kpi-footer">
                    <span class="status-dot dot-green"></span>
                    {{ stock_status.data[0] || 0 }} items safe level
                </div>
            </div>

            <div class="kpi-card" data-stagger="2" :class="{ 'kpi-alert': low_stock_count > 0 }">
                <div class="kpi-header">
                    <span class="kpi-label">Low Stock Alert</span>
                    <div class="kpi-icon" :class="low_stock_count > 0 ? 'icon-orange' : 'icon-green'">
                        <i class="fa" :class="low_stock_count > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle'"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ animatedLowStock }}</div>
                <div class="kpi-footer">
                    <span class="status-dot" :class="low_stock_count > 0 ? 'dot-orange' : 'dot-green'"></span>
                    {{ low_stock_count > 0 ? 'Requires attention' : 'All stock healthy' }}
                </div>
            </div>

            <div class="kpi-card" data-stagger="3">
                <div class="kpi-header">
                    <span class="kpi-label">Monthly Stock Out</span>
                    <div class="kpi-icon icon-purple">
                        <i class="fa fa-arrow-trend-up"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ animatedMonthlyOut }}</div>
                <div class="kpi-footer">
                    <span class="status-dot dot-purple"></span>
                    {{ material_out_today.total_transaction }} transactions today
                </div>
            </div>
        </div>

        <!-- AI Insight Card + Quick Actions -->
        <div class="content-grid fade-in-stagger">

            <!-- AI Insight -->
            <div class="card ai-insight-card" data-stagger="4">
                <div class="card-header">
                    <div class="header-content">
                        <div class="ai-icon">
                            <i class="fa fa-sparkles"></i>
                        </div>
                        <div>
                            <h3 class="card-title">AI Insights</h3>
                            <p class="card-subtitle">Powered by MaterialTrack AI</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="insight-item">
                        <i class="fa fa-chart-line insight-icon"></i>
                        <div class="insight-content">
                            <div class="insight-label">Inventory Prediction</div>
                            <div class="insight-value">{{ low_stock_count }} items need restocking within 7 days</div>
                        </div>
                    </div>
                    <div class="insight-item">
                        <i class="fa fa-lightbulb insight-icon"></i>
                        <div class="insight-content">
                            <div class="insight-label">Restock Recommendation</div>
                            <div class="insight-value">Order top {{ Math.min(5, low_stock_count) }} priority items</div>
                        </div>
                    </div>
                    <div class="insight-item">
                        <i class="fa fa-brain insight-icon"></i>
                        <div class="insight-content">
                            <div class="insight-label">AI Analysis</div>
                            <div class="insight-value">Stock movement patterns stable</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <Link v-if="hasAnyPermission(['ai_assistant.use'])" href="/apps/ai-assistant" class="ai-btn">
                        <i class="fa fa-robot"></i>
                        Open AI Assistant
                        <i class="fa fa-arrow-right"></i>
                    </Link>
                    <span v-else class="ai-disabled">AI Assistant requires permission</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card quick-actions-card" data-stagger="5">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <Link href="/apps/items/create" class="quick-action-item">
                        <div class="qa-icon icon-blue">
                            <i class="fa fa-plus"></i>
                        </div>
                        <div class="qa-content">
                            <div class="qa-label">Add Material</div>
                            <div class="qa-desc">Create new inventory item</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>

                    <Link href="/apps/stock-ins/create" class="quick-action-item">
                        <div class="qa-icon icon-green">
                            <i class="fa fa-arrow-down"></i>
                        </div>
                        <div class="qa-content">
                            <div class="qa-label">Stock In</div>
                            <div class="qa-desc">Record incoming materials</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>

                    <Link href="/apps/stock-outs/create" class="quick-action-item">
                        <div class="qa-icon icon-orange">
                            <i class="fa fa-arrow-up"></i>
                        </div>
                        <div class="qa-content">
                            <div class="qa-label">Stock Out</div>
                            <div class="qa-desc">Record outgoing materials</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>

                    <Link href="/apps/stock-movements" class="quick-action-item">
                        <div class="qa-icon icon-purple">
                            <i class="fa fa-file-lines"></i>
                        </div>
                        <div class="qa-content">
                            <div class="qa-label">Reports</div>
                            <div class="qa-desc">View stock movements</div>
                        </div>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </Link>
                </div>
            </div>

        </div>

        <!-- Charts Section -->
        <div class="charts-grid fade-in-stagger">

            <!-- Stock Movement Chart -->
            <div class="card chart-card chart-wide" data-stagger="6">
                <div class="card-header">
                    <h3 class="card-title">Stock Movement (7 Days)</h3>
                    <div class="chart-legend">
                        <span class="legend-item">
                            <span class="legend-dot dot-blue"></span>
                            Stock Out
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <apexchart type="area" height="280" :options="chartMovementOptions" :series="chartMovementSeries" />
                </div>
            </div>

            <!-- Material Category Distribution -->
            <div class="card chart-card" data-stagger="7">
                <div class="card-header">
                    <h3 class="card-title">Stock Status Distribution</h3>
                </div>
                <div class="card-body">
                    <apexchart v-if="hasStockData" type="donut" height="280" :options="chartStatusOptions" :series="stock_status.data" />
                    <div v-else class="empty-state">
                        <i class="fa fa-inbox"></i>
                        <p>No stock data available</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tables Section -->
        <div class="tables-grid fade-in-stagger">

            <!-- Low Stock Alert Table -->
            <div class="card table-card" data-stagger="8">
                <div class="card-header">
                    <h3 class="card-title">Low Stock Alert</h3>
                    <Link href="/apps/items" class="view-all-link">
                        View All
                        <i class="fa fa-arrow-right"></i>
                    </Link>
                </div>
                <div class="card-body">
                    <div v-if="low_stock_items && low_stock_items.length > 0" class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Code</th>
                                    <th>Stock Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in low_stock_items.slice(0, 5)" :key="item.id" class="table-row-fade">
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
                                            {{ item.current_stock <= 0 ? 'Out of Stock' : 'Low Stock' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="empty-state">
                        <i class="fa fa-check-circle"></i>
                        <p>All stock levels are healthy</p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card table-card" data-stagger="9">
                <div class="card-header">
                    <h3 class="card-title">Top Materials Usage</h3>
                    <span class="card-badge">Last 7 Days</span>
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
                            <div class="usage-value">{{ total[idx] }} units</div>
                        </div>
                    </div>
                    <div v-else class="empty-state">
                        <i class="fa fa-chart-bar"></i>
                        <p>No usage data available</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Warehouse Overview -->
        <div class="warehouse-section fade-in-stagger">
            <div class="card warehouse-card" data-stagger="10">
                <div class="card-header">
                    <h3 class="card-title">Warehouse Overview</h3>
                    <Link href="/apps/locations" class="view-all-link">
                        Manage Locations
                        <i class="fa fa-arrow-right"></i>
                    </Link>
                </div>
                <div class="card-body">
                    <div class="warehouse-stats">
                        <div class="warehouse-stat">
                            <div class="stat-icon icon-blue">
                                <i class="fa fa-warehouse"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Total Locations</div>
                                <div class="stat-value">{{ animatedLocations }}</div>
                            </div>
                        </div>
                        <div class="warehouse-stat">
                            <div class="stat-icon icon-green">
                                <i class="fa fa-boxes-stacked"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Active Storage</div>
                                <div class="stat-value">{{ stock_status.data[0] || 0 }}</div>
                            </div>
                        </div>
                        <div class="warehouse-stat">
                            <div class="stat-icon icon-purple">
                                <i class="fa fa-layer-group"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Categories</div>
                                <div class="stat-value">{{ animatedCategories }}</div>
                            </div>
                        </div>
                        <div class="warehouse-stat">
                            <div class="stat-icon icon-orange">
                                <i class="fa fa-gauge-high"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Utilization</div>
                                <div class="stat-value">{{ utilizationPercent }}%</div>
                            </div>
                        </div>
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
        };
    },

    computed: {
        userName() {
            const page = usePage();
            return page.props.auth?.user?.name ?? 'User';
        },
        greeting() {
            const hour = new Date().getHours();
            if (hour < 11) return 'Good Morning';
            if (hour < 15) return 'Good Afternoon';
            if (hour < 18) return 'Good Evening';
            return 'Good Night';
        },
        todayFormatted() {
            return new Date().toLocaleDateString('en-US', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
            });
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
            const active = this.availableStock;
            return Math.round((active / total) * 100);
        },
        chartMovementSeries() {
            return [{ name: 'Stock Out', data: this.grand_total ?? [] }];
        },
        chartMovementOptions() {
            return {
                chart: {
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    }
                },
                colors: ['#6366f1'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                stroke: { curve: 'smooth', width: 2.5 },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: this.sales_date ?? [],
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Inter, sans-serif',
                            colors: '#64748b'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Inter, sans-serif',
                            colors: '#64748b'
                        }
                    }
                },
                grid: {
                    strokeDashArray: 3,
                    borderColor: '#e5e7eb',
                    padding: { top: 0, right: 0, bottom: 0, left: 0 }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: (v) => v + ' units'
                    }
                }
            };
        },
        chartStatusOptions() {
            return {
                chart: {
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                labels: this.stock_status?.labels ?? [],
                colors: ['#10b981', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif',
                    markers: { width: 8, height: 8, radius: 2 },
                    itemMargin: { horizontal: 12, vertical: 8 }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Inter, sans-serif',
                        fontWeight: 600
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Items',
                                    fontSize: '14px',
                                    fontFamily: 'Inter, sans-serif',
                                    color: '#1e293b'
                                }
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
            const pct = (item.current_stock / item.min_stock) * 100;
            return Math.min(Math.max(pct, 4), 100);
        },
        animateCounter(target, key, duration = 1200) {
            const startTime = performance.now();
            const step = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                this[key] = Math.round(target * eased);
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        },
        updateClock() {
            this.liveClock = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        },
        initStaggerAnimation() {
            const elements = document.querySelectorAll('[data-stagger]');
            elements.forEach((el, idx) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, idx * 80);
            });
        }
    },

    mounted() {
        this.animateCounter(this.totalMaterials, 'animatedTotalMaterials');
        this.animateCounter(this.availableStock, 'animatedAvailableStock');
        this.animateCounter(this.low_stock_count ?? 0, 'animatedLowStock');
        this.animateCounter(this.grand_total?.reduce((a, b) => a + b, 0) || 0, 'animatedMonthlyOut');
        this.animateCounter(8, 'animatedLocations', 1000);
        this.animateCounter(12, 'animatedCategories', 1000);

        this.updateClock();
        this.clockInterval = setInterval(this.updateClock, 1000);

        this.$nextTick(() => {
            this.initStaggerAnimation();
        });
    },

    beforeUnmount() {
        if (this.clockInterval) clearInterval(this.clockInterval);
    },
};
</script>

<style scoped>
/* Minimal & Airy Design System */
.dashboard-page {
    background: #fafafa;
    min-height: 100vh;
    padding: 24px;
}

/* Header Section */
.dash-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    gap: 24px;
    flex-wrap: wrap;
}

.header-left {
    flex: 1;
    min-width: 300px;
}

.dash-title {
    font-size: 28px;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 6px;
    letter-spacing: -0.02em;
    font-family: 'Inter', sans-serif;
}

.dash-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0;
    font-weight: 400;
}

.live-time {
    font-variant-numeric: tabular-nums;
    color: #475569;
    font-weight: 500;
}

.header-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    background: white;
    color: #1e293b;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
}

.action-btn:hover {
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    text-decoration: none;
}

.btn-primary {
    background: #6366f1;
    border-color: #6366f1;
    color: white;
}

.btn-primary:hover {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.btn-secondary {
    background: white;
    border-color: #e5e7eb;
    color: #475569;
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.kpi-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.kpi-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}

.kpi-card.kpi-alert {
    background: linear-gradient(to bottom, #fffbeb, white);
}

.kpi-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.kpi-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.kpi-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.icon-blue {
    background: #eff6ff;
    color: #3b82f6;
}

.icon-green {
    background: #f0fdf4;
    color: #10b981;
}

.icon-orange {
    background: #fff7ed;
    color: #f97316;
}

.icon-purple {
    background: #faf5ff;
    color: #a855f7;
}

.icon-red {
    background: #fef2f2;
    color: #ef4444;
}

.kpi-value {
    font-size: 36px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 12px;
    line-height: 1;
    letter-spacing: -0.02em;
}

.kpi-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dot-blue {
    background: #3b82f6;
}

.dot-green {
    background: #10b981;
}

.dot-orange {
    background: #f97316;
}

.dot-purple {
    background: #a855f7;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

/* Card Base */
.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.card-subtitle {
    font-size: 12px;
    color: #94a3b8;
    margin: 2px 0 0;
}

.card-badge {
    font-size: 12px;
    padding: 4px 10px;
    background: #f1f5f9;
    color: #475569;
    border-radius: 6px;
    font-weight: 500;
}

.card-body {
    padding: 20px;
}

.card-footer {
    padding: 16px 20px;
    border-top: 1px solid #f1f5f9;
}

/* AI Insight Card */
.ai-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.insight-item {
    display: flex;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
}

.insight-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.insight-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
    font-size: 16px;
    flex-shrink: 0;
}

.insight-content {
    flex: 1;
}

.insight-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 4px;
}

.insight-value {
    font-size: 14px;
    color: #0f172a;
    font-weight: 500;
}

.ai-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.ai-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(168,85,247,0.3);
    text-decoration: none;
    color: white;
}

.ai-disabled {
    display: block;
    text-align: center;
    font-size: 13px;
    color: #94a3b8;
    padding: 12px;
}

/* Quick Actions */
.quick-action-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    margin-bottom: 8px;
}

.quick-action-item:last-child {
    margin-bottom: 0;
}

.quick-action-item:hover {
    background: #f8fafc;
    text-decoration: none;
}

.qa-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.qa-content {
    flex: 1;
}

.qa-label {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 2px;
}

.qa-desc {
    font-size: 12px;
    color: #64748b;
}

.qa-arrow {
    color: #cbd5e1;
    font-size: 14px;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 32px;
}

.chart-wide {
    grid-column: span 1;
}

.chart-legend {
    display: flex;
    gap: 16px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 2px;
}

/* Tables Grid */
.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.view-all-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6366f1;
    text-decoration: none;
    font-weight: 500;
}

.view-all-link:hover {
    color: #4f46e5;
    text-decoration: none;
}

/* Data Table */
.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead th {
    text-align: left;
    padding: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-bottom: 1px solid #e5e7eb;
}

.data-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #475569;
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.material-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.material-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.material-name {
    font-weight: 500;
    color: #0f172a;
}

.item-code {
    font-size: 12px;
    padding: 2px 8px;
    background: #f1f5f9;
    color: #64748b;
    border-radius: 4px;
}

.stock-progress {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s ease;
}

.fill-red {
    background: linear-gradient(90deg, #f87171, #ef4444);
}

.fill-orange {
    background: linear-gradient(90deg, #fbbf24, #f97316);
}

.stock-text {
    font-size: 11px;
    color: #94a3b8;
    font-variant-numeric: tabular-nums;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.badge-red {
    background: #fef2f2;
    color: #dc2626;
}

.badge-orange {
    background: #fff7ed;
    color: #ea580c;
}

/* Usage List */
.usage-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.usage-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.usage-rank {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    flex-shrink: 0;
}

.usage-info {
    flex: 1;
}

.usage-name {
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    margin-bottom: 6px;
}

.usage-bar {
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    border-radius: 3px;
    transition: width 0.6s ease;
}

.usage-value {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    font-variant-numeric: tabular-nums;
}

/* Warehouse Section */
.warehouse-section {
    margin-bottom: 32px;
}

.warehouse-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.warehouse-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 40px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Animations */
.fade-in-stagger [data-stagger] {
    opacity: 0;
    transform: translateY(20px);
}

.table-row-fade {
    animation: fadeInUp 0.5s ease forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-page {
        padding: 16px;
    }

    .dash-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .action-btn {
        flex: 1;
        justify-content: center;
    }

    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .content-grid,
    .tables-grid {
        grid-template-columns: 1fr;
    }

    .warehouse-stats {
        grid-template-columns: 1fr;
    }
}
</style>
