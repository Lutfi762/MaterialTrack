<template>
    <Head><title>Input Stock Opname - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-clipboard-check text-indigo-600" aria-hidden="true"></i> {{ $t('stock_opname.add') }}</span>
                    <Link :href="`/apps/stock-opnames?period=${form.period}`" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 mb-4">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_opname.period') }}</label>
                            <input v-model="form.period" type="month" class="mtx-input" />
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_opname.date') }}</label>
                            <input v-model="form.date" type="date" class="mtx-input" />
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_opname.filter_category') }}</label>
                            <select v-model="selectedCategory" class="mtx-select">
                                <option value="">{{ $t('stock_opname.filter_all') }}</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm align-middle divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr class="border-b border-gray-200">
                                    <th class="px-3 py-2.5 text-left font-semibold">Kode</th>
                                    <th class="px-3 py-2.5 text-left font-semibold">Nama Material</th>
                                    <th class="px-3 py-2.5 text-left font-semibold">Kategori</th>
                                    <th class="px-3 py-2.5 text-right font-semibold">{{ $t('stock_opname.col_system_stock') }}</th>
                                    <th class="px-3 py-2.5 text-left font-semibold w-[150px]">{{ $t('stock_opname.col_physical_stock') }}</th>
                                    <th class="px-3 py-2.5 text-center font-semibold w-[110px]">{{ $t('stock_opname.col_diff') }}</th>
                                    <th class="px-3 py-2.5 text-left font-semibold">{{ $t('stock_opname.col_notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="row in filteredItems" :key="row.item.id" :class="{ 'bg-gray-50': alreadyDone.includes(row.item.id) }">
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ row.item.code }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ row.item.name }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ row.item.category?.name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">{{ row.item.current_stock }} {{ row.item.unit }}</td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-if="!alreadyDone.includes(row.item.id)"
                                            v-model.number="row.physical_stock"
                                            type="number" min="0"
                                            class="mtx-input"
                                        />
                                        <span v-else class="text-xs text-gray-400">{{ $t('stock_opname.already_opnamed') }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span v-if="!alreadyDone.includes(row.item.id)"
                                              class="mtx-badge"
                                              :class="diffClass(row)">
                                            {{ diffLabel(row) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-if="!alreadyDone.includes(row.item.id)"
                                            v-model="row.notes"
                                            type="text"
                                            class="mtx-input"
                                            :placeholder="$t('common.optional')"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button @click="submit" class="mtx-btn mtx-btn-primary mt-4" :disabled="submitting">
                        <i class="fa fa-save" aria-hidden="true"></i> {{ $t('stock_opname.save_opname') }}
                    </button>
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
            if (d === 0) return 'mtx-badge-success';
            return d > 0 ? 'mtx-badge-info' : 'mtx-badge-danger';
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