<template>
    <Head>
        <title>{{ $t('stock_transfer.add') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-exchange-alt text-indigo-600" aria-hidden="true"></i> {{ $t('stock_transfer.heading') }}</span>
                    <Link href="/apps/stock-transfers" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_transfer.material') }} <span class="mtx-required">*</span></label>
                            <select class="mtx-select" :class="{ 'mtx-select-error': form.errors.item_id }" v-model="form.item_id" required>
                                <option :value="null" disabled>{{ $t('stock_transfer.material_placeholder') }}</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">
                                    {{ item.name }} ({{ item.code }}) — {{ $t('stock_transfer.stock_label') }} {{ item.current_stock }} {{ item.unit }}
                                </option>
                            </select>
                            <p v-if="form.errors.item_id" class="mtx-error-text">{{ form.errors.item_id }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_transfer.from_dept') }} <span class="mtx-required">*</span></label>
                                <select class="mtx-select" :class="{ 'mtx-select-error': form.errors.from_department_id }" v-model="form.from_department_id" required>
                                    <option :value="null" disabled>{{ $t('stock_transfer.from_placeholder') }}</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <p v-if="form.errors.from_department_id" class="mtx-error-text">{{ form.errors.from_department_id }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_transfer.to_dept') }} <span class="mtx-required">*</span></label>
                                <select class="mtx-select" :class="{ 'mtx-select-error': form.errors.to_department_id }" v-model="form.to_department_id" required>
                                    <option :value="null" disabled>{{ $t('stock_transfer.to_placeholder') }}</option>
                                    <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <p v-if="form.errors.to_department_id" class="mtx-error-text">{{ form.errors.to_department_id }}</p>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">Quantity <span class="mtx-required">*</span></label>
                            <input type="number" class="mtx-input" :class="{ 'mtx-input-error': form.errors.qty }" v-model="form.qty" min="1" :max="selectedItem?.current_stock" required />
                            <p v-if="form.errors.qty" class="mtx-error-text">{{ form.errors.qty }}</p>
                            <p v-if="selectedItem" class="mtx-hint">{{ $t('stock_transfer.stock_remaining') }} {{ selectedItem.current_stock }} {{ selectedItem.unit }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_transfer.notes') }}</label>
                            <textarea class="mtx-textarea" v-model="form.notes" rows="3" :placeholder="$t('stock_transfer.notes_placeholder')"></textarea>
                        </div>

                        <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                            <span v-if="form.processing">{{ $t('stock_transfer.sending') }}</span>
                            <span v-else>{{ $t('stock_transfer.add') }}</span>
                        </button>
                    </form>
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
