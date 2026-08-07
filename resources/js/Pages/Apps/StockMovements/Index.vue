<template>
    <Head><title>Riwayat Pergerakan Stok - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-clock-rotate-left me-1"></i> RIWAYAT PERGERAKAN STOK MATERIAL</span>
                                <div class="d-flex gap-2">
                                    <a :href="exportExcelUrl" class="btn btn-sm btn-outline-success">
                                        <i class="fa fa-file-excel me-1"></i> Export Excel
                                    </a>
                                    <a :href="downloadAllUrl" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-file-pdf me-1"></i> Download Semua PDF
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-2">
                                        <select v-model="filterForm.department_id" class="form-select form-select-sm" @change="onDepartmentChange">
                                            <option value="">-- Semua Department --</option>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select v-model="filterForm.item_id" class="form-select form-select-sm">
                                            <option value="">-- Semua Material --</option>
                                            <option v-for="item in filteredItems" :key="item.id" :value="item.id">
                                                [{{ item.code }}] {{ item.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select v-model="filterForm.type" class="form-select form-select-sm">
                                            <option value="">-- Semua Tipe --</option>
                                            <option value="in">Masuk (In)</option>
                                            <option value="out">Keluar (Out)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input v-model="filterForm.date_from" type="date" class="form-control form-control-sm" placeholder="Dari tanggal">
                                    </div>
                                    <div class="col-md-2">
                                        <input v-model="filterForm.date_to" type="date" class="form-control form-control-sm" placeholder="Sampai tanggal">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-1">
                                        <button @click="applyFilter" class="btn btn-sm btn-primary w-100">Filter</button>
                                    </div>
                                    <div class="col-md-1">
                                        <button @click="resetFilter" class="btn btn-sm btn-secondary w-100">Reset</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Material</th>
                                                <th>Tipe</th>
                                                <th>Qty</th>
                                                <th>Stok Sebelum</th>
                                                <th>Stok Sesudah</th>
                                                <th>Keterangan</th>
                                                <th>Oleh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="movements.data.length === 0">
                                                <td colspan="8" class="text-center text-muted py-4">Belum ada riwayat pergerakan stok.</td>
                                            </tr>
                                            <tr v-for="mov in movements.data" :key="mov.id">
                                                <td class="text-nowrap">{{ mov.created_at }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ mov.item?.name }}</div>
                                                    <div class="text-muted small">{{ mov.item?.code }}</div>
                                                </td>
                                                <td>
                                                    <span v-if="mov.type === 'in'" class="badge bg-success">⬇ Masuk</span>
                                                    <span v-else class="badge bg-danger">⬆ Keluar</span>
                                                </td>
                                                <td class="fw-bold">{{ mov.qty }}</td>
                                                <td>{{ mov.stock_before ?? '-' }}</td>
                                                <td>{{ mov.balance_after ?? '-' }}</td>
                                                <td>{{ mov.notes ?? '-' }}</td>
                                               <td>{{ mov.pic?.name ?? mov.user?.name ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <nav v-if="movements.last_page > 1">
                                        <ul class="pagination pagination-sm mb-0">
                                            <li v-for="link in movements.links" :key="link.label"
                                                class="page-item" :class="{ active: link.active, disabled: !link.url }">
                                                <Link class="page-link" :href="link.url ?? '#'" v-html="link.label" />
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, router } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        movements: Object,
        departments: Array,
        items: Array,
        categories: Array,
        filters: Object,
    },
    computed: {
        filteredItems() {
            if (!this.filterForm.department_id) return this.items;
            return this.items.filter(item =>
                (item.department_ids ?? []).includes(this.filterForm.department_id)
            );
        },
        downloadAllUrl() {
            const params = new URLSearchParams();
            Object.entries(this.filterForm).forEach(([key, val]) => {
                if (val) params.append(key, val);
            });
            const qs = params.toString();
            return `/apps/stock-movements/download-all/pdf${qs ? '?' + qs : ''}`;
        },
        exportExcelUrl() {
            const params = new URLSearchParams();
            Object.entries(this.filterForm).forEach(([key, val]) => {
                if (val) params.append(key, val);
            });
            const qs = params.toString();
            return `/apps/stock-movements/export/excel${qs ? '?' + qs : ''}`;
        },
    },
    data() {
        return {
            filterForm: {
                department_id: this.filters?.department_id ?? '',
                item_id: this.filters?.item_id ?? '',
                type: this.filters?.type ?? '',
                date_from: this.filters?.date_from ?? '',
                date_to: this.filters?.date_to ?? '',
            }
        };
    },
    methods: {
        onDepartmentChange() {
            // Reset material setiap kali department berubah,
            // karena daftar yang tersedia berubah total.
            this.filterForm.item_id = '';
        },
        applyFilter() {
            router.get('/apps/stock-movements', this.filterForm, { preserveState: true });
        },
        resetFilter() {
            this.filterForm = { department_id: '', item_id: '', type: '', date_from: '', date_to: '' };
            router.get('/apps/stock-movements');
        },
    }
}
</script>