<template>
    <div>
        <Head title="Activity Log" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('activity_logs.heading') }}</h1>
                <Breadcrumb :items="[
                    { label: 'Dashboard', url: '/apps/dashboard' },
                    { label: $t('activity_logs.heading') }
                ]" />
            </div>
            <a v-if="hasAnyPermission(['activity_logs.export', 'activity_logs.index'])" :href="exportUrl" class="btn btn-success btn-sm">
                <i class="fa fa-file-csv me-1"></i> Export CSV
            </a>
        </div>

        <!-- Ledger integrity -->
        <div v-if="ledgerStatus" class="alert mb-4 py-2" :class="ledgerStatus.valid ? 'alert-success' : 'alert-danger'">
            <i class="fa me-1" :class="ledgerStatus.valid ? 'fa-shield-alt' : 'fa-exclamation-triangle'"></i>
            <strong>{{ ledgerStatus.valid ? 'Ledger stock movements valid' : 'Ledger stock movements rusak!' }}</strong>
            <span class="ms-2 text-muted">— {{ ledgerStatus.checked }} baris diperiksa (rantai hash SHA-256).</span>
            <span v-if="!ledgerStatus.valid" class="d-block small mt-1">
                ID bermasalah: {{ ledgerStatus.broken_ids.join(', ') }}
            </span>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilter" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{ $t('activity_logs.action') }}</label>
                        <select class="form-select" v-model="filterForm.action">
                            <option value="">{{ $t('activity_logs.all_actions') }}</option>
                            <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">{{ $t('activity_logs.user') }}</label>
                        <select class="form-select" v-model="filterForm.user_id">
                            <option value="">{{ $t('activity_logs.all_users') }}</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Cari</label>
                        <input type="text" class="form-control" v-model="filterForm.q" placeholder="aksi / IP / model">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold">Dari</label>
                        <input type="date" class="form-control" v-model="filterForm.date_from">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold">Sampai</label>
                        <input type="date" class="form-control" v-model="filterForm.date_to">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter me-1"></i> {{ $t('common.filter') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" @click="resetFilter">
                            <i class="fa fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Log Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 160px;">{{ $t('activity_logs.col_time') }}</th>
                                <th style="width: 150px;">{{ $t('activity_logs.user') }}</th>
                                <th style="width: 180px;">{{ $t('activity_logs.action') }}</th>
                                <th>{{ $t('activity_logs.col_detail') }}</th>
                                <th style="width: 120px;">{{ $t('activity_logs.col_ip') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td>
                                    <div class="text-nowrap">{{ formatDate(log.created_at) }}</div>
                                    <small class="text-muted">{{ formatTime(log.created_at) }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ log.user?.name || 'System' }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="actionBadgeClass(log.action)">
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td>
                                    <div v-if="log.properties?.item_name" class="text-muted">
                                        Item: <strong>{{ log.properties.item_name }}</strong>
                                    </div>
                                    <div v-if="log.properties?.qty" class="text-muted">
                                        Qty: {{ log.properties.qty }}
                                    </div>
                                    <div v-if="log.properties?.reference_no" class="text-muted">
                                        Ref: {{ log.properties.reference_no }}
                                    </div>
                                    <div v-if="log.properties?.status" class="mt-1">
                                        <span class="badge bg-secondary">{{ log.properties.status }}</span>
                                    </div>
                                    <div v-if="log.properties?.old || log.properties?.new" class="small mt-1">
                                        <div v-if="log.properties.old && Object.keys(log.properties.old).length" class="text-muted">
                                            <i class="fa fa-minus-circle text-danger me-1"></i>
                                            {{ summaryOf(log.properties.old) }}
                                        </div>
                                        <div v-if="log.properties.new && Object.keys(log.properties.new).length" class="text-muted">
                                            <i class="fa fa-plus-circle text-success me-1"></i>
                                            {{ summaryOf(log.properties.new) }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ log.ip_address }}</small>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                    {{ $t('activity_logs.empty') }}
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
                {{ $t('activity_logs.showing', { from: logs.from || 0, to: logs.to || 0, total: logs.total }) }}
            </div>
            <nav v-if="logs.last_page > 1">
                <ul class="pagination mb-0">
                    <li class="page-item" :class="{ disabled: !logs.prev_page_url }">
                        <a class="page-link" href="#" @click.prevent="goToPage(logs.current_page - 1)">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                    </li>
                    <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === logs.current_page }">
                        <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: !logs.next_page_url }">
                        <a class="page-link" href="#" @click.prevent="goToPage(logs.current_page + 1)">
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';

export default {
    components: { Head, Link, Breadcrumb },
    props: {
        logs: Object,
        actions: Array,
        users: Array,
        ledgerStatus: Object,
    },
    data() {
        return {
            filterForm: {
                action: '',
                user_id: '',
                q: '',
                date_from: '',
                date_to: '',
            },
        };
    },
    computed: {
        exportUrl() {
            const params = new URLSearchParams();
            if (this.filterForm.action) params.set('action', this.filterForm.action);
            if (this.filterForm.user_id) params.set('user_id', this.filterForm.user_id);
            if (this.filterForm.q) params.set('q', this.filterForm.q);
            if (this.filterForm.date_from) params.set('date_from', this.filterForm.date_from);
            if (this.filterForm.date_to) params.set('date_to', this.filterForm.date_to);
            const qs = params.toString();
            return '/apps/activity-logs/export' + (qs ? '?' + qs : '');
        },
        visiblePages() {
            const pages = [];
            const start = Math.max(1, this.logs.current_page - 2);
            const end = Math.min(this.logs.last_page, this.logs.current_page + 2);
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
    },
    methods: {
        formatDate(datetime) {
            return new Date(datetime).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric',
            });
        },
        formatTime(datetime) {
            return new Date(datetime).toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit',
            });
        },
        actionBadgeClass(action) {
            if (action.includes('create')) return 'bg-success';
            if (action.includes('update')) return 'bg-primary';
            if (action.includes('delete')) return 'bg-danger';
            if (action.includes('approve')) return 'bg-info';
            if (action.includes('reject')) return 'bg-warning text-dark';
            return 'bg-secondary';
        },
        summaryOf(props) {
            const entries = Object.entries(props).slice(0, 3);
            if (entries.length === 0) return '';
            return entries.map(([k, v]) => `${k}: ${v}`).join(', ') + (Object.keys(props).length > 3 ? ' ...' : '');
        },
        applyFilter() {
            const params = {};
            if (this.filterForm.action) params.action = this.filterForm.action;
            if (this.filterForm.user_id) params.user_id = this.filterForm.user_id;
            if (this.filterForm.q) params.q = this.filterForm.q;
            if (this.filterForm.date_from) params.date_from = this.filterForm.date_from;
            if (this.filterForm.date_to) params.date_to = this.filterForm.date_to;
            router.get('/apps/activity-logs', params, { preserveState: true });
        },
        resetFilter() {
            this.filterForm = { action: '', user_id: '', q: '', date_from: '', date_to: '' };
            this.applyFilter();
        },
        goToPage(page) {
            if (page < 1 || page > this.logs.last_page) return;
            const params = { page };
            if (this.filterForm.action) params.action = this.filterForm.action;
            if (this.filterForm.user_id) params.user_id = this.filterForm.user_id;
            if (this.filterForm.q) params.q = this.filterForm.q;
            if (this.filterForm.date_from) params.date_from = this.filterForm.date_from;
            if (this.filterForm.date_to) params.date_to = this.filterForm.date_to;
            router.get('/apps/activity-logs', params, { preserveState: true });
        },
    },
};
</script>
