<template>
    <Head><title>{{ $t('activity_logs.heading') }} - MaterialTrack</title></Head>

    <main class="min-h-full bg-bg p-4 sm:p-6 text-on-surface">
        <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="mb-1 text-xs font-medium uppercase tracking-[0.16em] text-on-surface-variant">Security</p>
                <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-on-surface">
                    <i class="fa fa-shield-alt text-primary" aria-hidden="true"></i>
                    {{ $t('activity_logs.heading') }}
                </h2>
            </div>
            <a
                v-if="hasAnyPermission(['activity_logs.export', 'activity_logs.index'])"
                :href="exportUrl"
                class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-4 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg"
            >
                <i class="fa fa-file-csv" aria-hidden="true"></i> Export CSV
            </a>
        </div>

        <!-- Ledger integrity -->
        <div
            v-if="ledgerStatus"
            class="mb-4 flex items-start gap-3 rounded-xl border p-4 text-sm"
            :class="ledgerStatus.valid ? 'border-success/30 bg-success/10 text-success' : 'border-danger/30 bg-danger/10 text-danger'"
        >
            <i class="fa mt-0.5" :class="ledgerStatus.valid ? 'fa-shield-alt' : 'fa-exclamation-triangle'" aria-hidden="true"></i>
            <div>
                <strong>{{ ledgerStatus.valid ? 'Ledger stock movements valid' : 'Ledger stock movements rusak!' }}</strong>
                <span class="ml-1 text-on-surface-variant">— {{ ledgerStatus.checked }} baris diperiksa (rantai hash SHA-256).</span>
                <div v-if="!ledgerStatus.valid" class="mt-1 text-xs">
                    ID bermasalah: {{ ledgerStatus.broken_ids.join(', ') }}
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <select v-model="filterForm.action" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="applyFilter">
                <option value="">{{ $t('activity_logs.all_actions') }}</option>
                <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
            </select>
            <select v-model="filterForm.user_id" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="applyFilter">
                <option value="">{{ $t('activity_logs.all_users') }}</option>
                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
            <input
                v-model="filterForm.q"
                type="text"
                class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary"
                placeholder="aksi / IP / model"
                @keyup.enter="applyFilter"
            >
            <div>
                <label class="mb-1 block text-xs font-medium text-on-surface-variant">{{ $t('common.from') }}</label>
                <input type="date" v-model="filterForm.date_from" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="applyFilter">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-on-surface-variant">{{ $t('common.to') }}</label>
                <input type="date" v-model="filterForm.date_to" class="rounded-full border border-outline/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary" @change="applyFilter">
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg" @click="applyFilter">
                <i class="fa fa-filter" aria-hidden="true"></i> {{ $t('common.filter') }}
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-outline/30 bg-surface px-3.5 py-2.5 text-sm font-medium text-on-surface-variant transition hover:bg-bg" @click="resetFilter">
                <i class="fa fa-rotate-left" aria-hidden="true"></i> {{ $t('common.reset') }}
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-outline/20 bg-surface shadow-sm">
            <DataTable
                :columns="columns"
                :rows="logs.data"
                row-key="id"
                :striped="true"
                :loading="false"
                :show-pagination="false"
                :empty-text="$t('activity_logs.empty')"
            >
                <template #cell-created_at="{ row }">
                    <div class="whitespace-nowrap">{{ formatDate(row.created_at) }}</div>
                    <small class="text-on-surface-variant">{{ formatTime(row.created_at) }}</small>
                </template>
                <template #cell-user="{ row }">
                    <span class="font-semibold">{{ row.user?.name || 'System' }}</span>
                </template>
                <template #cell-properties="{ row }">
                    <div v-if="row.properties?.item_name" class="text-on-surface-variant">
                        Item: <strong class="text-on-surface">{{ row.properties.item_name }}</strong>
                    </div>
                    <div v-if="row.properties?.qty" class="text-on-surface-variant">
                        Qty: {{ row.properties.qty }}
                    </div>
                    <div v-if="row.properties?.reference_no" class="text-on-surface-variant">
                        Ref: {{ row.properties.reference_no }}
                    </div>
                    <div v-if="row.properties?.status" class="mt-1">
                        <span class="inline-flex rounded-full bg-bg px-2 py-0.5 text-xs text-on-surface-variant">{{ row.properties.status }}</span>
                    </div>
                    <div v-if="row.properties?.old || row.properties?.new" class="mt-1 text-xs">
                        <div v-if="row.properties.old && Object.keys(row.properties.old).length" class="text-on-surface-variant">
                            <i class="fa fa-minus-circle text-danger" aria-hidden="true"></i>
                            {{ summaryOf(row.properties.old) }}
                        </div>
                        <div v-if="row.properties.new && Object.keys(row.properties.new).length" class="text-on-surface-variant">
                            <i class="fa fa-plus-circle text-success" aria-hidden="true"></i>
                            {{ summaryOf(row.properties.new) }}
                        </div>
                    </div>
                </template>
                <template #cell-ip_address="{ row }">
                    <small class="text-on-surface-variant">{{ row.ip_address }}</small>
                </template>
            </DataTable>
        </div>

        <!-- Pagination -->
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm text-on-surface-variant">
            <div>{{ $t('activity_logs.showing', { from: logs.from || 0, to: logs.to || 0, total: logs.total }) }}</div>
            <Pagination v-if="logs.last_page > 1" :links="logs.links" />
        </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';


