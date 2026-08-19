<template>
    <Head>
        <title>{{ $t('stock_out.heading') }} - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-danger">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-arrow-up-right-from-square me-1"></i> {{ $t('stock_out.heading') }}</span>
                                <Link href="/apps/stock-outs" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                                </Link>
                            </div>
                            <div class="card-body">

                                <form @submit.prevent="submit">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_out.department') }} <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select"
                                                v-model="form.department_id"
                                                :class="{ 'is-invalid': form.errors.department_id }"
                                                :disabled="isDepartmentLocked"
                                                @change="onDepartmentChange"
                                                required
                                            >
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                                    {{ dept.name }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.department_id" class="invalid-feedback d-block">{{ form.errors.department_id }}</div>
                                            <small v-if="isDepartmentLocked" class="text-muted">
                                                <i class="fa fa-lock me-1"></i> {{ $t('stock_out.department_auto') }}
                                            </small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_out.category') }}</label>
                                            <select
                                                class="form-select"
                                                v-model="selectedCategory"
                                                @change="onCategoryChange"
                                            >
                                                <option value="">{{ $t('stock_out.category_all') }}</option>
                                                <option v-for="cat in availableCategories" :key="cat.id" :value="cat.id">
                                                    {{ cat.name }}
                                                </option>
                                            </select>
                                            <small class="text-muted">{{ $t('stock_out.category_filter_desc') }}</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_out.material') }} <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-2">
                                                <select
                                                    class="form-select"
                                                    v-model="form.item_id"
                                                    :class="{ 'is-invalid': form.errors.item_id }"
                                                    @change="onItemChange"
                                                    required
                                                >
                                                    <option :value="null" disabled>{{ $t('stock_out.material_placeholder') }}</option>
                                                    <option v-for="item in filteredItems" :key="item.id" :value="item.id">
                                                        {{ item.name }} ({{ item.code }}) — {{ $t('stock_out.current_stock') }} {{ item.current_stock }} {{ item.unit }}
                                                    </option>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary" @click="showScanner = true" title="Scan QR Code" style="white-space:nowrap;">
                                                    <i class="fa fa-camera me-1"></i> Scan
                                                </button>
                                            </div>
                                            <div v-if="form.errors.item_id" class="invalid-feedback d-block">{{ form.errors.item_id }}</div>
                                            <small v-if="filteredItems.length === 0" class="text-danger">
                                                {{ $t('stock_out.no_items_for_department') }}
                                            </small>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="fw-bold">Quantity <span class="text-danger">*</span></label>
                                            <input
                                                class="form-control"
                                                v-model.number="form.qty"
                                                :class="{ 'is-invalid': form.errors.qty }"
                                                type="number"
                                                min="1"
                                                :max="maxQty"
                                                required
                                            >
                                            <div v-if="form.errors.qty" class="invalid-feedback d-block">{{ form.errors.qty }}</div>
                                            <small v-if="selectedItem" class="text-muted">
                                                {{ $t('stock_out.available_stock') }} {{ selectedItem.current_stock }} {{ selectedItem.unit }}
                                            </small>
                                        </div>

                                        <div class="col-md-3 mb-3" v-if="selectedItem?.unit_alt">
                                            <label class="fw-bold">Satuan</label>
                                            <select class="form-select" v-model="form.unit_mode">
                                                <option value="base">{{ selectedItem.unit }} (dasar)</option>
                                                <option value="alt">{{ selectedItem.unit_alt }} (1 = {{ selectedItem.conversion_factor }} {{ selectedItem.unit }})</option>
                                            </select>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_out.pic') }} <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select"
                                                v-model="form.pic_id"
                                                :class="{ 'is-invalid': form.errors.pic_id }"
                                                required
                                            >
                                                <option :value="null" disabled>{{ $t('stock_out.pic_placeholder') }}</option>
                                                <option v-for="pic in filteredPics" :key="pic.id" :value="pic.id">
                                                    {{ pic.name }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.pic_id" class="invalid-feedback d-block">{{ form.errors.pic_id }}</div>
                                            <small v-if="filteredPics.length === 0" class="text-danger">
                                                {{ $t('stock_out.pic_empty') }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_out.purpose') }} <span class="text-danger">*</span></label>
                                        <input
                                            class="form-control"
                                            v-model="form.purpose"
                                            :class="{ 'is-invalid': form.errors.purpose }"
                                            type="text"
                                            placeholder="Contoh: Maintenance Mesin A"
                                            required
                                        >
                                        <div v-if="form.errors.purpose" class="invalid-feedback d-block">{{ form.errors.purpose }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_out.additional_notes') }}</label>
                                        <textarea
                                            class="form-control"
                                            v-model="form.notes"
                                            rows="2"
                                        ></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_out.date_pickup') }} <span class="text-danger">*</span></label>
                                        <input
                                            class="form-control"
                                            :value="todayDate"
                                            :class="{ 'is-invalid': form.errors.date }"
                                            type="date"
                                            readonly
                                            disabled
                                        >
                                        <div v-if="form.errors.date" class="invalid-feedback d-block">{{ form.errors.date }}</div>
                                        <small class="text-muted">
                                            <i class="fa fa-lock me-1"></i> {{ $t('stock_out.date_pickup_desc') }}
                                        </small>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary shadow-sm rounded-sm"
                                                type="submit"
                                                :disabled="form.processing || filteredPics.length === 0"
                                            >
                                                <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                                <span v-else>{{ $t('common.save') }}</span>
                                            </button>
                                        </div>
                                    </div>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Swal from 'sweetalert2';
import ScannerModal from '@/Components/ScannerModal.vue';
import { useI18n } from 'vue-i18n';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
        ScannerModal,
    },

    props: {
        items: Array,
        departments: Array,
        pics: Array,
        lockedDepartmentId: [Number, null],
    },

    setup(props) {
        const { t } = useI18n();
        const isDepartmentLocked = props.lockedDepartmentId !== null;
        const todayDate = new Date().toISOString().slice(0, 10);
        const showScanner = ref(false);

        const form = useForm({
            item_id: null,
            qty: 1,
            unit_mode: 'base',
            department_id: isDepartmentLocked ? props.lockedDepartmentId : (props.departments[0]?.id ?? null),
            pic_id: null,
            purpose: '',
            notes: '',
            date: todayDate,
        });

        const selectedCategory = ref('');

        // Item yang kategorinya terkait dengan department yang sedang dipilih.
        const itemsInDepartment = computed(() => {
            if (!form.department_id) return props.items;
            const deptId = Number(form.department_id);
            return props.items.filter(item =>
                (item.department_ids ?? []).includes(deptId)
            );
        });

        // Daftar kategori unik dari item-item yang tersedia untuk department ini.
        const availableCategories = computed(() => {
            const map = new Map();
            itemsInDepartment.value.forEach(item => {
                if (item.category_id) {
                    map.set(item.category_id, { id: item.category_id, name: item.category_name });
                }
            });
            return Array.from(map.values());
        });

        // Item final: sudah disaring department, lalu disaring lagi kategori (kalau dipilih).
        const filteredItems = computed(() => {
            if (!selectedCategory.value) return itemsInDepartment.value;
            return itemsInDepartment.value.filter(item => item.category_id === selectedCategory.value);
        });

        const selectedItem = computed(() =>
            props.items.find(i => i.id === form.item_id) ?? null
        );

        const maxQty = computed(() => {
            if (!selectedItem.value) return null;
            if (form.unit_mode === 'alt' && selectedItem.value.conversion_factor) {
                return Math.floor(selectedItem.value.current_stock / selectedItem.value.conversion_factor);
            }
            return selectedItem.value.current_stock;
        });

        const filteredPics = computed(() =>
            props.pics.filter(p => p.department_id === form.department_id)
        );

        const onDepartmentChange = () => {
            // Reset kategori & material setiap kali department berubah,
            // karena daftar yang tersedia berubah total.
            selectedCategory.value = '';
            form.item_id = null;
            form.pic_id = null;
        };

        const onCategoryChange = () => {
            form.item_id = null;
        };

        const onItemChange = () => {
            form.qty = 1;
            form.unit_mode = 'base';
        };

        const onItemSelected = (item) => {
            // Check if item is in the filtered list for current department/category
            const match = filteredItems.value.find(i => i.id === item.id);
            if (match) {
                form.item_id = item.id;
                onItemChange();
            } else {
                Swal.fire({
                    title: t('stock_out.material_mismatch'),
                    text: t('stock_out.material_mismatch_desc', { code: item.code }),
                    icon: 'warning',
                    confirmButtonText: 'OK',
                });
            }
        };

        const submit = () => {
            // Ensure date is always today's date before submitting
            form.date = todayDate;

            // Convert qty if using alternate unit
            let finalQty = form.qty;
            if (form.unit_mode === 'alt' && selectedItem.value?.conversion_factor) {
                finalQty = Math.round(form.qty * selectedItem.value.conversion_factor);
            }

            form.transform((data) => ({
                ...data,
                qty: finalQty,
            })).post('/apps/stock-outs', {
                onSuccess: () => {
                    const page = usePage();
                    const newId = page.props.newStockOutId;

                    Swal.fire({
                        title: t('common.success'),
                        text: newId
                            ? t('stock_out.save_success_pdf')
                            : t('stock_out.save_success'),
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                    });

                    if (newId) {
                        window.open(`/apps/stock-outs/${newId}/pdf`, '_blank');
                    }
                },
                onError: () => {
                    Swal.fire({
                        title: t('common.error'),
                        text: t('stock_out.save_error'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        };

        return {
            form,
            todayDate,
            selectedCategory,
            availableCategories,
            filteredItems,
            selectedItem,
            maxQty,
            filteredPics,
            isDepartmentLocked,
            onDepartmentChange,
            onCategoryChange,
            onItemChange,
            submit,
            showScanner,
            onItemSelected,
        };
    },
}
</script>

<style>
.border-top-danger {
    border-top: 3px solid #dc3545 !important;
}
</style>