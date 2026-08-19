<template>
    <Head>
        <title>{{ $t('stock_transfer.add') }} - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fa fa-exchange-alt me-1"></i> {{ $t('stock_transfer.heading') }}
                                </h6>
                                <Link href="/apps/stock-transfers" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                                </Link>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ $t('stock_transfer.material') }} <span class="text-danger">*</span></label>
                                        <select class="form-select" :class="{ 'is-invalid': form.errors.item_id }" v-model="form.item_id" required>
                                            <option :value="null" disabled>{{ $t('stock_transfer.material_placeholder') }}</option>
                                            <option v-for="item in items" :key="item.id" :value="item.id">
                                                {{ item.name }} ({{ item.code }}) — {{ $t('stock_transfer.stock_label') }} {{ item.current_stock }} {{ item.unit }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.item_id" class="invalid-feedback">{{ form.errors.item_id }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">{{ $t('stock_transfer.from_dept') }} <span class="text-danger">*</span></label>
                                            <select class="form-select" :class="{ 'is-invalid': form.errors.from_department_id }" v-model="form.from_department_id" required>
                                                <option :value="null" disabled>{{ $t('stock_transfer.from_placeholder') }}</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                            <div v-if="form.errors.from_department_id" class="invalid-feedback">{{ form.errors.from_department_id }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">{{ $t('stock_transfer.to_dept') }} <span class="text-danger">*</span></label>
                                            <select class="form-select" :class="{ 'is-invalid': form.errors.to_department_id }" v-model="form.to_department_id" required>
                                                <option :value="null" disabled>{{ $t('stock_transfer.to_placeholder') }}</option>
                                                <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                            <div v-if="form.errors.to_department_id" class="invalid-feedback">{{ form.errors.to_department_id }}</div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" :class="{ 'is-invalid': form.errors.qty }" v-model="form.qty" min="1" :max="selectedItem?.current_stock" required />
                                            <div v-if="form.errors.qty" class="invalid-feedback">{{ form.errors.qty }}</div>
                                            <small v-if="selectedItem" class="text-muted">{{ $t('stock_transfer.stock_remaining') }} {{ selectedItem.current_stock }} {{ selectedItem.unit }}</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ $t('stock_transfer.notes') }}</label>
                                        <textarea class="form-control" v-model="form.notes" rows="3" :placeholder="$t('stock_transfer.notes_placeholder')"></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                            <i class="fa fa-paper-plane me-1"></i>
                                            <span v-if="form.processing">{{ $t('stock_transfer.sending') }}</span>
                                            <span v-else>{{ $t('stock_transfer.add') }}</span>
                                        </button>
                                    </div>
                                </form>
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        items: Array,
        departments: Array,
        userDepartment: Number,
    },
    setup(props) {
        const form = useForm({
            item_id: null,
            from_department_id: props.userDepartment,
            to_department_id: null,
            qty: 1,
            notes: '',
        });

        const selectedItem = computed(() =>
            props.items.find(i => i.id === form.item_id) ?? null
        );

        const filteredDepartments = computed(() =>
            props.departments.filter(d => d.id !== form.from_department_id)
        );

        const submit = () => {
            form.post('/apps/stock-transfers');
        };

        return { form, selectedItem, filteredDepartments, submit };
    },
};
</script>
