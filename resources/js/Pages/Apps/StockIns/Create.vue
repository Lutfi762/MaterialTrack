<template>
    <Head><title>{{ $t('stock_in.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-arrow-down-left text-indigo-600" aria-hidden="true"></i> {{ $t('stock_in.heading') }}</span>
                    <Link href="/apps/stock-ins" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4">
                            <div class="mtx-field md:col-span-2">
                                <label class="mtx-label" for="item-select">{{ $t('stock_in.material') }} <span class="mtx-required">*</span></label>
                                <div class="flex gap-2">
                                    <select id="item-select" class="mtx-select" v-model="form.item_id" :class="{ 'mtx-select-error': form.errors.item_id }" required>
                                        <option :value="null" disabled>{{ $t('stock_in.material_placeholder') }}</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.name }} ({{ item.code }}) — {{ $t('stock_in.current_stock') }} {{ item.current_stock }} {{ item.unit }}
                                        </option>
                                    </select>
                                    <button type="button" class="mtx-btn mtx-btn-secondary" @click="showScanner = true" title="Scan QR Code">
                                        <i class="fa fa-camera" aria-hidden="true"></i> Scan
                                    </button>
                                </div>
                                <p v-if="form.errors.item_id" class="mtx-error-text">{{ form.errors.item_id }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label" for="qty-input">{{ $t('stock_in.qty_in') }} <span class="mtx-required">*</span></label>
                                <input id="qty-input" class="mtx-input" v-model.number="form.qty" :class="{ 'mtx-input-error': form.errors.qty }" type="number" min="1" required>
                                <p v-if="form.errors.qty" class="mtx-error-text">{{ form.errors.qty }}</p>
                            </div>
                        </div>
                        <div class="mtx-field" v-if="selectedItem?.unit_alt">
                            <label class="mtx-label">{{ $t('stock_in.unit') }}</label>
                            <select class="mtx-select" v-model="form.unit_mode">
                                <option value="base">{{ selectedItem.unit }} ({{ $t('stock_in.unit_base') }})</option>
                                <option value="alt">{{ selectedItem.unit_alt }} (1 = {{ selectedItem.conversion_factor }} {{ selectedItem.unit }})</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.department') }}</label>
                                <select class="mtx-select" v-model="form.department_id">
                                    <option :value="null">{{ $t('stock_in.department_none') }}</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.supplier') }}</label>
                                <select class="mtx-select" v-model="form.supplier_id">
                                    <option :value="null">{{ $t('stock_in.supplier_none') }}</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }} <template v-if="s.code">({{ s.code }})</template></option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.po_number') }}</label>
                                <input class="mtx-input" v-model="form.po_number" type="text" :placeholder="$t('stock_in.po_number_placeholder')">
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.source') }}</label>
                                <input class="mtx-input" v-model="form.source" type="text" :placeholder="$t('stock_in.source_placeholder')">
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_in.additional_notes') }}</label>
                            <textarea class="mtx-textarea" v-model="form.notes" rows="2"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.date') }} <span class="mtx-required">*</span></label>
                                <input class="mtx-input" v-model="form.date" :class="{ 'mtx-input-error': form.errors.date }" type="date" required>
                                <p v-if="form.errors.date" class="mtx-error-text">{{ form.errors.date }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.expiry_date') }}</label>
                                <input class="mtx-input" v-model="form.expiry_date" :class="{ 'mtx-input-error': form.errors.expiry_date }" type="date">
                                <p v-if="form.errors.expiry_date" class="mtx-error-text">{{ form.errors.expiry_date }}</p>
                                <p class="mtx-hint">{{ $t('stock_in.expiry_desc') }}</p>
                            </div>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>{{ $t('common.save') }}</span>
                        </button>
                    </form>
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