<template>
    <div>
        <Head :title="$t('stock_reservation.heading')" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('stock_reservation.heading') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('stock_reservation.heading') }]" />
            </div>
            <Link v-if="hasAnyPermission(['stock_reservations.create'])" href="/apps/stock-reservations/create" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> {{ $t('stock_reservation.add') }}
            </Link>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilter" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ $t('common.status') }}</label>
                        <select class="form-select" v-model="filterForm.status">
                            <option value="">{{ $t('common.all') }}</option>
                            <option value="active">{{ $t('stock_reservation.status_active') }}</option>
                            <option value="fulfilled">{{ $t('stock_reservation.status_fulfilled') }}</option>
                            <option value="cancelled">{{ $t('stock_reservation.status_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">{{ $t('common.item') }}</label>
                        <select class="form-select" v-model="filterForm.item_id">
                            <option value="">{{ $t('common.all') }}</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">[{{ item.code }}] {{ item.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">{{ $t('common.filter') }}</button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-secondary w-100" @click="resetFilter">{{ $t('common.reset') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ $t('stock_reservation.col_date') }}</th>
                                <th>{{ $t('stock_reservation.col_material') }}</th>
                                <th>{{ $t('stock_reservation.col_qty') }}</th>
                                <th>{{ $t('stock_reservation.col_project') }}</th>
                                <th>{{ $t('stock_reservation.col_dept') }}</th>
                                <th>{{ $t('stock_reservation.col_by') }}</th>
                                <th>{{ $t('stock_reservation.col_until') }}</th>
                                <th>{{ $t('common.status') }}</th>
                                <th>{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in reservations.data" :key="r.id">
                                <td>{{ formatDate(r.created_at) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ r.item?.name }}</div>
                                    <small class="text-muted">{{ r.item?.code }}</small>
                                </td>
                                <td class="fw-bold">{{ r.qty }}</td>
                                <td>{{ r.project_name || '-' }}</td>
                                <td>{{ r.department?.name || '-' }}</td>
                                <td>{{ r.user?.name }}</td>
                                <td>{{ r.reserved_until ? formatDate(r.reserved_until) : '-' }}</td>
                                <td>
                                    <span class="badge" :class="statusBadge(r.status)">{{ $t('stock_reservation.status_' + r.status) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <Link v-if="r.status === 'active'" :href="`/apps/stock-reservations/${r.id}`" class="btn btn-sm btn-outline-success" :title="$t('stock_reservation.fulfill')">
                                            <i class="fa fa-check"></i>
                                        </Link>
                                        <button v-if="r.status === 'active'" @click="cancelReservation(r)" class="btn btn-sm btn-outline-warning" :title="$t('stock_reservation.cancel_btn')">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                        <Link :href="`/apps/stock-reservations/${r.id}`" class="btn btn-sm btn-outline-info" :title="$t('common.info')">
                                            <i class="fa fa-eye"></i>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="reservations.data.length === 0">
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa fa-bookmark fa-2x mb-2 d-block"></i>
                                    {{ $t('stock_reservation.empty') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                {{ $t('common.showing') }} {{ reservations.from || 0 }} - {{ reservations.to || 0 }} {{ $t('common.of') }} {{ reservations.total }}
            </div>
            <nav v-if="reservations.last_page > 1">
                <ul class="pagination pagination-sm mb-0">
                    <li v-for="link in reservations.links" :key="link.label" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                        <Link class="page-link" :href="link.url ?? '#'" v-html="link.label" />
                    </li>
                </ul>
            </nav>
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
            return { active: 'bg-warning text-dark', fulfilled: 'bg-success', cancelled: 'bg-secondary' }[s] || 'bg-secondary';
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
