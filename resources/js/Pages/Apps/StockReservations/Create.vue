<template>
    <div>
        <Head :title="$t('stock_reservation.add')" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('stock_reservation.add') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('stock_reservation.heading'), url: '/apps/stock-reservations' }, { label: $t('stock_reservation.add') }]" />
            </div>
            <Link href="/apps/stock-reservations" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
            </Link>
        </div>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ $t('stock_reservation.material') }} <span class="text-danger">*</span></label>
                            <select class="form-select" v-model="form.item_id" required>
                                <option value="">{{ $t('stock_reservation.material_placeholder') }}</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">
                                    [{{ item.code }}] {{ item.name }} (Stok: {{ item.current_stock }})
                                </option>
                            </select>
                            <div v-if="selectedItem" class="mt-1">
                                <small class="text-muted">
                                    {{ $t('stock_reservation.stock_available') }}: {{ availableStock }}
                                    ({{ $t('stock_reservation.stock_reserved') }}: {{ reservedStock }})
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">{{ $t('stock_reservation.qty') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" v-model="form.qty" min="1" :max="availableStock" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">{{ $t('stock_reservation.reserved_until') }}</label>
                            <input type="date" class="form-control" v-model="form.reserved_until" :min="tomorrow">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ $t('stock_reservation.project_name') }}</label>
                            <input type="text" class="form-control" v-model="form.project_name" :placeholder="$t('stock_reservation.project_placeholder')">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ $t('stock_reservation.department') }}</label>
                            <select class="form-select" v-model="form.department_id">
                                <option :value="null">{{ $t('stock_reservation.dept_none') }}</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ $t('stock_reservation.purpose') }}</label>
                        <textarea class="form-control" v-model="form.purpose" rows="2" :placeholder="$t('stock_reservation.purpose_placeholder')"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <i class="fa fa-bookmark me-1"></i>
                            {{ form.processing ? $t('common.saving') : $t('stock_reservation.save') }}
                        </button>
                        <Link href="/apps/stock-reservations" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
