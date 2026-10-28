<template>
    <Head><title>{{ $t('forecasting.title') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-wrap justify-between items-start gap-3 mb-5">
                <div>
                    <h1 class="text-lg font-bold text-gray-900 m-0"><i class="fa fa-chart-line mr-2 text-indigo-600" aria-hidden="true"></i>{{ $t('forecasting.heading') }}</h1>
                    <p class="text-sm text-gray-400 mb-0">{{ $t('forecasting.subtitle') }}</p>
                </div>
                <div class="flex gap-2">
                    <span class="mtx-badge mtx-badge-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ urgentCount }} {{ $t('forecasting.urgent') }}</span>
                    <span class="mtx-badge mtx-badge-warning"><i class="fa fa-clock" aria-hidden="true"></i> {{ warningCount }} {{ $t('forecasting.warning') }}</span>
                </div>
            </div>

            <div v-if="items.length === 0" class="text-center py-12">
                <i class="fa fa-check-circle text-5xl text-green-500 mb-3" aria-hidden="true"></i>
                <h5 class="text-lg font-semibold text-green-600">{{ $t('forecasting.all_healthy') }}</h5>
                <p class="text-sm text-gray-400">{{ $t('forecasting.all_healthy_desc') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="item in items" :key="item.id" class="mtx-card h-full border-l-4 forecast-card" :class="{
                    'border-l-red-500': item.status === 'urgent',
                    'border-l-amber-500': item.status === 'warning',
                    'border-l-green-500': item.status === 'ok',
                }">
                    <div class="mtx-card-body">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h6 class="text-sm font-semibold text-gray-900 mb-0">{{ item.name }}</h6>
                                <small class="text-xs text-gray-400">{{ item.code }}</small>
                            </div>
                            <span class="mtx-badge" :class="{
                                'mtx-badge-danger': item.status === 'urgent',
                                'mtx-badge-warning': item.status === 'warning',
                                'mtx-badge-success': item.status === 'ok',
                            }">{{ $t('forecasting.status_' + item.status) }}</span>
                        </div>

                        <hr class="my-2 border-gray-100">

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="forecast-stat">
                                <span class="forecast-stat-label">{{ $t('forecasting.current_stock') }}</span>
                                <span class="forecast-stat-value">{{ item.current_stock }}</span>
                            </div>
                            <div class="forecast-stat">
                                <span class="forecast-stat-label">{{ $t('forecasting.min_stock') }}</span>
                                <span class="forecast-stat-value">{{ item.min_stock }}</span>
                            </div>
                            <div class="forecast-stat">
                                <span class="forecast-stat-label">{{ $t('forecasting.daily_avg') }}</span>
                                <span class="forecast-stat-value text-sky-600">{{ item.daily_avg_usage }}</span>
                            </div>
                            <div class="forecast-stat">
                                <span class="forecast-stat-label">{{ $t('forecasting.days_until_empty') }}</span>
                                <span class="forecast-stat-value" :class="{
                                    'text-red-600 font-bold': item.status === 'urgent',
                                    'text-amber-500': item.status === 'warning',
                                    'text-green-600': item.status === 'ok',
                                }">{{ item.days_until_empty !== null ? item.days_until_empty + ' ' + $t('forecasting.days') : '-' }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <div>
                                <small class="text-xs text-gray-400">{{ $t('forecasting.suggested_restock') }}:</small>
                                <span class="text-sm font-bold text-indigo-600 ml-1">{{ Math.ceil(item.suggested_restock) }} unit</span>
                            </div>
                            <Link :href="'/apps/items/' + item.id" class="mtx-btn mtx-btn-secondary mtx-btn-sm !rounded-full">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </Link>
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