import { useI18n } from 'vue-i18n';

export default {
    layout: LayoutApp,
    components: { Head, DataTable, Pagination },
    props: {
        logs: Object,
        actions: Array,
        users: Array,
        ledgerStatus: Object,
    },
    setup(props) {
        const { t } = useI18n();

        const filterForm = reactive({
            action: '',
            user_id: '',
            q: '',
            date_from: '',
            date_to: '',
        });

        const columns = [
            { key: 'created_at', label: t('activity_logs.col_time'), width: '160px' },
            { key: 'user', label: t('activity_logs.user'), width: '150px' },
            { key: 'action', label: t('activity_logs.col_action'), width: '180px' },
            { key: 'properties', label: t('activity_logs.col_detail') },
            { key: 'ip_address', label: t('activity_logs.col_ip'), width: '120px' },
        ];

        const buildParams = () => {
            const params = {};
            if (filterForm.action) params.action = filterForm.action;
            if (filterForm.user_id) params.user_id = filterForm.user_id;
            if (filterForm.q) params.q = filterForm.q;
            if (filterForm.date_from) params.date_from = filterForm.date_from;
            if (filterForm.date_to) params.date_to = filterForm.date_to;
            return params;
        };


        const exportUrl = () => {
            const qs = new URLSearchParams(buildParams()).toString();
            return '/apps/activity-logs/export' + (qs ? '?' + qs : '');
        };

        const formatDate = (datetime) => new Date(datetime).toLocaleDateString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric',
        });

        const formatTime = (datetime) => new Date(datetime).toLocaleTimeString('id-ID', {
            hour: '2-digit', minute: '2-digit',
        });

        const actionBadgeClass = (action) => {
            if (action.includes('create')) return 'bg-success/15 text-success';
            if (action.includes('update')) return 'bg-primary/15 text-primary';
            if (action.includes('delete')) return 'bg-danger/15 text-danger';
            if (action.includes('approve')) return 'bg-primary/15 text-primary';
            if (action.includes('reject')) return 'bg-warning/15 text-warning';
            return 'bg-bg text-on-surface-variant';
        };

        const summaryOf = (obj) => {
            const entries = Object.entries(obj).slice(0, 3);
            if (entries.length === 0) return '';
            return entries.map(([k, v]) => `${k}: ${v}`).join(', ') + (Object.keys(obj).length > 3 ? ' ...' : '');
        };

        const applyFilter = () => {
            router.get('/apps/activity-logs', buildParams(), { preserveState: true });
        };

        const resetFilter = () => {
            filterForm.action = '';
            filterForm.user_id = '';
            filterForm.q = '';
            filterForm.date_from = '';
            filterForm.date_to = '';
            applyFilter();
        };

        return {
            filterForm,
            columns,
            exportUrl: exportUrl(),
            formatDate,
            formatTime,
            actionBadgeClass,
            summaryOf,
            applyFilter,
            resetFilter,
        };
    },
};
</script>

