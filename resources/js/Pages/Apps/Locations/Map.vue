<template>
    <Head><title>Peta Gudang - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-map-location-dot me-1"></i> {{ $t('locations.map_heading') }}</span>
                            <Link v-if="hasAnyPermission(['locations.index'])" href="/apps/locations" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-list me-1"></i> {{ $t('locations.map_legend') }}
                            </Link>
                        </div>
                        <div class="card-body">
                            <!-- Legend -->
                            <div class="d-flex flex-wrap gap-3 mb-4 p-3 rounded-3" style="background: #f1f5f9;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width: 14px; height: 14px; background: #16A34A;"></span>
                                    <small class="fw-semibold">{{ $t('locations.map_active') }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width: 14px; height: 14px; background: #F59E0B;"></span>
                                    <small class="fw-semibold">{{ $t('locations.map_low_stock') }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width: 14px; height: 14px; background: #94A3B8;"></span>
                                    <small class="fw-semibold">{{ $t('locations.map_empty') }}</small>
                                </div>
                                <div class="ms-auto">
                                    <small class="text-muted">
                                        <i class="fa fa-cubes me-1"></i> {{ $t('locations.map_total_locations') }} <strong>{{ locations.length }}</strong>
                                        <span class="mx-2">|</span>
                                        <i class="fa fa-triangle-exclamation me-1" style="color: #F59E0B;"></i> {{ $t('locations.map_total_low_stock') }} <strong>{{ low_stock_at_location }}</strong>
                                    </small>
                                </div>
                            </div>

                            <!-- Location Cards Grid -->
                            <div v-if="locations.length === 0" class="text-center py-5">
                                <i class="fa fa-map-location-dot fa-3x mb-3" style="color: #CBD5E1;"></i>
                                <p class="text-muted">{{ $t('locations.empty') }}</p>
                            </div>
                            <div v-else class="row g-3">
                                <div v-for="loc in locations" :key="loc.id" class="col-12 col-md-6 col-lg-4">
                                    <Link
                                        :href="`/apps/locations?location_id=${loc.id}`"
                                        class="text-decoration-none"
                                    >
                                        <div
                                            class="card h-100 border-0 rounded-3 shadow-sm location-card"
                                            :style="cardStyle(loc)"
                                        >
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start justify-content-between mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div
                                                            class="rounded-2 d-flex align-items-center justify-content-center"
                                                            :style="iconBoxStyle(loc)"
                                                        >
                                                            <i class="fa fa-warehouse"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold" style="color: #0F172A;">{{ loc.code || '-' }}</h6>
                                                            <small class="text-muted">{{ loc.name }}</small>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="badge rounded-pill px-2 py-1"
                                                        :style="badgeStyle(loc)"
                                                    >
                                                        {{ statusLabel(loc) }}
                                                    </span>
                                                </div>

                                                <p v-if="loc.address" class="mb-3 small" style="color: #64748B;">
                                                    <i class="fa fa-location-dot me-1"></i> {{ loc.address }}
                                                </p>
                                                <p v-else class="mb-3 small text-muted">
                                                    <i class="fa fa-location-dot me-1"></i> <em>{{ $t('locations.map_no_address') }}</em>
                                                </p>

                                                <div class="d-flex gap-3 pt-2 border-top">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <i class="fa fa-cubes small" style="color: #7C3AED;"></i>
                                                        <small class="fw-semibold" style="color: #334155;">
                                                            {{ loc.item_count ?? 0 }} {{ $t('locations.map_items') }}
                                                        </small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <i class="fa fa-boxes-stacked small" style="color: #7C3AED;"></i>
                                                        <small class="fw-semibold" style="color: #334155;">
                                                            {{ formatNumber(loc.total_stock) }} {{ $t('locations.map_stock') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
import LayoutApp from '../../../Layouts/App.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        locations: {
            type: Array,
            default: () => [],
        },
        low_stock_at_location: {
            type: Number,
            default: 0,
        },
    },
    setup() {
        const formatNumber = (num) => {
            if (!num || num === null) return '0';
            return Number(num).toLocaleString('id-ID');
        };

        const hasLowStock = (loc) => {
            const count = loc.item_count ?? 0;
            if (count === 0) return false;
            const lowItems = loc.low_items_count ?? 0;
            return lowItems > 0;
        };

        const getStatus = (loc) => {
            const count = loc.item_count ?? 0;
            if (count === 0) return 'empty';
            return 'active';
        };

        const statusLabel = (loc) => {
            const status = getStatus(loc);
            if (status === 'empty') return 'Kosong';
            return 'Aktif';
        };

        const cardStyle = (loc) => {
            const status = getStatus(loc);
            if (status === 'empty') {
                return { borderLeft: '4px solid #94A3B8', background: '#FAFBFC' };
            }
            return { borderLeft: '4px solid #16A34A', background: '#FFFFFF' };
        };

        const iconBoxStyle = (loc) => {
            const status = getStatus(loc);
            if (status === 'empty') {
                return { background: '#F1F5F9', color: '#94A3B8', width: '36px', height: '36px', fontSize: '14px' };
            }
            return { background: '#F0FDF4', color: '#16A34A', width: '36px', height: '36px', fontSize: '14px' };
        };

        const badgeStyle = (loc) => {
            const status = getStatus(loc);
            if (status === 'empty') {
                return { background: '#F1F5F9', color: '#64748B' };
            }
            return { background: '#DCFCE7', color: '#15803D' };
        };

        return { formatNumber, statusLabel, cardStyle, iconBoxStyle, badgeStyle };
    },
};
</script>

<style scoped>
.location-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}
.location-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
}
</style>
