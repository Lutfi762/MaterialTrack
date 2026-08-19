<template>
    <Head><title>{{ $t('forecasting.title') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="fw-bold"><i class="fa fa-chart-line me-2 text-primary"></i>{{ $t('forecasting.heading') }}</h4>
                        <p class="text-muted small mb-0">{{ $t('forecasting.subtitle') }}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-danger me-2"><i class="fa fa-exclamation-triangle me-1"></i> {{ urgentCount }} {{ $t('forecasting.urgent') }}</span>
                        <span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i> {{ warningCount }} {{ $t('forecasting.warning') }}</span>
                    </div>
                </div>

                <div v-if="items.length === 0" class="text-center py-5">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-success">{{ $t('forecasting.all_healthy') }}</h5>
                    <p class="text-muted">{{ $t('forecasting.all_healthy_desc') }}</p>
                </div>

                <div class="row g-3">
                    <div v-for="item in items" :key="item.id" class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-xl h-100 forecast-card" :class="{
                            'border-start border-4 border-danger': item.status === 'urgent',
                            'border-start border-4 border-warning': item.status === 'warning',
                            'border-start border-4 border-success': item.status === 'ok',
                        }">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="card-title fw-bold mb-0">{{ item.name }}</h6>
                                        <small class="text-muted">{{ item.code }}</small>
                                    </div>
                                    <span class="badge" :class="{
                                        'bg-danger': item.status === 'urgent',
                                        'bg-warning text-dark': item.status === 'warning',
                                        'bg-success': item.status === 'ok',
                                    }">{{ $t('forecasting.status_' + item.status) }}</span>
                                </div>

                                <hr class="my-2">

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="forecast-stat">
                                            <span class="forecast-stat-label">{{ $t('forecasting.current_stock') }}</span>
                                            <span class="forecast-stat-value">{{ item.current_stock }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="forecast-stat">
                                            <span class="forecast-stat-label">{{ $t('forecasting.min_stock') }}</span>
                                            <span class="forecast-stat-value">{{ item.min_stock }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="forecast-stat">
                                            <span class="forecast-stat-label">{{ $t('forecasting.daily_avg') }}</span>
                                            <span class="forecast-stat-value text-info">{{ item.daily_avg_usage }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="forecast-stat">
                                            <span class="forecast-stat-label">{{ $t('forecasting.days_until_empty') }}</span>
                                            <span class="forecast-stat-value" :class="{
                                                'text-danger fw-bold': item.status === 'urgent',
                                                'text-warning': item.status === 'warning',
                                                'text-success': item.status === 'ok',
                                            }">{{ item.days_until_empty !== null ? item.days_until_empty + ' ' + $t('forecasting.days') : '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">{{ $t('forecasting.suggested_restock') }}:</small>
                                        <span class="fw-bold text-primary ms-1">{{ Math.ceil(item.suggested_restock) }} unit</span>
                                    </div>
                                    <Link :href="'/apps/items/' + item.id" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fa fa-eye"></i>
                                    </Link>
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
import { Head, Link } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        items: Array,
    },
    computed: {
        urgentCount() {
            return this.items.filter(i => i.status === 'urgent').length;
        },
        warningCount() {
            return this.items.filter(i => i.status === 'warning').length;
        },
    },
};
</script>

<style scoped>
.forecast-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.forecast-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.forecast-stat {
    text-align: center;
    padding: 6px 0;
}
.forecast-stat-label {
    display: block;
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.forecast-stat-value {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}
</style>
