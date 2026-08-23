<template>
  <Head><title>Peta Gudang - MaterialTrack</title></Head>

  <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
    <div class="mx-auto max-w-7xl">
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Warehouse Setup</p>
          <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface"><i class="fa fa-map-location-dot text-primary" aria-hidden="true"></i>{{ $t('locations.map_heading') }}</h2>
        </div>
        <Link v-if="hasAnyPermission(['locations.index'])" href="/apps/locations" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg hover:text-on-surface">
          <i class="fa fa-list" aria-hidden="true"></i>{{ $t('locations.map_legend') }}
        </Link>
      </div>

      <section class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
        <div class="flex flex-wrap items-center gap-4 border-b border-outline/20 bg-bg/50 p-4 text-sm text-on-surface-variant">
          <div class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-success"></span>{{ $t('locations.map_active') }}</div>
          <div class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-warning"></span>{{ $t('locations.map_low_stock') }}</div>
          <div class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-outline"></span>{{ $t('locations.map_empty') }}</div>
          <div class="ml-auto flex flex-wrap items-center gap-3 text-xs sm:text-sm">
            <span><i class="fa fa-cubes mr-1 text-primary" aria-hidden="true"></i>{{ $t('locations.map_total_locations') }} <strong class="text-on-surface">{{ locations.length }}</strong></span>
            <span><i class="fa fa-triangle-exclamation mr-1 text-warning" aria-hidden="true"></i>{{ $t('locations.map_total_low_stock') }} <strong class="text-on-surface">{{ low_stock_at_location }}</strong></span>
          </div>
        </div>

        <div v-if="locations.length === 0" class="py-16 text-center"><i class="fa fa-map-location-dot mb-3 text-4xl text-outline" aria-hidden="true"></i><p class="text-sm text-on-surface-variant">{{ $t('locations.empty') }}</p></div>

        <div v-else class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
          <Link v-for="loc in locations" :key="loc.id" :href="`/apps/locations?location_id=${loc.id}`" class="group block">
            <article class="h-full rounded-xl border border-outline/20 bg-surface p-4 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md">
              <div class="mb-4 flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" :class="getStatus(loc) === 'empty' ? 'bg-bg text-outline' : 'bg-success/10 text-success'"><i class="fa fa-warehouse" aria-hidden="true"></i></div>
                  <div class="min-w-0"><h3 class="truncate font-mono text-sm font-semibold text-on-surface group-hover:text-primary">{{ loc.code || '-' }}</h3><p class="truncate text-sm text-on-surface-variant">{{ loc.name }}</p></div>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium" :class="getStatus(loc) === 'empty' ? 'bg-bg text-on-surface-variant' : 'bg-success/10 text-success'"><span class="h-1.5 w-1.5 rounded-full" :class="getStatus(loc) === 'empty' ? 'bg-outline' : 'bg-success'"></span>{{ statusLabel(loc) }}</span>
              </div>
              <div class="min-h-12 space-y-1.5 text-sm text-on-surface-variant">
                <p v-if="loc.warehouse_zone" class="truncate"><i class="fa fa-tag mr-1.5 text-outline" aria-hidden="true"></i>{{ $t('locations.warehouse_zone') }}: {{ loc.warehouse_zone }}</p>
                <p v-if="loc.address" class="truncate"><i class="fa fa-location-dot mr-1.5 text-outline" aria-hidden="true"></i>{{ loc.address }}</p>
                <p v-else><i class="fa fa-location-dot mr-1.5 text-outline" aria-hidden="true"></i><em>{{ $t('locations.map_no_address') }}</em></p>
              </div>
              <div class="mt-4 flex items-center gap-4 border-t border-outline/20 pt-3 text-xs font-semibold text-on-surface-variant"><span><i class="fa fa-cubes mr-1.5 text-primary" aria-hidden="true"></i>{{ loc.item_count ?? 0 }} {{ $t('locations.map_items') }}</span><span><i class="fa fa-boxes-stacked mr-1.5 text-primary" aria-hidden="true"></i>{{ formatNumber(loc.total_stock) }} {{ $t('locations.map_stock') }}</span></div>
            </article>
          </Link>
        </div>
      </section>
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
    locations: { type: Array, default: () => [] },
    low_stock_at_location: { type: Number, default: 0 },
  },
  setup() {
    const formatNumber = (num) => !num || num === null ? '0' : Number(num).toLocaleString('id-ID');
    const getStatus = (loc) => (loc.item_count ?? 0) === 0 ? 'empty' : 'active';
    const statusLabel = (loc) => getStatus(loc) === 'empty' ? 'Kosong' : 'Aktif';

    return { formatNumber, getStatus, statusLabel };
  },
};
</script>