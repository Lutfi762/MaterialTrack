<template>
    <Head><title>{{ $t('stock_out.title') }} - MaterialTrack</title></Head>
    <div class="stockouts-page">

        <div class="page-header-card mb-4">
            <div>
                <h4 class="mb-1"><i class="fa fa-arrow-up-right-from-square me-2 text-danger"></i>{{ $t('stock_out.title') }}</h4>
                <p class="text-gray-400 mb-0 small">{{ $t('stock_out.subtitle') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a v-if="hasAnyPermission(['stock_outs.export', 'stock_outs.index'])" href="/apps/stock-outs/export/excel" class="btn-modern btn-modern-outline">
                    <i class="fa fa-file-excel me-2"></i>{{ $t('stock_out.export_excel') }}
                </a>
                <a v-if="hasAnyPermission(['stock_outs.export', 'stock_outs.index'])" href="/apps/stock-outs/download-all/pdf" class="btn-modern btn-modern-outline">
                    <i class="fa fa-file-pdf me-2"></i>{{ $t('stock_out.download_all_pdf') }}
                </a>
                <Link v-if="hasAnyPermission(['stock_outs.create'])" href="/apps/stock-outs/create" class="btn-modern btn-modern-danger">
                    <i class="fa fa-plus me-2"></i>{{ $t('stock_out.add') }}
                </Link>
            </div>
        </div>

        <div class="status-tabs mb-4">
            <button class="status-tab" :class="{ active: filterStatus === '' }" @click="filterStatus = ''; applyFilter()">
                {{ $t('common.all') }}
            </button>
            <button class="status-tab tab-pending" :class="{ active: filterStatus === 'pending' }" @click="filterStatus = 'pending'; applyFilter()">
<i class="fa fa-clock me-1"></i>{{ $t('common.pending') }}
                <span v-if="pendingCount > 0" class="tab-badge">{{ pendingCount }}</span>
            </button>
            <button class="status-tab tab-approved" :class="{ active: filterStatus === 'approved' }" @click="filterStatus = 'approved'; applyFilter()">
<i class="fa fa-check me-1"></i>{{ $t('common.approved') }}
            </button>
            <button class="status-tab tab-rejected" :class="{ active: filterStatus === 'rejected' }" @click="filterStatus = 'rejected'; applyFilter()">
<i class="fa fa-times me-1"></i>{{ $t('common.rejected') }}
            </button>
        </div>

        <div class="filter-bar mb-4">
            <div class="filter-item">
                <i class="fa fa-building text-gray-400"></i>
                <select v-model="filterDepartment" class="filter-select" :disabled="isLoading">
                    <option value="">{{ $t('common.all_department') }}</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
            <div class="filter-item">
                <i class="fa fa-box text-gray-400"></i>
                <select v-model="filterItem" class="filter-select" :disabled="isLoading">
                    <option value="">{{ $t('common.all_material') }}</option>
                    <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
            </div>
            <div class="filter-item">
                <i class="fa fa-calendar text-gray-400"></i>
                <input type="date" v-model="filterFrom" class="filter-select" :disabled="isLoading">
            </div>
            <div class="filter-item">
                <span class="text-gray-500 text-xs">{{ $t('stock_out.date_range') }}</span>
                <input type="date" v-model="filterTo" class="filter-select" :disabled="isLoading">
            </div>
            <button class="btn-modern btn-modern-primary" @click="applyFilter" :disabled="isLoading">
                <span v-if="isLoading" class="fa fa-circle-notch fa-spin mr-2" role="status"></span>
                <i v-else class="fa fa-filter me-2"></i>{{ $t('common.apply') }}
            </button>
        </div>

        <div v-if="stockOuts.data.length > 0" class="transaction-list">
            <div v-for="row in stockOuts.data" :key="row.id" class="transaction-card">
                <div class="transaction-icon" role="img" :aria-label="$t('stock_out.title')">
                    <i class="fa fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </div>
                <div class="transaction-main">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="transaction-ref">{{ row.reference_no }}</div>
                            <div class="transaction-item-name">{{ row.item?.name }}</div>
                        </div>
                        <div class="transaction-qty" aria-label="Quantity">
                            <span>{{ row.qty }}</span> {{ row.item?.unit }}
                        </div>
                    </div>
                    <div class="transaction-meta">
                        <span class="meta-badge"><i class="fa fa-building me-1" aria-hidden="true"></i>{{ row.department?.name }}</span>
                        <span class="meta-badge"><i class="fa fa-user me-1" aria-hidden="true"></i>{{ row.pic?.name }}</span>
                        <span class="meta-badge"><i class="fa fa-clipboard me-1" aria-hidden="true"></i>{{ row.purpose }}</span>
                        <span class="meta-badge"><i class="fa fa-calendar me-1" aria-hidden="true"></i>{{ row.date }}</span>
                        <span v-if="row.status === 'pending'" class="status-badge status-pending"><i class="fa fa-clock me-1"></i>{{ $t('stock_out.pending_approval') }}</span>
                        <span v-else-if="row.status === 'approved'" class="status-badge status-approved"><i class="fa fa-check me-1"></i>{{ $t('common.approved') }}</span>
                        <span v-else-if="row.status === 'rejected'" class="status-badge status-rejected"><i class="fa fa-times me-1"></i>{{ $t('common.rejected') }}</span>
                    </div>
                    <div v-if="row.notes" class="transaction-notes">
                        <i class="fa fa-sticky-note me-1" aria-hidden="true"></i>{{ row.notes }}
                    </div>
                </div>
                <div class="transaction-actions">
                    <button v-if="row.status === 'pending' && hasAnyPermission(['stock_outs.approve'])" class="act-btn act-btn-success" :title="$t('stock_out.approve_title')" @click="handleApprove(row.id)">
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </button>
                    <button v-if="row.status === 'pending' && hasAnyPermission(['stock_outs.approve'])" class="act-btn act-btn-danger" :title="$t('stock_out.reject_title')" @click="handleReject(row.id)">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                    <Link :href="`/apps/stock-outs/${row.id}`" class="act-btn act-btn-info" :title="$t('stock_out.view_detail')" :aria-label="$t('stock_out.view_detail')">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </Link>
                    <a :href="`/apps/stock-outs/${row.id}/pdf`" target="_blank" class="act-btn act-btn-neutral" :title="$t('stock_out.download_pdf')" :aria-label="$t('stock_out.download_pdf')">
                        <i class="fa fa-file-pdf" aria-hidden="true"></i>
                    </a>
                    <Link v-if="hasAnyPermission(['stock_outs.edit']) && row.status !== 'rejected'" :href="`/apps/stock-outs/${row.id}/edit`" class="act-btn act-btn-success" :title="$t('common.edit')" :aria-label="$t('stock_out.edit_transaction')">
                        <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                    </Link>
                    <button v-if="hasAnyPermission(['stock_outs.delete'])" class="act-btn act-btn-danger" :title="$t('common.cancel')" :aria-label="$t('stock_out.cancel_transaction')" @click="handleDelete(row.id)">
                        <i class="fa fa-undo" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <i class="fa fa-inbox"></i>
            <p class="mb-0 font-semibold">{{ $t('stock_out.empty') }}</p>
            <p class="text-gray-500 text-xs">{{ $t('stock_out.empty_hint') }}</p>
        </div>

        <Pagination v-if="stockOuts.data.length > 0" :links="stockOuts.links" align="center" />
    </div>
</template>

<script>
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';
import LayoutApp from '../../../Layouts/App.vue';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { stockOuts: Object, items: Array, departments: Array, filters: Object, pendingCount: { type: Number, default: 0 } },
    setup(props) {
        const { t } = useI18n();
        const filterDepartment = ref(props.filters?.department_id ?? '');
        const filterItem = ref(props.filters?.item_id ?? '');
        const filterFrom = ref(props.filters?.date_from ?? '');
        const filterTo = ref(props.filters?.date_to ?? '');
        const filterStatus = ref(props.filters?.status ?? '');
        const isLoading = ref(false);

        const applyFilter = () => {
            isLoading.value = true;
            router.get('/apps/stock-outs', {
                department_id: filterDepartment.value || undefined,
                item_id: filterItem.value || undefined,
                date_from: filterFrom.value || undefined,
                date_to: filterTo.value || undefined,
                status: filterStatus.value || undefined,
            }, {
                preserveState: true,
                onFinish: () => { isLoading.value = false; }
            });
        };

        const handleApprove = async (id) => {
            const { value: confirmed } = await Swal.fire({
                title: t('stock_out.approve_confirm'),
                text: t('stock_out.approve_desc'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                confirmButtonText: t('stock_out.approve_yes'),
                cancelButtonText: t('common.cancel'),
            });
            if (confirmed) {
                isLoading.value = true;
                router.patch(`/apps/stock-outs/${id}/approve`, {}, {
                    onFinish: () => { isLoading.value = false; },
                });
            }
        };

        const handleReject = async (id) => {
            const { value: confirmed } = await Swal.fire({
                title: t('stock_out.reject_confirm'),
                text: t('stock_out.reject_desc'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: t('stock_out.reject_yes'),
                cancelButtonText: t('common.cancel'),
            });
            if (confirmed) {
                isLoading.value = true;
                router.patch(`/apps/stock-outs/${id}/reject`, {}, {
                    onFinish: () => { isLoading.value = false; },
                });
            }
        };

        const handleDelete = async (id) => {
            if (await confirmDelete(t('stock_out.cancel_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/stock-outs/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => {
                        isLoading.value = false;
                    }
                });
            }
        };

        return { t, filterDepartment, filterItem, filterFrom, filterTo, filterStatus, applyFilter, handleApprove, handleReject, handleDelete, isLoading };
    },
};
</script>

<style scoped>
.page-header-card {
    background: var(--color-surface); border: 1px solid color-mix(in srgb, var(--color-outline) 20%, transparent); border-radius: 16px; padding: 22px 26px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.btn-modern {
    display: inline-flex; align-items: center; border-radius: 10px;
    padding: 10px 18px; font-weight: 600; font-size: 13px;
    text-decoration: none; border: none; transition: transform 0.15s ease;
}
.btn-modern:hover { transform: translateY(-2px); text-decoration: none; }
.btn-modern-danger { background: var(--color-danger); color: white; box-shadow: 0 4px 12px color-mix(in srgb, var(--color-danger) 25%, transparent); }
.btn-modern-danger:hover { color: white; }
.btn-modern-primary { background: var(--color-primary); color: white; box-shadow: 0 4px 12px color-mix(in srgb, var(--color-primary) 25%, transparent); }
.btn-modern-primary:hover { color: white; }
.btn-modern-outline { background: var(--color-surface); color: var(--color-primary); border: 1.5px solid color-mix(in srgb, var(--color-outline) 30%, transparent); }
.btn-modern-outline:hover { color: var(--color-primary); background: var(--color-bg); }

.filter-bar {
    background: var(--color-surface); border: 1px solid color-mix(in srgb, var(--color-outline) 20%, transparent); border-radius: 14px; padding: 14px 18px;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.filter-item { display: flex; align-items: center; gap: 8px; }
.filter-select { background: var(--color-surface); color: var(--color-on-surface); border: 1.5px solid color-mix(in srgb, var(--color-outline) 30%, transparent); border-radius: 8px; padding: 7px 12px; font-size: 13px; }

.status-tabs {
    display: flex; gap: 8px; flex-wrap: wrap;
}
.status-tab {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 500;
    border: 1.5px solid color-mix(in srgb, var(--color-outline) 30%, transparent); background: var(--color-surface); color: var(--color-on-surface-variant);
    cursor: pointer; transition: all 0.2s;
}
.status-tab:hover { background: var(--color-bg); border-color: var(--color-primary); }
.status-tab.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.status-tab.tab-pending.active { background: var(--color-warning); border-color: var(--color-warning); }
.status-tab.tab-approved.active { background: var(--color-success); border-color: var(--color-success); }
.status-tab.tab-rejected.active { background: var(--color-danger); border-color: var(--color-danger); }
.tab-badge {
    background: white; color: #f59e0b; font-size: 11px; font-weight: 700;
    padding: 1px 7px; border-radius: 10px; min-width: 20px; text-align: center;
}
.status-tab.active .tab-badge { background: rgba(255,255,255,0.25); color: white; }

.status-badge {
    font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px;
}
.status-pending { background: color-mix(in srgb, var(--color-warning) 15%, transparent); color: var(--color-warning); }
.status-approved { background: color-mix(in srgb, var(--color-success) 15%, transparent); color: var(--color-success); }
.status-rejected { background: color-mix(in srgb, var(--color-danger) 15%, transparent); color: var(--color-danger); }

.transaction-list { display: flex; flex-direction: column; gap: 12px; }
.transaction-card {
    background: var(--color-surface); border: 1px solid color-mix(in srgb, var(--color-outline) 20%, transparent); border-radius: 14px; padding: 18px 20px;
    display: flex; gap: 16px; align-items: flex-start;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.transaction-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.08); transform: translateY(-2px); }
.transaction-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: color-mix(in srgb, var(--color-danger) 12%, transparent); color: var(--color-danger);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.transaction-main { flex: 1; min-width: 0; }
.transaction-ref { font-size: 11px; color: #94a3b8; font-weight: 600; letter-spacing: 0.5px; }
.transaction-item-name { font-size: 15px; font-weight: 700; color: var(--color-on-surface); margin-top: 2px; }
.transaction-qty { font-size: 20px; font-weight: 700; color: var(--color-danger); white-space: nowrap; }
.transaction-qty span { font-size: 20px; }
.transaction-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.meta-badge { font-size: 11px; color: var(--color-on-surface-variant); background: var(--color-bg); padding: 4px 10px; border-radius: 20px; }
.transaction-actions { display: flex; gap: 6px; flex-shrink: 0; }
.act-btn {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    border: none; text-decoration: none; font-size: 13px; transition: transform 0.15s ease;
}
.act-btn:hover { transform: scale(1.1); }
.act-btn-neutral { background: var(--color-bg); color: var(--color-on-surface-variant); }
.act-btn-info { background: color-mix(in srgb, var(--color-primary) 12%, transparent); color: var(--color-primary); }
.act-btn-success { background: color-mix(in srgb, var(--color-success) 12%, transparent); color: var(--color-success); }
.act-btn-danger { background: color-mix(in srgb, var(--color-danger) 12%, transparent); color: var(--color-danger); }
.transaction-notes {
    margin-top: 8px; padding: 8px 12px; background: color-mix(in srgb, var(--color-warning) 10%, transparent); border: 1px solid color-mix(in srgb, var(--color-warning) 35%, transparent);
    border-radius: 8px; font-size: 12px; color: var(--color-warning); line-height: 1.5;
}
.empty-state { text-align: center; padding: 60px 20px; background: var(--color-surface); border-radius: 16px; border: 1px dashed color-mix(in srgb, var(--color-outline) 40%, transparent); }
.empty-state i { font-size: 42px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>