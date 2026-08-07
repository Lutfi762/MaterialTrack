<template>
    <Head><title>Tambah Material - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow border-top-purple">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-box me-1"></i> TAMBAH MATERIAL</span>
                            <Link href="/apps/items" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</Link>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Department</label>
                                        <select class="form-select" v-model="selectedDepartment" @change="form.category_id = null">
                                            <option value="">- Semua Department -</option>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                        <small class="text-muted">Pilih department untuk menyaring daftar kategori di bawah.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Nama Material</label>
                                        <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text">
                                        <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Kategori</label>
                                        <select class="form-select" v-model="form.category_id" :class="{ 'is-invalid': form.errors.category_id }">
                                            <option :value="null" disabled>- Pilih Kategori -</option>
                                            <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }} ({{ cat.code_prefix }})</option>
                                        </select>
                                        <small class="text-muted" v-if="selectedCategory">
                                            Kode material otomatis: <strong>{{ selectedCategory.code_prefix }}-XXXX</strong>
                                        </small>
                                        <div v-if="form.errors.category_id" class="invalid-feedback d-block">{{ form.errors.category_id }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">Lokasi Gudang</label>
                                        <select class="form-select" v-model="form.location_id">
                                            <option :value="null">- Tanpa Lokasi -</option>
                                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">Satuan</label>
                                        <select class="form-select" v-model="form.unit" :class="{ 'is-invalid': form.errors.unit }">
                                            <option value="" disabled>- Pilih Satuan -</option>
                                            <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                        </select>
                                        <div v-if="form.errors.unit" class="invalid-feedback d-block">{{ form.errors.unit }}</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">Stok Awal</label>
                                        <input class="form-control" v-model.number="form.current_stock" :class="{ 'is-invalid': form.errors.current_stock }" type="number" min="0">
                                        <div v-if="form.errors.current_stock" class="invalid-feedback d-block">{{ form.errors.current_stock }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Stok Minimum</label>
                                        <input class="form-control" v-model.number="form.min_stock" :class="{ 'is-invalid': form.errors.min_stock }" type="number" min="0">
                                        <div v-if="form.errors.min_stock" class="invalid-feedback d-block">{{ form.errors.min_stock }}</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">Deskripsi</label>
                                    <textarea class="form-control" v-model="form.description" rows="2"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">Gambar (opsional)</label>
                                    <input class="form-control" type="file" accept="image/*" @change="onImageChange">
                                    <div v-if="form.errors.image" class="invalid-feedback d-block">{{ form.errors.image }}</div>
                                </div>

                                <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>SIMPAN</span>
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
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { departments: Array, categories: Array, locations: Array, unitOptions: Array },
    setup(props) {
        const form = useForm({
            category_id: null,
            name: '',
            location_id: null,
            unit: '',
            current_stock: 0,
            min_stock: 0,
            description: '',
            image: null,
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
                onSuccess: () => Swal.fire({ title: 'Berhasil!', text: 'Material ditambahkan.', icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, selectedDepartment, filteredCategories, selectedCategory, onImageChange, submit };
    },
};
</script>

<style>
.border-top-purple { border-top: 3px solid #6f42c1 !important; }
</style>