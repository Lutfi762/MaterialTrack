<template>
    <Head><title>{{ $t('stock_in.add') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-success">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-arrow-down-left me-1"></i> {{ $t('stock_in.heading') }}</span>
                                <Link href="/apps/stock-ins" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold" for="item-select">{{ $t('stock_in.material') }} <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-2">
                                                <select id="item-select" class="form-select" v-model="form.item_id" :class="{ 'is-invalid': form.errors.item_id }" required>
                                                    <option :value="null" disabled>{{ $t('stock_in.material_placeholder') }}</option>
                                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                                        {{ item.name }} ({{ item.code }}) — {{ $t('stock_in.current_stock') }} {{ item.current_stock }} {{ item.unit }}
                                                    </option>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary" @click="showScanner = true" title="Scan QR Code" style="white-space:nowrap;">
                                                    <i class="fa fa-camera me-1"></i> Scan
                                                </button>
                                            </div>
                                            <div v-if="form.errors.item_id" class="invalid-feedback d-block">{{ form.errors.item_id }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="fw-bold" for="qty-input">{{ $t('stock_in.qty_in') }} <span class="text-danger">*</span></label>
                                            <input id="qty-input" class="form-control" v-model.number="form.qty" :class="{ 'is-invalid': form.errors.qty }" type="number" min="1" required>
                                            <div v-if="form.errors.qty" class="invalid-feedback d-block">{{ form.errors.qty }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3" v-if="selectedItem?.unit_alt">
                                            <label class="fw-bold">{{ $t('stock_in.unit') }}</label>
                                            <select class="form-select" v-model="form.unit_mode">
                                                <option value="base">{{ selectedItem.unit }} ({{ $t('stock_in.unit_base') }})</option>
                                                <option value="alt">{{ selectedItem.unit_alt }} (1 = {{ selectedItem.conversion_factor }} {{ selectedItem.unit }})</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.department') }}</label>
                                            <select class="form-select" v-model="form.department_id">
                                                <option :value="null">{{ $t('stock_in.department_none') }}</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.supplier') }}</label>
                                            <select class="form-select" v-model="form.supplier_id">
                                                <option :value="null">{{ $t('stock_in.supplier_none') }}</option>
                                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }} <template v-if="s.code">({{ s.code }})</template></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.po_number') }}</label>
                                            <input class="form-control" v-model="form.po_number" type="text" :placeholder="$t('stock_in.po_number_placeholder')">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.source') }}</label>
                                            <input class="form-control" v-model="form.source" type="text" :placeholder="$t('stock_in.source_placeholder')">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_in.additional_notes') }}</label>
                                        <textarea class="form-control" v-model="form.notes" rows="2"></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.date') }} <span class="text-danger">*</span></label>
                                            <input class="form-control" v-model="form.date" :class="{ 'is-invalid': form.errors.date }" type="date" required>
                                            <div v-if="form.errors.date" class="invalid-feedback d-block">{{ form.errors.date }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.expiry_date') }}</label>
                                            <input class="form-control" v-model="form.expiry_date" :class="{ 'is-invalid': form.errors.expiry_date }" type="date">
                                            <div v-if="form.errors.expiry_date" class="invalid-feedback d-block">{{ form.errors.expiry_date }}</div>
                                            <small class="text-muted">{{ $t('stock_in.expiry_desc') }}</small>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                        <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                        <span v-else>{{ $t('common.save') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <ScannerModal :show="showScanner" @close="showScanner = false" @item-selected="onItemSelected" />
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';
import ScannerModal from '@/Components/ScannerModal.vue';

export default {
    layout: LayoutApp,
    components: { Head, Link, ScannerModal },
    props: { items: Array, departments: Array, suppliers: Array },
    setup(props) {
        const { t } = useI18n();
        const showScanner = ref(false);
        const form = useForm({
            item_id: null,
            qty: 1,
            unit_mode: 'base',
            department_id: null,
            supplier_id: null,
            po_number: '',
            source: '',
            notes: '',
            expiry_date: '',
            date: new Date().toISOString().slice(0, 10),
        });

        const selectedItem = computed(() =>
            props.items.find(i => i.id === form.item_id) ?? null
        );

        const submit = () => {
            // Convert qty if using alternate unit
            let finalQty = form.qty;
            if (form.unit_mode === 'alt' && selectedItem.value?.conversion_factor) {
                finalQty = Math.round(form.qty * selectedItem.value.conversion_factor);
            }

            form.transform((data) => ({
                ...data,
                qty: finalQty,
            })).post('/apps/stock-ins', {
                onSuccess: () => Swal.fire({ title: t('common.saved_success'), text: t('stock_in.success_msg'), icon: 'success', showConfirmButton: false, timer: 2000 }),
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

        const onItemSelected = (item) => {
            form.item_id = item.id;
        };

        return { form, selectedItem, submit, showScanner, onItemSelected };
    },
};
</script>

<style>
.border-top-success { border-top: 3px solid #198754 !important; }
</style>