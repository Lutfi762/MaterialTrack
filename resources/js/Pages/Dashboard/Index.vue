<template>
    <Head><title>Dashboard - MaterialTrack</title></Head>

    <div class="dash-bg">
        <div class="dash-wrap">

            <!-- Hero header -->
            <div class="hero-row">
                <div>
                    <h1 class="hero-title">{{ greeting }}, {{ userName }} 👋</h1>
                    <p class="hero-sub">{{ todayFormatted }} &middot; <span class="live-clock">{{ liveClock }}</span></p>
                </div>
                <div class="hero-actions">
                    <Link href="/apps/stock-outs/create" class="pill-btn pill-danger">
                        <i class="fa fa-arrow-up-right-from-square"></i> Stok Keluar
                    </Link>
                    <Link href="/apps/stock-ins/create" class="pill-btn pill-success">
                        <i class="fa fa-arrow-down-left"></i> Stok Masuk
                    </Link>
                    <Link href="/apps/items" class="pill-btn pill-primary">
                        Lihat Material <i class="fa fa-arrow-right"></i>
                    </Link>
                </div>
            </div>

            <!-- Bento grid -->
            <div class="bento-grid">

                <!-- KPI cards -->
                <div class="glass kpi-card kpi-blue">
                    <div class="kpi-glow"></div>
                    <div class="kpi-top">
                        <div>
                            <p class="kpi-label">Material Keluar Hari Ini</p>
                            <h2 class="kpi-value">{{ animatedOut }}<span class="kpi-unit">unit</span></h2>
                        </div>
                        <div class="kpi-icon icon-blue"><i class="fa fa-box-open"></i></div>
                    </div>
                    <div class="kpi-foot foot-blue">
                        <i class="fa fa-receipt"></i> {{ material_out_today?.total_transaction ?? 0 }} Transaksi
                    </div>
                </div>

                <div class="glass kpi-card kpi-green">
                    <div class="kpi-glow glow-green"></div>
                    <div class="kpi-top">
                        <div>
                            <p class="kpi-label">Material Masuk Hari Ini</p>
                            <h2 class="kpi-value">{{ animatedIn }}<span class="kpi-unit">unit</span></h2>
                        </div>
                        <div class="kpi-icon icon-green"><i class="fa fa-truck-ramp-box"></i></div>
                    </div>
                    <div class="kpi-foot foot-green">
                        <i class="fa fa-truck"></i> {{ material_in_today?.total_transaction ?? 0 }} Aktivitas Restock
                    </div>
                </div>

                <div class="glass kpi-card kpi-red" :class="{ 'kpi-safe': low_stock_count === 0 }">
                    <div class="kpi-glow glow-red"></div>
                    <div class="kpi-top">
                        <div>
                            <p class="kpi-label" :class="low_stock_count > 0 ? 'text-danger' : ''">Monitoring Stok Menipis</p>
                            <h2 class="kpi-value">{{ animatedLow }}<span class="kpi-unit">item</span></h2>
                        </div>
                        <div class="kpi-icon" :class="low_stock_count > 0 ? 'icon-red' : 'icon-green'">
                            <i class="fa" :class="low_stock_count > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check'"></i>
                        </div>
                    </div>
                    <div class="kpi-foot" :class="low_stock_count > 0 ? 'foot-red' : 'foot-green'">
                        <span v-if="low_stock_count > 0" class="pulse-dot"></span>
                        {{ low_stock_count > 0 ? 'Membutuhkan perhatian segera' : 'Semua stok aman' }}
                    </div>
                </div>

                <!-- Line chart -->
                <div class="glass chart-panel span-8">
                    <div class="panel-head">
                        <h3>Pengeluaran 7 Hari Terakhir</h3>
                    </div>
                    <apexchart type="area" height="260" :options="chartOutOptions" :series="chartOutSeries" />
                </div>

                <!-- Donut chart -->
                <div class="glass chart-panel span-4">
                    <div class="panel-head">
                        <h3>Status Stok Material</h3>
                    </div>
                    <apexchart v-if="hasStockData" type="donut" height="260" :options="chartStatusOptions" :series="stock_status.data" />
                    <div v-else class="empty-mini">
                        <span>✅</span> Belum ada data material
                    </div>
                </div>

                <!-- Top 5 bar chart -->
                <div class="glass chart-panel span-6">
                    <div class="panel-head">
                        <h3>Top 5 Material Terbanyak Dipakai</h3>
                    </div>
                    <apexchart v-if="hasTopData" type="bar" height="220" :options="chartTopOptions" :series="chartTopSeries" />
                    <div v-else class="empty-mini">
                        <span>📊</span> Belum ada data pemakaian
                    </div>
                </div>

                <!-- Stock limit list -->
                <div class="glass chart-panel span-6">
                    <div class="panel-head panel-head-row">
                        <h3>Material Stock Limit</h3>
                        <Link href="/apps/items" class="link-more">Lihat Semua <i class="fa fa-arrow-right"></i></Link>
                    </div>
                    <div v-if="low_stock_items && low_stock_items.length > 0" class="stock-list">
                        <div v-for="item in low_stock_items" :key="item.id" class="stock-row">
                            <div class="stock-info">
                                <div class="stock-icon" :class="item.current_stock <= 0 ? 'icon-red' : 'icon-orange'">
                                    <i class="fa fa-box"></i>
                                </div>
                                <div>
                                    <div class="stock-name">{{ item.name }}</div>
                                    <code class="stock-code">{{ item.code }}</code>
                                </div>
                            </div>
                            <div class="stock-bar-wrap">
                                <div class="stock-track">
                                    <div class="stock-fill" :class="item.current_stock <= 0 ? 'fill-red' : 'fill-orange'"
                                         :style="{ width: stockPercent(item) + '%' }"></div>
                                </div>
                                <div class="stock-text">{{ item.current_stock }} / {{ item.min_stock }} {{ item.unit }}</div>
                            </div>
                            <span class="mini-badge" :class="item.current_stock <= 0 ? 'badge-red' : 'badge-orange'">
                                {{ item.current_stock <= 0 ? 'Habis' : 'Menipis' }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="empty-mini">
                        <span>✅</span> Semua stok material dalam kondisi aman
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
        total:               Array,
        stock_status:        Object,
        low_stock_items:     Array,
        low_stock_count:     Number,
    },

    data() {
        return {
            animatedOut: 0,
            animatedIn: 0,
            animatedLow: 0,
            liveClock: '',
            clockInterval: null,
        };
    },

    computed: {
        userName() {
            const page = usePage();
            return page.props.auth?.user?.name ?? 'Admin';
        },
        greeting() {
            const hour = new Date().getHours();
            if (hour < 11) return 'Selamat Pagi';
            if (hour < 15) return 'Selamat Siang';
            if (hour < 18) return 'Selamat Sore';
            return 'Selamat Malam';
        },
        todayFormatted() {
            return new Date().toLocaleDateString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
            });
        },
        hasStockData() {
            return this.stock_status?.data?.some(v => v > 0);
        },
        hasTopData() {
            return this.total?.length > 0 && this.total.some(v => v > 0);
        },
        chartOutSeries() {
            return [{ name: 'Material Keluar', data: this.grand_total ?? [] }];
        },
        chartOutOptions() {
            return {
                chart: { toolbar: { show: false }, zoom: { enabled: false }, background: 'transparent' },
                colors: ['#4648d4'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.02, stops: [0, 100] },
                },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                xaxis: { categories: this.sales_date ?? [], labels: { style: { fontSize: '11px' } } },
                grid: { strokeDashArray: 3, borderColor: 'rgba(0,0,0,0.05)' },
                tooltip: { theme: 'light', y: { formatter: (v) => v + ' unit' } },
            };
        },
        chartTopSeries() {
            return [{ name: 'Qty Dipakai', data: this.total ?? [] }];
        },
        chartTopOptions() {
            return {
                chart: { toolbar: { show: false }, background: 'transparent' },
                colors: ['#a36700'],
                plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '55%' } },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600 } },
                xaxis: { categories: this.product ?? [] },
                grid: { strokeDashArray: 3, borderColor: 'rgba(0,0,0,0.05)' },
                tooltip: { theme: 'light' },
            };
        },
        chartStatusOptions() {
            return {
                chart: { toolbar: { show: false }, background: 'transparent' },
                labels: this.stock_status?.labels ?? [],
                colors: ['#4648d4', '#006c49', '#ba1a1a'],
                legend: { position: 'bottom', fontSize: '12px', markers: { size: 8 } },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700 } },
                plotOptions: {
                    pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total Item', fontSize: '12px' } } } },
                },
                tooltip: { theme: 'light' },
            };
        },
    },

    methods: {
        stockPercent(item) {
            if (!item.min_stock || item.min_stock === 0) return item.current_stock > 0 ? 100 : 0;
            const pct = (item.current_stock / item.min_stock) * 100;
            return Math.min(Math.max(pct, 4), 100);
        },
        animateCounter(target, key) {
            const duration = 900;
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
            this.liveClock = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', second: '2-digit',
            });
        },
    },

    mounted() {
        this.animateCounter(this.material_out_today?.total_qty ?? 0, 'animatedOut');
        this.animateCounter(this.material_in_today?.total_qty ?? 0, 'animatedIn');
        this.animateCounter(this.low_stock_count ?? 0, 'animatedLow');
        this.updateClock();
        this.clockInterval = setInterval(this.updateClock, 1000);
    },

    beforeUnmount() {
        if (this.clockInterval) clearInterval(this.clockInterval);
    },
}
</script>

