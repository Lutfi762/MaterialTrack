<template>
    <main class="p-6">
        <Head :title="$t('stock_reservation.detail')" />
        <div class="max-w-5xl mx-auto">
            <div class="mtx-page-header">
                <div>
                    <h1 class="mtx-page-title">{{ $t('stock_reservation.detail') }}</h1>
                    <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('stock_reservation.heading'), url: '/apps/stock-reservations' }, { label: $t('stock_reservation.detail') }]" />
                </div>
                <Link href="/apps/stock-reservations" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 mtx-card">
                    <div class="mtx-card-header">
                        <span class="mtx-card-title">{{ $t('stock_reservation.detail_info') }}</span>
                    </div>
                    <div class="mtx-card-body">
                        <table class="w-full text-sm divide-y divide-gray-200">
                            <tr>
                                <th class="text-left align-top py-2 pr-4 w-48 font-semibold text-gray-500">{{ $t('stock_reservation.col_material') }}</th>
                                <td class="py-2">
                                    <div class="font-semibold text-gray-900">{{ reservation.item?.name }}</div>
                                    <div class="text-xs text-gray-400">{{ reservation.item?.code }}</div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.col_qty') }}</th>
                                <td class="py-2 font-bold text-lg">{{ reservation.qty }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.col_project') }}</th>
                                <td class="py-2">{{ reservation.project_name || '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.purpose') }}</th>
                                <td class="py-2">{{ reservation.purpose || '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.col_dept') }}</th>
                                <td class="py-2">{{ reservation.department?.name || '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.col_by') }}</th>
                                <td class="py-2">{{ reservation.user?.name }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.col_date') }}</th>
                                <td class="py-2">{{ formatDate(reservation.created_at) }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('stock_reservation.col_until') }}</th>
                                <td class="py-2">{{ reservation.reserved_until ? formatDate(reservation.reserved_until) : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left align-top py-2 pr-4 font-semibold text-gray-500">{{ $t('common.status') }}</th>
                                <td class="py-2">
                                    <span class="mtx-badge" :class="statusBadge(reservation.status)">{{ $t('stock_reservation.status_' + reservation.status) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="mtx-card">
                    <div class="mtx-card-header">
                        <span class="mtx-card-title">{{ $t('common.actions') }}</span>
                    </div>
                    <div class="mtx-card-body flex flex-col gap-2">
                        <button v-if="reservation.status === 'active'" @click="fulfill" class="mtx-btn mtx-btn-primary">
                            <i class="fa fa-check" aria-hidden="true"></i> {{ $t('stock_reservation.fulfill') }}
                        </button>
                        <button v-if="reservation.status === 'active'" @click="cancel" class="mtx-btn mtx-btn-secondary">
                            <i class="fa fa-ban" aria-hidden="true"></i> {{ $t('stock_reservation.cancel_btn') }}
                        </button>
                        <button @click="confirmDelete" class="mtx-btn mtx-btn-danger">
                            <i class="fa fa-trash" aria-hidden="true"></i> {{ $t('common.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Breadcrumb },
    props: { reservation: Object },
    methods: {
        formatDate(d) {
            return new Date(d).toLocaleDateString(undefined, { day: '2-digit', month: 'long', year: 'numeric' });
        },
        statusBadge(s) {
            return { active: 'mtx-badge-warning', fulfilled: 'mtx-badge-success', cancelled: 'mtx-badge-neutral' }[s] || 'mtx-badge-neutral';
        },
        fulfill() {
            router.post(`/apps/stock-reservations/${this.reservation.id}/fulfill`);
        },
        async cancel() {
            const confirmed = await confirmDelete(this.$t('stock_reservation.cancel_confirm'));
            if (confirmed) router.post(`/apps/stock-reservations/${this.reservation.id}/cancel`);
        },
        async confirmDelete() {
            const confirmed = await confirmDelete(this.$t('stock_reservation.delete_confirm'));
            if (confirmed) router.delete(`/apps/stock-reservations/${this.reservation.id}`);
        },
    },
};
</script>
