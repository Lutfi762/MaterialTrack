<template>
    <main class="min-h-full bg-bg p-4 text-on-surface sm:p-6">
      <div class="mx-auto max-w-7xl">
        <Head :title="$t('stock_reservation.heading')" />
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-on-surface">{{ $t('stock_reservation.heading') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('stock_reservation.heading') }]" />
            </div>
            <Link v-if="hasAnyPermission(['stock_reservations.create'])" href="/apps/stock-reservations/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm dark:text-[#1000a9]">
                <i class="fa fa-plus me-1"></i> {{ $t('stock_reservation.add') }}
            </Link>
        </div>

        <!-- Filters -->
        <form @submit.prevent="applyFilter" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-outline/20 bg-surface p-4 shadow-sm">
                    <div class="min-w-44 flex-1">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $t('common.status') }}</label>
                        <select class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="filterForm.status">
                            <option value="">{{ $t('common.all') }}</option>
                            <option value="active">{{ $t('stock_reservation.status_active') }}</option>
                            <option value="fulfilled">{{ $t('stock_reservation.status_fulfilled') }}</option>
                            <option value="cancelled">{{ $t('stock_reservation.status_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="min-w-56 flex-[2]">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $t('common.item') }}</label>
                        <select class="w-full rounded-lg border border-outline/30 bg-surface px-3 py-2.5 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" v-model="filterForm.item_id">
                            <option value="">{{ $t('common.all') }}</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">[{{ item.code }}] {{ item.name }}</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white dark:text-[#1000a9]">{{ $t('common.filter') }}</button>
                    <button type="button" class="rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-bg" @click="resetFilter">{{ $t('common.reset') }}</button>
        </form>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm"><div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-outline/20 text-sm">
                        <thead class="bg-bg text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 text-left">{{ $t('stock_reservation.col_date') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('stock_reservation.col_material') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('stock_reservation.col_qty') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('stock_reservation.col_project') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('stock_reservation.col_dept') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('stock_reservation.col_by') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('stock_reservation.col_until') }}</th>
                                <th class="px-4 py-3 text-left">{{ $t('common.status') }}</th>
                                <th class="px-4 py-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline/20 text-on-surface">
                            <tr v-for="r in reservations.data" :key="r.id" class="transition-colors hover:bg-primary/5">
                                <td class="whitespace-nowrap px-4 py-3">{{ formatDate(r.created_at) }}</td>
                                <td class="px-4 py-3"><div class="font-semibold">{{ r.item?.name }}</div><small class="text-on-surface-variant">{{ r.item?.code }}</small>
                                </td>
                                <td class="px-4 py-3 font-bold">{{ r.qty }}</td><td class="px-4 py-3">{{ r.project_name || '-' }}</td><td class="px-4 py-3">{{ r.department?.name || '-' }}</td><td class="px-4 py-3">{{ r.user?.name }}</td><td class="whitespace-nowrap px-4 py-3">{{ r.reserved_until ? formatDate(r.reserved_until) : '-' }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusBadge(r.status)">{{ $t('stock_reservation.status_' + r.status) }}</span>
                                </td>
                                <td class="px-4 py-3"><div class="flex justify-center gap-1">
                                        <Link v-if="r.status === 'active'" :href="`/apps/stock-reservations/${r.id}`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-success hover:bg-success/10" :title="$t('stock_reservation.fulfill')">
                                            <i class="fa fa-check"></i>
                                        </Link>
                                        <button v-if="r.status === 'active'" @click="cancelReservation(r)" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-warning hover:bg-warning/10" :title="$t('stock_reservation.cancel_btn')">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                        <Link :href="`/apps/stock-reservations/${r.id}`" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-primary hover:bg-primary/10" :title="$t('common.info')">
                                            <i class="fa fa-eye"></i>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="reservations.data.length === 0">
                                <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant"><i class="fa fa-bookmark mb-2 block text-2xl"></i>
                                    {{ $t('stock_reservation.empty') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div></div>

        <!-- Pagination -->
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm text-on-surface-variant">
            <div>
                {{ $t('common.showing') }} {{ reservations.from || 0 }} - {{ reservations.to || 0 }} {{ $t('common.of') }} {{ reservations.total }}
            </div>
            <Pagination v-if="reservations.last_page > 1" :links="reservations.links" />
        </div>
      </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Pagination from '@/Components/Pagination.vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Breadcrumb, Pagination },
    props: {
        reservations: Object,
        items: Array,
    },
    data() {
        return {
            filterForm: {
                status: '',
                item_id: '',
            },
        };
    },
    methods: {
        formatDate(d) {
            return new Date(d).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
        },
        statusBadge(s) {
            return { active: 'bg-warning/15 text-warning', fulfilled: 'bg-success/15 text-success', cancelled: 'bg-bg text-on-surface-variant' }[s] || 'bg-bg text-on-surface-variant';
        },
        applyFilter() {
            router.get('/apps/stock-reservations', this.filterForm, { preserveState: true });
        },
        resetFilter() {
            this.filterForm = { status: '', item_id: '' };
            this.applyFilter();
        },
        async cancelReservation(r) {
            const confirmed = await confirmDelete(this.$t('stock_reservation.cancel_confirm'));
            if (confirmed) {
                router.post(`/apps/stock-reservations/${r.id}/cancel`);
            }
        },
    },
};
</script>
