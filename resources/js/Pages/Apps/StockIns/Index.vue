<template>
    <Head><title>{{ $t('stock_in.title') }} - MaterialTrack</title></Head>
    <div class="stockins-page">

        <div class="page-header-card mb-4">
            <div>
                <h4 class="mb-1"><i class="fa fa-arrow-down-left me-2 text-success"></i>{{ $t('stock_in.title') }}</h4>
                <p class="text-gray-400 mb-0 small">{{ $t('stock_in.subtitle') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a v-if="hasAnyPermission(['stock_ins.export', 'stock_ins.index'])" href="/apps/stock-ins/export/excel" class="btn-modern btn-modern-outline">
                    <i class="fa fa-file-excel me-2"></i>{{ $t('stock_in.export_excel') }}
                </a>
                <a v-if="hasAnyPermission(['stock_ins.export', 'stock_ins.index'])" href="/apps/stock-ins/download-all/pdf" class="btn-modern btn-modern-outline">
                    <i class="fa fa-file-pdf me-2"></i>{{ $t('stock_in.download_all_pdf') }}
                </a>
                <Link v-if="hasAnyPermission(['stock_ins.create'])" href="/apps/stock-ins/create" class="btn-modern btn-modern-success">
                    <i class="fa fa-plus me-2"></i>{{ $t('stock_in.add') }}
                </Link>
            </div>
        </div>

        <div class="filter-bar mb-4">
            <div class="filter-item">
                <i class="fa fa-building text-gray-400"></i>
                <select v-model="filterDepartment" class="filter-select" :disabled="isLoading">
                    <option value="">{{ $t('stock_in.filter_all_dept') }}</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
            <div class="filter-item">
                <i class="fa fa-box text-gray-400"></i>
                <select v-model="filterItem" class="filter-select" :disabled="isLoading">
                    <option value="">{{ $t('stock_in.filter_all_material') }}</option>
                    <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
            </div>
            <div class="filter-item">
                <i class="fa fa-calendar text-gray-400"></i>
                <input type="date" v-model="filterFrom" class="filter-select" :disabled="isLoading">
            </div>
            <div class="filter-item">
                <span class="text-gray-500 text-xs">{{ $t('stock_in.date_range') }}</span>
                <input type="date" v-model="filterTo" class="filter-select" :disabled="isLoading">
            </div>
            <button class="btn-modern btn-modern-primary" @click="applyFilter" :disabled="isLoading">
                <span v-if="isLoading" class="fa fa-circle-notch fa-spin mr-2" role="status"></span>
                <i v-else class="fa fa-filter me-2"></i>{{ $t('common.apply') }}
            </button>
        </div>

        <div v-if="stockIns.data.length > 0" class="transaction-list">
            <div v-for="row in stockIns.data" :key="row.id" class="transaction-card">
                <div class="transaction-icon" role="img" :aria-label="$t('stock_in.title')">
                    <i class="fa fa-arrow-down-left" aria-hidden="true"></i>
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
                        <span class="meta-badge" v-if="row.po_number"><i class="fa fa-file-invoice me-1" aria-hidden="true"></i>PO: {{ row.po_number }}</span>
                        <span class="meta-badge" v-if="row.supplier"><i class="fa fa-truck me-1" aria-hidden="true"></i>{{ row.supplier.name }}</span>
                        <span class="meta-badge" v-if="row.department"><i class="fa fa-building me-1" aria-hidden="true"></i>{{ row.department.name }}</span>
                        <span class="meta-badge" v-if="!row.supplier && row.source"><i class="fa fa-tag me-1" aria-hidden="true"></i>{{ row.source }}</span>
                        <span class="meta-badge"><i class="fa fa-calendar me-1" aria-hidden="true"></i>{{ row.date }}</span>
                    </div>
                </div>
                <div class="transaction-actions">
                    <a :href="`/apps/stock-ins/${row.id}/pdf`" target="_blank" class="act-btn act-btn-neutral" title="Download PDF" aria-label="Download PDF">
                        <i class="fa fa-file-pdf" aria-hidden="true"></i>
                    </a>
                    <Link v-if="hasAnyPermission(['stock_ins.edit'])" :href="`/apps/stock-ins/${row.id}/edit`" class="act-btn act-btn-success" :title="$t('common.edit')" :aria-label="$t('stock_in.edit')">
                        <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                    </Link>
                    <button v-if="hasAnyPermission(['stock_ins.delete'])" class="act-btn act-btn-danger" :title="$t('common.cancel')" :aria-label="$t('common.cancel')" @click="handleDelete(row.id)">
                        <i class="fa fa-undo" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <i class="fa fa-inbox"></i>
            <p class="mb-0 font-semibold">{{ $t('stock_in.empty') }}</p>
            <p class="text-gray-500 text-xs">{{ $t('stock_in.empty_desc') }}</p>
        </div>

        <Pagination v-if="stockIns.data.length > 0" :links="stockIns.links" align="center" />
    </div>
</template>

<script>
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { confirmDelete } from '@/Utils/confirmDialog';
import LayoutApp from '../../../Layouts/App.vue';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { stockIns: Object, items: Array, departments: Array, filters: Object },
    setup(props) {
        const { t } = useI18n();
        const filterDepartment = ref(props.filters?.department_id ?? '');
        const filterItem = ref(props.filters?.item_id ?? '');
        const filterFrom = ref(props.filters?.date_from ?? '');
        const filterTo = ref(props.filters?.date_to ?? '');
        const isLoading = ref(false);

        const applyFilter = () => {
            isLoading.value = true;
            router.get('/apps/stock-ins', {
                department_id: filterDepartment.value || undefined,
                item_id: filterItem.value || undefined,
                date_from: filterFrom.value || undefined,
                date_to: filterTo.value || undefined,
            }, {
                preserveState: true,
                onFinish: () => { isLoading.value = false; }
            });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete(t('stock_in.cancel_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/stock-ins/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => {
                        isLoading.value = false;
                    }
                });
            }
        };
        return { filterDepartment, filterItem, filterFrom, filterTo, applyFilter, handleDelete, isLoading };
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
.btn-modern-success { background: var(--color-primary); color: white; box-shadow: 0 4px 12px color-mix(in srgb, var(--color-primary) 25%, transparent); }
.btn-modern-success:hover { color: white; }
.btn-modern-outline { background: var(--color-surface); color: var(--color-primary); border: 1.5px solid color-mix(in srgb, var(--color-outline) 30%, transparent); }
.btn-modern-outline:hover { color: var(--color-primary); background: var(--color-bg); }

.filter-bar {
    background: var(--color-surface); border: 1px solid color-mix(in srgb, var(--color-outline) 20%, transparent); border-radius: 14px; padding: 14px 18px;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.filter-item { display: flex; align-items: center; gap: 8px; }
.filter-select { background: var(--color-surface); color: var(--color-on-surface); border: 1.5px solid color-mix(in srgb, var(--color-outline) 30%, transparent); border-radius: 8px; padding: 7px 12px; font-size: 13px; }
.btn-modern-primary { background: var(--color-primary); color: white; box-shadow: 0 4px 12px color-mix(in srgb, var(--color-primary) 25%, transparent); }
.btn-modern-primary:hover { color: white; }

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
    background: color-mix(in srgb, var(--color-primary) 12%, transparent); color: var(--color-primary);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.transaction-main { flex: 1; min-width: 0; }
.transaction-ref { font-size: 11px; color: #94a3b8; font-weight: 600; letter-spacing: 0.5px; }
.transaction-item-name { font-size: 15px; font-weight: 700; color: var(--color-on-surface); margin-top: 2px; }
.transaction-qty { font-size: 20px; font-weight: 700; color: var(--color-primary); white-space: nowrap; }
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
.act-btn-success { background: color-mix(in srgb, var(--color-primary) 12%, transparent); color: var(--color-primary); }
.act-btn-danger { background: color-mix(in srgb, var(--color-danger) 12%, transparent); color: var(--color-danger); }
.empty-state { text-align: center; padding: 60px 20px; background: var(--color-surface); border-radius: 16px; border: 1px dashed color-mix(in srgb, var(--color-outline) 40%, transparent); }
.empty-state i { font-size: 42px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>