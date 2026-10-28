<template>
    <Head><title>{{ $t('stock_return.title') }} - MaterialTrack</title></Head>
    <div class="stockreturns-page">

        <div class="page-header-card mb-4">
            <div>
                <h4 class="mb-1"><i class="fa fa-undo me-2 text-warning"></i>{{ $t('stock_return.title') }}</h4>
                <p class="text-gray-400 mb-0 small">{{ $t('stock_return.subtitle') }}</p>
            </div>
            <div class="d-flex gap-2">
                <Link v-if="hasAnyPermission(['stock_ins.create'])" href="/apps/stock-returns/create" class="btn-modern btn-modern-warning">
                    <i class="fa fa-plus me-2"></i>{{ $t('stock_return.add') }}
                </Link>
            </div>
        </div>

        <div class="status-tabs mb-4">
            <button class="status-tab" :class="{ active: activeTab === 'all' }" @click="filterByStatus('all')">
                {{ $t('common.all') }} <span class="tab-count">{{ stockReturns.total }}</span>
            </button>
            <button class="status-tab" :class="{ active: activeTab === 'pending' }" @click="filterByStatus('pending')">
                <i class="fa fa-clock me-1"></i>{{ $t('common.pending') }} <span class="tab-count">{{ pendingCount }}</span>
            </button>
            <button class="status-tab" :class="{ active: activeTab === 'approved' }" @click="filterByStatus('approved')">
                <i class="fa fa-check me-1"></i>{{ $t('common.approved') }}
            </button>
            <button class="status-tab" :class="{ active: activeTab === 'rejected' }" @click="filterByStatus('rejected')">
                <i class="fa fa-times me-1"></i>{{ $t('common.rejected') }}
            </button>
        </div>

        <div class="filter-bar mb-4">
            <div class="filter-item">
                <i class="fa fa-building text-gray-400"></i>
                <select v-model="filterDepartment" class="filter-select" :disabled="isLoading">
                    <option value="">{{ $t('stock_return.filter_all_dept') }}</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
            <div class="filter-item">
                <i class="fa fa-box text-gray-400"></i>
                <select v-model="filterItem" class="filter-select" :disabled="isLoading">
                    <option value="">{{ $t('stock_return.filter_all_material') }}</option>
                    <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
            </div>
            <button class="btn-modern btn-modern-primary" @click="applyFilter" :disabled="isLoading">
                <i class="fa fa-filter me-2"></i>{{ $t('common.apply') }}
            </button>
        </div>

        <div v-if="stockReturns.data.length > 0" class="transaction-list">
            <div v-for="row in stockReturns.data" :key="row.id" class="transaction-card">
                <div class="transaction-icon" :class="`icon-${row.status}`">
                    <i class="fa fa-undo"></i>
                </div>
                <div class="transaction-main">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="transaction-item-name">{{ row.item?.name }}</div>
                            <div class="transaction-meta">
                                <span class="meta-badge" v-if="row.department"><i class="fa fa-building me-1"></i>{{ row.department.name }}</span>
                                <span class="meta-badge"><i class="fa fa-user me-1"></i>{{ row.user?.name }}</span>
                                <span class="meta-badge"><i class="fa fa-calendar me-1"></i>{{ row.created_at }}</span>
                            </div>
                        </div>
                        <div class="transaction-qty">
                            <span>+{{ row.qty }}</span> {{ row.item?.unit }}
                        </div>
                    </div>
                    <div v-if="row.reason" class="transaction-reason mt-2">
                        <small class="text-gray-400"><i class="fa fa-info-circle me-1"></i>{{ row.reason }}</small>
                    </div>
                    <div v-if="row.status === 'pending' && hasAnyPermission(['stock_outs.approve'])" class="transaction-actions mt-3">
                        <button class="act-btn act-btn-success" :title="$t('stock_return.approve_title')" @click="handleApprove(row.id)">
                            <i class="fa fa-check"></i> {{ $t('stock_return.approve') }}
                        </button>
                        <button class="act-btn act-btn-danger" :title="$t('stock_return.reject_title')" @click="handleReject(row.id)">
                            <i class="fa fa-times"></i> {{ $t('stock_return.reject') }}
                        </button>
                    </div>
                    <div v-if="row.status !== 'pending'" class="transaction-status-info mt-2">
                        <small :class="row.status === 'approved' ? 'text-success' : 'text-danger'">
                            <i :class="row.status === 'approved' ? 'fa fa-check-circle' : 'fa fa-times-circle'" class="me-1"></i>
                            {{ row.status === 'approved' ? $t('common.approved') : $t('common.rejected') }}
                            <span v-if="row.approver"> â€” {{ row.approver?.name }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <i class="fa fa-inbox"></i>
            <p class="mb-0 font-semibold">{{ $t('stock_return.empty') }}</p>
            <p class="text-gray-500 text-xs">{{ $t('stock_return.empty_desc') }}</p>
        </div>

        <Pagination v-if="stockReturns.data.length > 0" :links="stockReturns.links" align="center" />
    </div>
</template>

<script>
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import LayoutApp from '../../../Layouts/App.vue';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { stockReturns: Object, items: Array, departments: Array, filters: Object, pendingCount: Number },
    setup(props) {
        const { t } = useI18n();
        const activeTab = ref(props.filters?.status ?? 'all');
        const filterDepartment = ref(props.filters?.department_id ?? '');
        const filterItem = ref(props.filters?.item_id ?? '');
        const isLoading = ref(false);

        const filterByStatus = (status) => {
            activeTab.value = status;
            isLoading.value = true;
            router.get('/apps/stock-returns', {
                status: status === 'all' ? undefined : status,
                department_id: filterDepartment.value || undefined,
                item_id: filterItem.value || undefined,
            }, {
                preserveState: true,
                onFinish: () => { isLoading.value = false; }
            });
        };

        const applyFilter = () => {
            isLoading.value = true;
            router.get('/apps/stock-returns', {
                status: activeTab.value === 'all' ? undefined : activeTab.value,
                department_id: filterDepartment.value || undefined,
                item_id: filterItem.value || undefined,
            }, {
                preserveState: true,
                onFinish: () => { isLoading.value = false; }
            });
        };

        const handleApprove = async (id) => {
            const result = await Swal.fire({
                title: t('stock_return.approve_confirm'),
                text: t('stock_return.approve_desc'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: t('stock_return.approve_yes'),
                cancelButtonText: t('common.cancel'),
            });
            if (result.isConfirmed) {
                isLoading.value = true;
                router.post(`/apps/stock-returns/${id}/approve`, {}, {
                    onFinish: () => { isLoading.value = false; }
                });
            }
        };

        const handleReject = async (id) => {
            const result = await Swal.fire({
                title: t('stock_return.reject_confirm'),
                text: t('stock_return.reject_desc'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: t('stock_return.reject_yes'),
                cancelButtonText: t('common.cancel'),
            });
            if (result.isConfirmed) {
                isLoading.value = true;
                router.post(`/apps/stock-returns/${id}/reject`, {}, {
                    onFinish: () => { isLoading.value = false; }
                });
            }
        };

        return { activeTab, filterDepartment, filterItem, isLoading, filterByStatus, applyFilter, handleApprove, handleReject };
    },
};
</script>

<style scoped>
.page-header-card {
    background: white; border-radius: 16px; padding: 22px 26px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.btn-modern {
    display: inline-flex; align-items: center; border-radius: 10px;
    padding: 10px 18px; font-weight: 600; font-size: 13px;
    text-decoration: none; border: none; transition: transform 0.15s ease;
}
.btn-modern:hover { transform: translateY(-2px); text-decoration: none; }
.btn-modern-warning { background: #f59e0b; color: white; box-shadow: 0 4px 12px rgba(245,158,11,0.25); }
.btn-modern-warning:hover { color: white; }
.btn-modern-primary { background: #3b82f6; color: white; box-shadow: 0 4px 12px rgba(59,130,246,0.25); }
.btn-modern-primary:hover { color: white; }

.status-tabs {
    display: flex; gap: 8px; flex-wrap: wrap;
}
.status-tab {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
    border-radius: 10px; border: 1.5px solid #e2e8f0; background: white;
    font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer;
    transition: all 0.2s;
}
.status-tab:hover { border-color: #cbd5e1; color: #334155; }
.status-tab.active { background: #7c3aed; color: white; border-color: #7c3aed; }
.tab-count { font-size: 11px; opacity: 0.8; }

.filter-bar {
    background: white; border-radius: 14px; padding: 14px 18px;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.filter-item { display: flex; align-items: center; gap: 8px; }
.filter-select { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 12px; font-size: 13px; }

.transaction-list { display: flex; flex-direction: column; gap: 12px; }
.transaction-card {
    background: white; border-radius: 14px; padding: 18px 20px;
    display: flex; gap: 16px; align-items: flex-start;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.transaction-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.08); transform: translateY(-2px); }
.transaction-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.icon-pending { background: #fef3c7; color: #d97706; }
.icon-approved { background: #d1fae5; color: #059669; }
.icon-rejected { background: #fee2e2; color: #dc2626; }
.transaction-main { flex: 1; min-width: 0; }
.transaction-item-name { font-size: 15px; font-weight: 700; color: #1e293b; }
.transaction-qty { font-size: 20px; font-weight: 700; color: #d97706; white-space: nowrap; }
.transaction-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.meta-badge { font-size: 11px; color: #64748b; background: #f8fafc; padding: 4px 10px; border-radius: 20px; }
.transaction-actions { display: flex; gap: 8px; }
.act-btn {
    padding: 6px 14px; border-radius: 8px; border: none; font-size: 12px;
    font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
    transition: transform 0.15s ease;
}
.act-btn:hover { transform: scale(1.05); }
.act-btn-success { background: #d1fae5; color: #059669; }
.act-btn-danger { background: #fee2e2; color: #dc2626; }
.empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #dee2e6; }
.empty-state i { font-size: 42px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>
