<template>
    <main class="p-6">
        <Head :title="$t('stock_reservation.add')" />
        <div class="max-w-3xl mx-auto">
            <div class="mtx-page-header">
                <div>
                    <h1 class="mtx-page-title">{{ $t('stock_reservation.add') }}</h1>
                    <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('stock_reservation.heading'), url: '/apps/stock-reservations' }, { label: $t('stock_reservation.add') }]" />
                </div>
                <Link href="/apps/stock-reservations" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                </Link>
            </div>

            <div class="mtx-card">
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_reservation.material') }} <span class="mtx-required">*</span></label>
                                <select class="mtx-select" v-model="form.item_id" required>
                                    <option value="">{{ $t('stock_reservation.material_placeholder') }}</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        [{{ item.code }}] {{ item.name }} (Stok: {{ item.current_stock }})
                                    </option>
                                </select>
                                <p v-if="selectedItem" class="mtx-hint">
                                    {{ $t('stock_reservation.stock_available') }}: {{ availableStock }}
                                    ({{ $t('stock_reservation.stock_reserved') }}: {{ reservedStock }})
                                </p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_reservation.qty') }} <span class="mtx-required">*</span></label>
                                <input type="number" class="mtx-input" v-model="form.qty" min="1" :max="availableStock" required>
                            </div>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_reservation.reserved_until') }}</label>
                            <input type="date" class="mtx-input" v-model="form.reserved_until" :min="tomorrow">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_reservation.project_name') }}</label>
                                <input type="text" class="mtx-input" v-model="form.project_name" :placeholder="$t('stock_reservation.project_placeholder')">
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_reservation.department') }}</label>
                                <select class="mtx-select" v-model="form.department_id">
                                    <option :value="null">{{ $t('stock_reservation.dept_none') }}</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_reservation.purpose') }}</label>
                            <textarea class="mtx-textarea" v-model="form.purpose" rows="2" :placeholder="$t('stock_reservation.purpose_placeholder')"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">
                                <i class="fa fa-bookmark" aria-hidden="true"></i>
                                {{ form.processing ? $t('common.saving') : $t('stock_reservation.save') }}
                            </button>
                            <Link href="/apps/stock-reservations" class="mtx-btn mtx-btn-secondary">{{ $t('common.cancel') }}</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';

export default {
    layout: LayoutApp,
    components: { Head, Link, Breadcrumb },
    props: {
        items: Array,
        departments: Array,
        user_department_id: [Number, null],
    },
    data() {
        return {
            form: useForm({
                item_id: '',
                qty: 1,
                purpose: '',
                project_name: '',
                department_id: this.user_department_id,
                reserved_until: '',
            }),
            reservedStocks: {},
        };
    },
    computed: {
        selectedItem() {
            return this.items.find(i => i.id == this.form.item_id);
        },
        availableStock() {
            if (!this.selectedItem) return 0;
            return this.selectedItem.current_stock - (this.reservedStocks[this.form.item_id] || 0);
        },
        reservedStock() {
            return this.reservedStocks[this.form.item_id] || 0;
        },
        tomorrow() {
            return new Date(Date.now() + 86400000).toISOString().split('T')[0];
        },
    },
    methods: {
        submit() {
            this.form.post('/apps/stock-reservations');
        },
    },
};
</script>
