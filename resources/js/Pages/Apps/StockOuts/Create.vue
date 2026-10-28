<template>
    <Head>
        <title>{{ $t('stock_out.heading') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-arrow-up-right-from-square text-indigo-600" aria-hidden="true"></i> {{ $t('stock_out.heading') }}</span>
                    <Link href="/apps/stock-outs" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">

                    <form @submit.prevent="submit">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_out.department') }} <span class="mtx-required">*</span></label>
                                <select
                                    class="mtx-select"
                                    v-model="form.department_id"
                                    :class="{ 'mtx-select-error': form.errors.department_id }"
                                    :disabled="isDepartmentLocked"
                                    @change="onDepartmentChange"
                                    required
                                >
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.department_id" class="mtx-error-text">{{ form.errors.department_id }}</p>
                                <p v-if="isDepartmentLocked" class="mtx-hint">
                                    <i class="fa fa-lock" aria-hidden="true"></i> {{ $t('stock_out.department_auto') }}
                                </p>
                            </div>

                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_out.category') }}</label>
                                <select
                                    class="mtx-select"
                                    v-model="selectedCategory"
                                    @change="onCategoryChange"
                                >
                                    <option value="">{{ $t('stock_out.category_all') }}</option>
                                    <option v-for="cat in availableCategories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <p class="mtx-hint">{{ $t('stock_out.category_filter_desc') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_out.material') }} <span class="mtx-required">*</span></label>
                                <div class="flex gap-2">
                                    <select
                                        class="mtx-select"
                                        v-model="form.item_id"
                                        :class="{ 'mtx-select-error': form.errors.item_id }"
                                        @change="onItemChange"
                                        required
                                    >
                                        <option :value="null" disabled>{{ $t('stock_out.material_placeholder') }}</option>
                                        <option v-for="item in filteredItems" :key="item.id" :value="item.id">
                                            {{ item.name }} ({{ item.code }}) — {{ $t('stock_out.current_stock') }} {{ item.current_stock }} {{ item.unit }}
                                        </option>
                                    </select>
                                    <button type="button" class="mtx-btn mtx-btn-secondary" @click="showScanner = true" title="Scan QR Code" style="white-space:nowrap;">
                                        <i class="fa fa-camera" aria-hidden="true"></i> Scan
                                    </button>
                                </div>
                                <p v-if="form.errors.item_id" class="mtx-error-text">{{ form.errors.item_id }}</p>
                                <p v-if="filteredItems.length === 0" class="mtx-error-text">
                                    {{ $t('stock_out.no_items_for_department') }}
                                </p>
                            </div>

                            <div class="mtx-field">
                                <label class="mtx-label">Quantity <span class="mtx-required">*</span></label>
                                <input
                                    class="mtx-input"
                                    v-model.number="form.qty"
                                    :class="{ 'mtx-input-error': form.errors.qty }"
                                    type="number"
                                    min="1"
                                    :max="maxQty"
                                    required
                                >
                                <p v-if="form.errors.qty" class="mtx-error-text">{{ form.errors.qty }}</p>
                                <p v-if="selectedItem" class="mtx-hint">
                                    {{ $t('stock_out.available_stock') }} {{ selectedItem.current_stock }} {{ selectedItem.unit }}
                                </p>
                            </div>

                            <div class="mtx-field" v-if="selectedItem?.unit_alt">
                                <label class="mtx-label">Satuan</label>
                                <select class="mtx-select" v-model="form.unit_mode">
                                    <option value="base">{{ selectedItem.unit }} (dasar)</option>
                                    <option value="alt">{{ selectedItem.unit_alt }} (1 = {{ selectedItem.conversion_factor }} {{ selectedItem.unit }})</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-gray-200 mb-4">

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_out.pic') }} <span class="mtx-required">*</span></label>
                            <select
                                class="mtx-select"
                                v-model="form.pic_id"
                                :class="{ 'mtx-select-error': form.errors.pic_id }"
                                required
                            >
                                <option :value="null" disabled>{{ $t('stock_out.pic_placeholder') }}</option>
                                <option v-for="pic in filteredPics" :key="pic.id" :value="pic.id">
                                    {{ pic.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.pic_id" class="mtx-error-text">{{ form.errors.pic_id }}</p>
                            <p v-if="filteredPics.length === 0" class="mtx-error-text">
                                {{ $t('stock_out.pic_empty') }}
                            </p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_out.purpose') }} <span class="mtx-required">*</span></label>
                            <input
                                class="mtx-input"
                                v-model="form.purpose"
                                :class="{ 'mtx-input-error': form.errors.purpose }"
                                type="text"
                                placeholder="Contoh: Maintenance Mesin A"
                                required
                            >
                            <p v-if="form.errors.purpose" class="mtx-error-text">{{ form.errors.purpose }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_out.additional_notes') }}</label>
                            <textarea
                                class="mtx-textarea"
                                v-model="form.notes"
                                rows="2"
                            ></textarea>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_out.date_pickup') }} <span class="mtx-required">*</span></label>
                            <input
                                class="mtx-input"
                                :value="todayDate"
                                :class="{ 'mtx-input-error': form.errors.date }"
                                type="date"
                                readonly
                                disabled
                            >
                            <p v-if="form.errors.date" class="mtx-error-text">{{ form.errors.date }}</p>
                            <p class="mtx-hint">
                                <i class="fa fa-lock" aria-hidden="true"></i> {{ $t('stock_out.date_pickup_desc') }}
                            </p>
                        </div>

                        <button
                            class="mtx-btn mtx-btn-primary"
                            type="submit"
                            :disabled="form.processing || filteredPics.length === 0"
                        >
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