<style scoped>
.dash-bg {
    min-height: 100vh;
    background: linear-gradient(160deg, #f2f3ff 0%, #eaedff 40%, #f8f7ff 100%);
    padding: 4px 0 32px;
}
.dash-wrap { max-width: 1400px; margin: 0 auto; padding: 8px 20px; }

.hero-row { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 28px; }
.hero-title { font-size: 30px; font-weight: 700; color: #131b2e; margin: 0 0 4px; letter-spacing: -0.5px; }
.hero-sub { color: #464554; font-size: 15px; margin: 0; }
.live-clock { font-variant-numeric: tabular-nums; font-weight: 600; color: #4648d4; }

.hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.pill-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: 12px; font-size: 13px; font-weight: 600;
    text-decoration: none; border: 1px solid transparent; transition: all 0.25s ease;
    backdrop-filter: blur(8px);
}
.pill-btn:hover { transform: translateY(-2px); text-decoration: none; }
.pill-danger { background: rgba(186,26,26,0.08); color: #ba1a1a; border-color: rgba(186,26,26,0.15); }
.pill-success { background: rgba(0,108,73,0.08); color: #006c49; border-color: rgba(0,108,73,0.15); }
.pill-primary { background: linear-gradient(90deg, #4648d4, #494bd6); color: white; box-shadow: 0 6px 16px rgba(70,72,212,0.3); }
.pill-primary:hover { color: white; }

.bento-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 24px; }

.glass {
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 18px;
    box-shadow: 0 20px 40px rgba(15,23,42,0.07), inset 0 0 0 1px rgba(255,255,255,0.6);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.glass:hover { transform: translateY(-3px); box-shadow: 0 28px 50px rgba(15,23,42,0.1), inset 0 0 0 1px rgba(255,255,255,0.8); }

.kpi-card { grid-column: span 4; padding: 24px; position: relative; overflow: hidden; }
.kpi-glow { position: absolute; right: -24px; top: -24px; width: 100px; height: 100px; border-radius: 50%; background: rgba(70,72,212,0.12); filter: blur(20px); }
.glow-green { background: rgba(0,108,73,0.12); }
.glow-red { background: rgba(186,26,26,0.12); }

.kpi-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
.kpi-label { font-size: 11px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; color: #464554; margin: 0 0 6px; }
.kpi-value { font-size: 34px; font-weight: 700; color: #131b2e; margin: 0; line-height: 1.1; }
.kpi-unit { font-size: 15px; font-weight: 500; color: #767586; margin-left: 6px; }
.kpi-icon { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
.icon-blue { background: rgba(70,72,212,0.15); color: #4648d4; }
.icon-green { background: rgba(0,108,73,0.15); color: #006c49; }
.icon-red { background: rgba(186,26,26,0.15); color: #ba1a1a; }
.icon-orange { background: rgba(163,103,0,0.15); color: #a36700; }

.kpi-foot { display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; }
.foot-blue { color: #4648d4; }
.foot-green { color: #006c49; }
.foot-red { color: #ba1a1a; }

.pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: #ba1a1a; display: inline-block; animation: pulse 1.4s infinite; }
@keyframes pulse { 0% { opacity: 1; transform: scale(1);} 50% { opacity: 0.4; transform: scale(1.5);} 100% { opacity: 1; transform: scale(1);} }

.chart-panel { padding: 22px; }
.span-4 { grid-column: span 4; }
.span-6 { grid-column: span 6; }
.span-8 { grid-column: span 8; }

.panel-head { border-bottom: 1px solid rgba(0,0,0,0.06); padding-bottom: 10px; margin-bottom: 16px; }
.panel-head h3 { font-size: 16px; font-weight: 600; color: #131b2e; margin: 0; }
.panel-head-row { display: flex; justify-content: space-between; align-items: center; }
.link-more { font-size: 12.5px; color: #4648d4; text-decoration: none; font-weight: 600; }
.link-more:hover { text-decoration: underline; color: #4648d4; }

.empty-mini { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px; color: #767586; font-size: 13px; gap: 8px; }
.empty-mini span { font-size: 30px; }

.stock-list { display: flex; flex-direction: column; gap: 4px; max-height: 300px; overflow-y: auto; }
.stock-row { display: flex; align-items: center; gap: 14px; padding: 12px 4px; border-bottom: 1px solid rgba(0,0,0,0.05); }
.stock-row:last-child { border-bottom: none; }
.stock-info { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 160px; }
.stock-icon { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stock-name { font-weight: 600; font-size: 13.5px; color: #131b2e; }
.stock-code { font-size: 10.5px; color: #767586; }
.stock-bar-wrap { flex: 1.4; min-width: 140px; }
.stock-track { height: 7px; background: rgba(0,0,0,0.06); border-radius: 20px; overflow: hidden; }
.stock-fill { height: 100%; border-radius: 20px; transition: width 0.6s ease; }
.fill-red { background: linear-gradient(90deg, #f87171, #ba1a1a); }
.fill-orange { background: linear-gradient(90deg, #fbbf24, #a36700); }
.stock-text { font-size: 10.5px; color: #767586; margin-top: 4px; }
.mini-badge { font-size: 10.5px; font-weight: 700; padding: 4px 11px; border-radius: 20px; flex-shrink: 0; }
.badge-red { background: rgba(186,26,26,0.12); color: #ba1a1a; }
.badge-orange { background: rgba(163,103,0,0.12); color: #a36700; }

@media (max-width: 1100px) {
    .kpi-card, .span-4, .span-6, .span-8 { grid-column: span 12; }
}
</style>