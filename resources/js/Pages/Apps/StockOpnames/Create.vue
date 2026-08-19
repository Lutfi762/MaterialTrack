<template>
    <Head><title>Input Stock Opname - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-clipboard-check me-1"></i> {{ $t('stock_opname.add') }}</span>
                            <Link :href="`/apps/stock-opnames?period=${form.period}`" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                            </Link>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">{{ $t('stock_opname.period') }}</label>
                                    <input v-model="form.period" type="month" class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ $t('stock_opname.date') }}</label>
                                    <input v-model="form.date" type="date" class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ $t('stock_opname.filter_category') }}</label>
                                    <select v-model="selectedCategory" class="form-select">
                                        <option value="">{{ $t('stock_opname.filter_all') }}</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Material</th>
                                            <th>Kategori</th>
                                            <th class="text-end">{{ $t('stock_opname.col_system_stock') }}</th>
                                            <th style="width: 150px;">{{ $t('stock_opname.col_physical_stock') }}</th>
                                            <th class="text-center" style="width: 100px;">{{ $t('stock_opname.col_diff') }}</th>
                                            <th>{{ $t('stock_opname.col_notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in filteredItems" :key="row.item.id" :class="{ 'table-secondary': alreadyDone.includes(row.item.id) }">
                                            <td>{{ row.item.code }}</td>
                                            <td>{{ row.item.name }}</td>
                                            <td>{{ row.item.category?.name ?? '-' }}</td>
                                            <td class="text-end">{{ row.item.current_stock }} {{ row.item.unit }}</td>
                                            <td>
                                                <input
                                                    v-if="!alreadyDone.includes(row.item.id)"
                                                    v-model.number="row.physical_stock"
                                                    type="number" min="0"
                                                    class="form-control form-control-sm"
                                                />
                                                <span v-else class="text-muted small">{{ $t('stock_opname.already_opnamed') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span v-if="!alreadyDone.includes(row.item.id)"
                                                      :class="diffClass(row)">
                                                    {{ diffLabel(row) }}
                                                </span>
                                            </td>
                                            <td>
                                                <input
                                                    v-if="!alreadyDone.includes(row.item.id)"
                                                    v-model="row.notes"
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    :placeholder="$t('common.optional')"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button @click="submit" class="btn btn-primary" :disabled="submitting">
                                <i class="fa fa-save me-1"></i> {{ $t('stock_opname.save_opname') }}
                            </button>
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
        items: Array,
        period: String,
        alreadyDone: Array,
    },
    data() {
        return {
            form: {
                period: this.period,
                date: new Date().toISOString().slice(0, 10),
            },
            selectedCategory: '',
            rows: this.items.map(item => ({
                item,
                physical_stock: item.current_stock,
                notes: '',
            })),
            submitting: false,
        };
    },
    computed: {
        categories() {
            const map = new Map();
            this.items.forEach(i => {
                if (i.category) map.set(i.category.id, i.category);
            });
            return Array.from(map.values());
        },
        filteredItems() {
            if (!this.selectedCategory) return this.rows;
            return this.rows.filter(r => r.item.category_id === this.selectedCategory);
        },
    },
    methods: {
        diff(row) {
            return (row.physical_stock ?? 0) - row.item.current_stock;
        },
        diffLabel(row) {
            const d = this.diff(row);
            if (d === 0) return this.$t('stock_opname.diff_match');
            return d > 0 ? `+${d}` : `${d}`;
        },
        diffClass(row) {
            const d = this.diff(row);
            if (d === 0) return 'badge bg-success';
            return d > 0 ? 'badge bg-info' : 'badge bg-danger';
        },
        submit() {
            const entries = this.rows
                .filter(r => !this.alreadyDone.includes(r.item.id))
                .map(r => ({
                    item_id: r.item.id,
                    physical_stock: r.physical_stock,
                    notes: r.notes || null,
                }));

            if (entries.length === 0) {
                alert(this.$t('stock_opname.no_items_save'));
                return;
            }

            this.submitting = true;
            router.post('/apps/stock-opnames', {
                period: this.form.period,
                date: this.form.date,
                entries,
            }, {
                onFinish: () => { this.submitting = false; },
            });
        },
    },
};
</script>