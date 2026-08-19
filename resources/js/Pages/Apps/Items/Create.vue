<template>
    <Head><title>{{ $t('items.add') }} - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow border-top-purple">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-box me-1"></i> {{ $t('items.add') }}</span>
                            <Link href="/apps/items" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">{{ $t('common.department') }}</label>
                                        <select class="form-select" v-model="selectedDepartment" @change="form.category_id = null">
                                            <option value="">- {{ $t('items.filter_all_dept') }} -</option>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                        <small class="text-muted">Pilih department untuk menyaring daftar kategori di bawah.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">{{ $t('items.name') }} <span class="text-danger">*</span></label>
                                        <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text" required>
                                        <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">{{ $t('common.category') }} <span class="text-danger">*</span></label>
                                        <select class="form-select" v-model="form.category_id" :class="{ 'is-invalid': form.errors.category_id }" required>
                                            <option :value="null" disabled>- {{ $t('items.filter_all_cat') }} -</option>
                                            <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }} ({{ cat.code_prefix }})</option>
                                        </select>
                                        <small class="text-muted" v-if="selectedCategory">
                                            {{ $t('items.code_auto') }} <strong>{{ selectedCategory.code_prefix }}-XXXX</strong>
                                        </small>
                                        <div v-if="form.errors.category_id" class="invalid-feedback d-block">{{ form.errors.category_id }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.location_label') }}</label>
                                        <select class="form-select" v-model="form.location_id">
                                            <option :value="null">{{ $t('items.location_none') }}</option>
                                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.warehouse_label') }}</label>
                                        <select class="form-select" v-model="form.warehouse_id">
                                            <option :value="null">{{ $t('items.warehouse_none') }}</option>
                                            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.unit_label') }} <span class="text-danger">*</span></label>
                                        <select class="form-select" v-model="form.unit" :class="{ 'is-invalid': form.errors.unit }" required>
                                            <option value="" disabled>- {{ $t('items.unit_label') }} -</option>
                                            <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                        </select>
                                        <div v-if="form.errors.unit" class="invalid-feedback d-block">{{ form.errors.unit }}</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.stock_initial') }} <span class="text-danger">*</span></label>
                                        <input class="form-control" v-model.number="form.current_stock" :class="{ 'is-invalid': form.errors.current_stock }" type="number" min="0" required>
                                        <div v-if="form.errors.current_stock" class="invalid-feedback d-block">{{ form.errors.current_stock }}</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('items.price') }}</label>
                                    <input type="number" class="form-control" v-model="form.price" min="0" step="0.01" placeholder="0">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">{{ $t('items.stock_min') }} <span class="text-danger">*</span></label>
                                        <input class="form-control" v-model.number="form.min_stock" :class="{ 'is-invalid': form.errors.min_stock }" type="number" min="0" required>
                                        <div v-if="form.errors.min_stock" class="invalid-feedback d-block">{{ form.errors.min_stock }}</div>
                                    </div>
                                </div>

                                <hr>
                                <p class="text-muted small mb-3"><i class="fa fa-info-circle me-1"></i> {{ $t('items.conversion_desc') }}</p>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">{{ $t('items.unit_alt') }}</label>
                                        <input class="form-control" v-model="form.unit_alt" :class="{ 'is-invalid': form.errors.unit_alt }" type="text" :placeholder="$t('items.unit_alt_placeholder')">
                                        <div v-if="form.errors.unit_alt" class="invalid-feedback d-block">{{ form.errors.unit_alt }}</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">{{ $t('items.conversion_factor') }}</label>
                                        <div class="input-group">
                                            <input class="form-control" v-model.number="form.conversion_factor" :class="{ 'is-invalid': form.errors.conversion_factor }" type="number" min="0.01" step="0.01" placeholder="12">
                                            <span class="input-group-text">{{ form.unit || '...' }}</span>
                                        </div>
                                        <div v-if="form.errors.conversion_factor" class="invalid-feedback d-block">{{ form.errors.conversion_factor }}</div>
                                        <small class="text-muted">1 {{ form.unit_alt || '?' }} = ? {{ form.unit || '...' }}</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('common.description') }}</label>
                                    <textarea class="form-control" v-model="form.description" rows="2"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('items.image') }}</label>
                                    <input class="form-control" type="file" accept="image/*" @change="onImageChange">
                                    <div v-if="form.errors.image" class="invalid-feedback d-block">{{ form.errors.image }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('items.photos') }}</label>
                                    <input type="file" class="form-control" @input="form.photos = $event.target.files" multiple accept="image/*">
                                    <small class="text-muted">{{ $t('items.photos_desc') }}</small>
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

<style>
.border-top-purple { border-top: 3px solid #6f42c1 !important; }
</style>