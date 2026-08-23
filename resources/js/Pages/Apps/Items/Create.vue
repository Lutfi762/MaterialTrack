<template>
    <Head><title>{{ $t('items.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-box text-indigo-600" aria-hidden="true"></i> {{ $t('items.add') }}</span>
                    <Link href="/apps/items" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('common.department') }}</label>
                                <select class="mtx-select" v-model="selectedDepartment" @change="form.category_id = null">
                                    <option value="">- {{ $t('items.filter_all_dept') }} -</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <p class="mtx-hint">Pilih department untuk menyaring daftar kategori di bawah.</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.name') }} <span class="mtx-required">*</span></label>
                                <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }" v-model="form.name" type="text" required>
                                <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('common.category') }} <span class="mtx-required">*</span></label>
                            <select class="mtx-select" v-model="form.category_id" :class="{ 'mtx-input-error': form.errors.category_id }" required>
                                <option :value="null" disabled>- {{ $t('items.filter_all_cat') }} -</option>
                                <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }} ({{ cat.code_prefix }})</option>
                            </select>
                            <p class="mtx-hint" v-if="selectedCategory">
                                {{ $t('items.code_auto') }} <strong>{{ selectedCategory.code_prefix }}-XXXX</strong>
                            </p>
                            <p v-if="form.errors.category_id" class="mtx-error-text">{{ form.errors.category_id }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.location_label') }}</label>
                                <select class="mtx-select" v-model="form.location_id">
                                    <option :value="null">{{ $t('items.location_none') }}</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                                </select>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.warehouse_label') }}</label>
                                <select class="mtx-select" v-model="form.warehouse_id">
                                    <option :value="null">{{ $t('items.warehouse_none') }}</option>
                                    <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                                </select>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.unit_label') }} <span class="mtx-required">*</span></label>
                                <select class="mtx-select" v-model="form.unit" :class="{ 'mtx-input-error': form.errors.unit }" required>
                                    <option value="" disabled>- {{ $t('items.unit_label') }} -</option>
                                    <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                </select>
                                <p v-if="form.errors.unit" class="mtx-error-text">{{ form.errors.unit }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.stock_initial') }} <span class="mtx-required">*</span></label>
                                <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.current_stock }" v-model.number="form.current_stock" type="number" min="0" required>
                                <p v-if="form.errors.current_stock" class="mtx-error-text">{{ form.errors.current_stock }}</p>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.price') }}</label>
                            <input type="number" class="mtx-input" v-model="form.price" min="0" step="0.01" placeholder="0">
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.stock_min') }} <span class="mtx-required">*</span></label>
                            <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.min_stock }" v-model.number="form.min_stock" type="number" min="0" required>
                            <p v-if="form.errors.min_stock" class="mtx-error-text">{{ form.errors.min_stock }}</p>
                        </div>

                        <hr class="border-gray-100 my-4">
                        <p class="mtx-hint mb-3"><i class="fa fa-info-circle" aria-hidden="true"></i> {{ $t('items.conversion_desc') }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.unit_alt') }}</label>
                                <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.unit_alt }" v-model="form.unit_alt" type="text" :placeholder="$t('items.unit_alt_placeholder')">
                                <p v-if="form.errors.unit_alt" class="mtx-error-text">{{ form.errors.unit_alt }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.conversion_factor') }}</label>
                                <input class="mtx-input" :class="{ 'mtx-input-error': form.errors.conversion_factor }" v-model.number="form.conversion_factor" type="number" min="0.01" step="0.01" placeholder="12">
                                <p v-if="form.errors.conversion_factor" class="mtx-error-text">{{ form.errors.conversion_factor }}</p>
                                <p class="mtx-hint">1 {{ form.unit_alt || '?' }} = {{ form.conversion_factor || '?' }} {{ form.unit || '...' }}</p>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('common.description') }}</label>
                            <textarea class="mtx-textarea" v-model="form.description" rows="2"></textarea>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.image') }}</label>
                            <input class="mtx-input" type="file" accept="image/*" @change="onImageChange">
                            <p v-if="form.errors.image" class="mtx-error-text">{{ form.errors.image }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.photos') }}</label>
                            <input type="file" class="mtx-input" @input="form.photos = $event.target.files" multiple accept="image/*">
                            <p class="mtx-hint">{{ $t('items.photos_desc') }}</p>
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
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { departments: Array, categories: Array, locations: Array, warehouses: Array, unitOptions: Array },
    setup(props) {
        const { t } = useI18n();
        const form = useForm({
            category_id: null,
            name: '',
            location_id: null,
            warehouse_id: null,
            unit: '',
            unit_alt: '',
            conversion_factor: null,
            current_stock: 0,
            min_stock: 0,
            price: null,
            description: '',
            image: null,
            photos: [],
        });

        const selectedDepartment = ref('');

        const filteredCategories = computed(() => {
            if (!selectedDepartment.value) return props.categories;
            return props.categories.filter(cat =>
                (cat.department_ids ?? []).includes(selectedDepartment.value)
            );
        });

        const selectedCategory = computed(() =>
            props.categories.find(c => c.id === form.category_id) ?? null
        );

        const onImageChange = (e) => {
            form.image = e.target.files[0] ?? null;
        };

        const submit = () => {
            form.post('/apps/items', {
                onSuccess: () => Swal.fire({ title: t('common.saved_success'), text: t('items.add'), icon: 'success', showConfirmButton: false, timer: 2000 }),
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

        return { form, selectedDepartment, filteredCategories, selectedCategory, onImageChange, submit };
    },
};
</script>