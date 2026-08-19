<template>
    <div>
        <Head :title="$t('stock_reservation.detail')" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('stock_reservation.detail') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('stock_reservation.heading'), url: '/apps/stock-reservations' }, { label: $t('stock_reservation.detail') }]" />
            </div>
            <Link href="/apps/stock-reservations" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
            </Link>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $t('stock_reservation.detail_info') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width:200px">{{ $t('stock_reservation.col_material') }}</th>
                                <td>
                                    <div class="fw-semibold">{{ reservation.item?.name }}</div>
                                    <small class="text-muted">{{ reservation.item?.code }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.col_qty') }}</th>
                                <td class="fw-bold fs-5">{{ reservation.qty }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.col_project') }}</th>
                                <td>{{ reservation.project_name || '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.purpose') }}</th>
                                <td>{{ reservation.purpose || '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.col_dept') }}</th>
                                <td>{{ reservation.department?.name || '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.col_by') }}</th>
                                <td>{{ reservation.user?.name }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.col_date') }}</th>
                                <td>{{ formatDate(reservation.created_at) }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('stock_reservation.col_until') }}</th>
                                <td>{{ reservation.reserved_until ? formatDate(reservation.reserved_until) : '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('common.status') }}</th>
                                <td>
                                    <span class="badge" :class="statusBadge(reservation.status)">{{ $t('stock_reservation.status_' + reservation.status) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $t('common.actions') }}</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button v-if="reservation.status === 'active'" @click="fulfill" class="btn btn-success">
                            <i class="fa fa-check me-1"></i> {{ $t('stock_reservation.fulfill') }}
                        </button>
                        <button v-if="reservation.status === 'active'" @click="cancel" class="btn btn-warning">
                            <i class="fa fa-ban me-1"></i> {{ $t('stock_reservation.cancel_btn') }}
                        </button>
                        <button @click="confirmDelete" class="btn btn-outline-danger">
                            <i class="fa fa-trash me-1"></i> {{ $t('common.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
            return { active: 'bg-warning text-dark', fulfilled: 'bg-success', cancelled: 'bg-secondary' }[s] || 'bg-secondary';
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
