<template>
    <Head><title>{{ $t('stock_return.add') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-warning">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-undo me-1"></i> {{ $t('stock_return.heading') }}</span>
                                <Link href="/apps/stock-returns" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold" for="item-select">{{ $t('stock_return.material') }} <span class="text-danger">*</span></label>
                                            <select id="item-select" class="form-select" v-model="form.item_id" :class="{ 'is-invalid': form.errors.item_id }" required>
                                                <option :value="null" disabled>{{ $t('stock_return.material_placeholder') }}</option>
                                                <option v-for="item in items" :key="item.id" :value="item.id">
                                                    {{ item.name }} ({{ item.code }}) — {{ $t('stock_return.current_stock') }} {{ item.current_stock }} {{ item.unit }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.item_id" class="invalid-feedback d-block">{{ form.errors.item_id }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="fw-bold" for="qty-input">{{ $t('stock_return.qty') }} <span class="text-danger">*</span></label>
                                            <input id="qty-input" class="form-control" v-model.number="form.qty" :class="{ 'is-invalid': form.errors.qty }" type="number" min="1" :max="selectedItem?.current_stock || 9999" required>
                                            <div v-if="form.errors.qty" class="invalid-feedback d-block">{{ form.errors.qty }}</div>
                                            <small v-if="selectedItem" class="text-muted">{{ $t('stock_return.available_stock') }}: {{ selectedItem.current_stock }} {{ selectedItem.unit }}</small>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="fw-bold">{{ $t('stock_return.department') }}</label>
                                            <select class="form-select" v-model="form.department_id">
                                                <option :value="null">{{ $t('stock_return.department_none') }}</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_return.reason') }}</label>
                                        <textarea class="form-control" v-model="form.reason" rows="3" :placeholder="$t('stock_return.reason_placeholder')"></textarea>
                                    </div>

                                    <button class="btn btn-warning shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                        <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                        <span v-else>{{ $t('stock_return.submit_return') }}</span>
                                    </button>
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

<style>
.border-top-warning { border-top: 3px solid #f59e0b !important; }
</style>
