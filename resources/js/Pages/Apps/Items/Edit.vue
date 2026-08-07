<template>
    <Head><title>Edit Material - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow border-top-purple">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-box me-1"></i> EDIT MATERIAL</span>
                            <Link href="/apps/items" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</Link>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fa fa-circle-info me-1"></i>
                                Kode material (<strong>{{ item.code }}</strong>) dan kategori tidak bisa diubah setelah dibuat. Stok saat ini ({{ item.current_stock }} {{ item.unit }}) juga tidak bisa diubah di sini — gunakan menu Stok Masuk/Keluar.
                            </div>

                            <form @submit.prevent="submit">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Kategori (terkunci)</label>
                                        <input class="form-control" :value="item.category?.name" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Nama Material</label>
                                        <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text">
                                        <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
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
                                            <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                        </select>
                                        <div v-if="form.errors.unit" class="invalid-feedback d-block">{{ form.errors.unit }}</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
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
                                    <label class="fw-bold">Gambar Baru (opsional)</label>
                                    <input class="form-control" type="file" accept="image/*" @change="onImageChange">
                                    <div v-if="form.errors.image" class="invalid-feedback d-block">{{ form.errors.image }}</div>
                                    <img v-if="item.image" :src="item.image" class="mt-2 rounded" style="max-height:80px">
                                </div>

                                <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>UPDATE</span>
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
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { item: Object, categories: Array, locations: Array, unitOptions: Array },
    setup(props) {
        const form = useForm({
            _method: 'put',
            category_id: props.item.category_id,
            name: props.item.name,
            location_id: props.item.location_id,
            unit: props.item.unit,
            min_stock: props.item.min_stock,
            description: props.item.description ?? '',
            image: null,
        });

        const onImageChange = (e) => {
            form.image = e.target.files[0] ?? null;
        };

        const submit = () => {
            form.post(`/apps/items/${props.item.id}`, {
                onSuccess: () => Swal.fire({ title: 'Berhasil!', text: 'Material diperbarui.', icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, onImageChange, submit };
    },
};
</script>

<style>
.border-top-purple { border-top: 3px solid #6f42c1 !important; }
</style>