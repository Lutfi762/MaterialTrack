<template>
    <Head><title>{{ $t('supplier_scorecards.title') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <DataTable
                :title="$t('supplier_scorecards.title')"
                icon="fa-star"
                :items="scorecards"
                :filters="filters"
                :columns="columns"
                search-placeholder="supplier_scorecards.search_placeholder"
                :has-create-permission="false"
                :has-edit-permission="false"
                :has-delete-permission="false"
            >
                <template #filter>
                    <select v-model="filterSupplier" class="mtx-input mtx-input-sm w-48 mr-2" @change="applyFilter">
                        <option value="">Semua Supplier</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <input type="month" v-model="filterPeriod" class="mtx-input mtx-input-sm w-40" @change="applyFilter">
                    <button v-if="hasAnyPermission(['supplier_scorecards.generate'])" @click="generateScorecard" class="mtx-btn mtx-btn-primary mtx-btn-sm ml-2">
                        {{ $t('supplier_scorecards.generate') }}
                    </button>
                </template>
                <template #cell_supplier="{ item }">
                    {{ item.supplier.name }}
                </template>
                <template #cell_delivery_score="{ item }">
                    <span :class="{'text-green-600 font-bold': item.delivery_score >= 80, 'text-yellow-600': item.delivery_score >= 60 && item.delivery_score < 80, 'text-red-600 font-bold': item.delivery_score < 60}">
                        {{ Number(item.delivery_score).toFixed(1) }}
                    </span>
                </template>
                <template #cell_quality_score="{ item }">
                    <span :class="{'text-green-600 font-bold': item.quality_score >= 80, 'text-yellow-600': item.quality_score >= 60 && item.quality_score < 80, 'text-red-600 font-bold': item.quality_score < 60}">
                        {{ Number(item.quality_score).toFixed(1) }}
                    </span>
                </template>
                <template #cell_overall_score="{ item }">
                    <span :class="{'text-green-600 font-bold': item.overall_score >= 80, 'text-yellow-600': item.overall_score >= 60 && item.overall_score < 80, 'text-red-600 font-bold': item.overall_score < 60}">
                        {{ Number(item.overall_score).toFixed(1) }}
                    </span>
                </template>
            </DataTable>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, DataTable },
    props: { scorecards: Object, filters: Object, suppliers: Array },
    setup(props) {
        const { t } = useI18n();
        const filterSupplier = ref(props.filters.supplier_id || '');
        const filterPeriod = ref(props.filters.period || '');
        const columns = [
            { key: 'supplier', label: 'supplier_scorecards.col_supplier' },
            { key: 'period', label: 'supplier_scorecards.col_period' },
            { key: 'total_po', label: 'supplier_scorecards.col_total_po' },
            { key: 'delivery_score', label: 'supplier_scorecards.col_delivery' },
            { key: 'quality_score', label: 'supplier_scorecards.col_quality' },
            { key: 'overall_score', label: 'supplier_scorecards.col_overall' },
            { key: 'total_qty_rejected', label: 'supplier_scorecards.col_rejected' }
        ];

        const applyFilter = () => {
            router.get('/apps/supplier-scorecards', { supplier_id: filterSupplier.value, period: filterPeriod.value, page: 1 }, { preserveState: true });
        };

        const generateScorecard = () => {
            if (!filterPeriod.value) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Pilih periode (bulan) terlebih dahulu di kolom filter.' });
                return;
            }
            Swal.fire({
                title: t('supplier_scorecards.generate_confirm'),
                text: "Periode: " + filterPeriod.value,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Generate',
                cancelButtonText: t('common.cancel')
            }).then((result) => {
                if (result.isConfirmed) {
                    router.post('/apps/supplier-scorecards/generate', { period: filterPeriod.value });
                }
            });
        };

        return { columns, filterSupplier, filterPeriod, applyFilter, generateScorecard };
    }
}
</script>
