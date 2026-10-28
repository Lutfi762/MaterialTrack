<template>
    <Head><title>{{ $t('stock_return.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-undo text-indigo-600" aria-hidden="true"></i> {{ $t('stock_return.heading') }}</span>
                    <Link href="/apps/stock-returns" class="mtx-btn mtx-btn-secondary mtx-btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}</Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label" for="item-select">{{ $t('stock_return.material') }} <span class="mtx-required">*</span></label>
                                <select id="item-select" class="mtx-select" v-model="form.item_id" :class="{ 'mtx-select-error': form.errors.item_id }" required>
                                    <option :value="null" disabled>{{ $t('stock_return.material_placeholder') }}</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        {{ item.name }} ({{ item.code }}) — {{ $t('stock_return.current_stock') }} {{ item.current_stock }} {{ item.unit }}
                                    </option>
                                </select>
                                <p v-if="form.errors.item_id" class="mtx-error-text">{{ form.errors.item_id }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label" for="qty-input">{{ $t('stock_return.qty') }} <span class="mtx-required">*</span></label>
                                <input id="qty-input" class="mtx-input" v-model.number="form.qty" :class="{ 'mtx-input-error': form.errors.qty }" type="number" min="1" :max="selectedItem?.current_stock || 9999" required>
                                <p v-if="form.errors.qty" class="mtx-error-text">{{ form.errors.qty }}</p>
                                <p v-if="selectedItem" class="mtx-hint">{{ $t('stock_return.available_stock') }}: {{ selectedItem.current_stock }} {{ selectedItem.unit }}</p>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_return.department') }}</label>
                            <select class="mtx-select" v-model="form.department_id">
                                <option :value="null">{{ $t('stock_return.department_none') }}</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_return.reason') }}</label>
                            <textarea class="mtx-textarea" v-model="form.reason" rows="3" :placeholder="$t('stock_return.reason_placeholder')"></textarea>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>{{ $t('stock_return.submit_return') }}</span>
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
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { items: Array, departments: Array, lockedDepartmentId: Number },
    setup(props) {
        const { t } = useI18n();
        const form = useForm({
            item_id: null,
            qty: 1,
            department_id: props.lockedDepartmentId ?? null,
            reason: '',
        });

        const selectedItem = computed(() =>
            props.items.find(i => i.id === form.item_id) ?? null
        );

        const submit = () => {
            if (selectedItem.value && form.qty > selectedItem.value.current_stock) {
                Swal.fire({
                    title: t('common.save_error'),
                    text: t('stock_return.qty_exceed_stock'),
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            form.post('/apps/stock-returns', {
                onSuccess: () => Swal.fire({ title: t('common.saved_success'), text: t('stock_return.success_msg'), icon: 'success', showConfirmButton: false, timer: 2000 }),
                onError: () => {
                    Swal.fire({
                        title: t('common.save_error'),
                        text: t('common.save_error_desc'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        };

        return { form, selectedItem, submit };
    },
};
</script>

