<template>
    <Head><title>Material Keluar - MaterialTrack</title></Head>
    <div class="stockouts-page">

        <div class="page-header-card mb-4">
            <div>
                <h4 class="mb-1"><i class="fa fa-arrow-up-right-from-square me-2 text-danger"></i>Material Keluar</h4>
                <p class="text-muted mb-0 small">Riwayat pengeluaran material dari gudang</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/apps/stock-outs/export/excel" class="btn-modern btn-modern-outline">
                    <i class="fa fa-file-excel me-2"></i>Export Excel
                </a>
                <a href="/apps/stock-outs/download-all/pdf" class="btn-modern btn-modern-outline">
    <i class="fa fa-file-pdf me-2"></i>Download Semua PDF
</a>
                <Link v-if="hasAnyPermission(['stock_outs.create'])" href="/apps/stock-outs/create" class="btn-modern btn-modern-danger">
                    <i class="fa fa-plus me-2"></i>Input Baru
                </Link>
            </div>
        </div>

        <div class="filter-bar mb-4">
            <div class="filter-item">
                <i class="fa fa-box text-muted"></i>
                <select v-model="filterItem" class="filter-select">
                    <option value="">Semua Material</option>
                    <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
            </div>
            <div class="filter-item">
                <i class="fa fa-calendar text-muted"></i>
                <input type="date" v-model="filterFrom" class="filter-select">
            </div>
            <div class="filter-item">
                <span class="text-muted small">s/d</span>
                <input type="date" v-model="filterTo" class="filter-select">
            </div>
            <button class="btn-modern btn-modern-primary" @click="applyFilter">
                <i class="fa fa-filter me-2"></i>Terapkan
            </button>
        </div>

        <div v-if="stockOuts.data.length > 0" class="transaction-list">
            <div v-for="row in stockOuts.data" :key="row.id" class="transaction-card">
                <div class="transaction-icon">
                    <i class="fa fa-arrow-up-right-from-square"></i>
                </div>
                <div class="transaction-main">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="transaction-ref">{{ row.reference_no }}</div>
                            <div class="transaction-item-name">{{ row.item?.name }}</div>
                        </div>
                        <div class="transaction-qty">
                            <span>{{ row.qty }}</span> {{ row.item?.unit }}
                        </div>
                    </div>
                    <div class="transaction-meta">
                        <span class="meta-badge"><i class="fa fa-building me-1"></i>{{ row.department?.name }}</span>
                        <span class="meta-badge"><i class="fa fa-user me-1"></i>{{ row.pic?.name }}</span>
                        <span class="meta-badge"><i class="fa fa-clipboard me-1"></i>{{ row.purpose }}</span>
                        <span class="meta-badge"><i class="fa fa-calendar me-1"></i>{{ row.date }}</span>
                    </div>
                </div>
                <div class="transaction-actions">
                    <a :href="`/apps/stock-outs/${row.id}/pdf`" target="_blank" class="act-btn act-btn-neutral" title="Download PDF">
                        <i class="fa fa-file-pdf"></i>
                    </a>
                    <Link v-if="hasAnyPermission(['stock_outs.edit'])" :href="`/apps/stock-outs/${row.id}/edit`" class="act-btn act-btn-success" title="Edit">
                        <i class="fa fa-pencil-alt"></i>
                    </Link>
                    <button v-if="hasAnyPermission(['stock_outs.delete'])" class="act-btn act-btn-danger" title="Batalkan" @click="handleDelete(row.id)">
                        <i class="fa fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <i class="fa fa-inbox"></i>
            <p class="mb-0 fw-semibold">Belum ada transaksi material keluar</p>
            <p class="text-muted small">Klik "Input Baru" untuk mencatat transaksi pertama</p>
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

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { stockOuts: Object, items: Array, filters: Object },
    setup(props) {
        const filterItem = ref(props.filters?.item_id ?? '');
        const filterFrom = ref(props.filters?.date_from ?? '');
        const filterTo = ref(props.filters?.date_to ?? '');

        const applyFilter = () => {
            router.get('/apps/stock-outs', {
                item_id: filterItem.value || undefined,
                date_from: filterFrom.value || undefined,
                date_to: filterTo.value || undefined,
            }, { preserveState: true });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete('Transaksi ini akan dibatalkan dan stok dikembalikan.')) {
                router.delete(`/apps/stock-outs/${id}`);
            }
        };

        return { filterItem, filterFrom, filterTo, applyFilter, handleDelete };
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
.btn-modern-danger { background: #dc3545; color: white; box-shadow: 0 4px 12px rgba(220,53,69,0.25); }
.btn-modern-danger:hover { color: white; }
.btn-modern-primary { background: #3b82f6; color: white; box-shadow: 0 4px 12px rgba(59,130,246,0.25); }
.btn-modern-primary:hover { color: white; }
.btn-modern-outline { background: white; color: #059669; border: 1.5px solid #d1fae5; }
.btn-modern-outline:hover { color: #059669; background: #f0fdf4; }

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
    background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.transaction-main { flex: 1; min-width: 0; }
.transaction-ref { font-size: 11px; color: #94a3b8; font-weight: 600; letter-spacing: 0.5px; }
.transaction-item-name { font-size: 15px; font-weight: 700; color: #1e293b; margin-top: 2px; }
.transaction-qty { font-size: 20px; font-weight: 700; color: #dc2626; white-space: nowrap; }
.transaction-qty span { font-size: 20px; }
.transaction-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.meta-badge { font-size: 11px; color: #64748b; background: #f8fafc; padding: 4px 10px; border-radius: 20px; }
.transaction-actions { display: flex; gap: 6px; flex-shrink: 0; }
.act-btn {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    border: none; text-decoration: none; font-size: 13px; transition: transform 0.15s ease;
}
.act-btn:hover { transform: scale(1.1); }
.act-btn-neutral { background: #f1f5f9; color: #64748b; }
.act-btn-success { background: #d1fae5; color: #059669; }
.act-btn-danger { background: #fee2e2; color: #dc2626; }
.empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #dee2e6; }
.empty-state i { font-size: 42px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>