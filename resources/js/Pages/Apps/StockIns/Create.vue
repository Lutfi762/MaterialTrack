<template>
    <Head><title>Input Material Masuk - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-success">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-arrow-down-left me-1"></i> INPUT MATERIAL MASUK</span>
                                <Link href="/apps/stock-ins" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</Link>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Material</label>
                                            <select class="form-select" v-model="form.item_id" :class="{ 'is-invalid': form.errors.item_id }">
                                                <option :value="null" disabled>- Pilih Material -</option>
                                                <option v-for="item in items" :key="item.id" :value="item.id">
                                                    {{ item.name }} ({{ item.code }}) — Stok saat ini: {{ item.current_stock }} {{ item.unit }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.item_id" class="invalid-feedback d-block">{{ form.errors.item_id }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Quantity Masuk</label>
                                            <input class="form-control" v-model.number="form.qty" :class="{ 'is-invalid': form.errors.qty }" type="number" min="1">
                                            <div v-if="form.errors.qty" class="invalid-feedback d-block">{{ form.errors.qty }}</div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Department (opsional)</label>
                                            <select class="form-select" v-model="form.department_id">
                                                <option :value="null">- Tidak Terkait Departemen -</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Sumber</label>
                                            <input class="form-control" v-model="form.source" type="text" placeholder="Supplier PT ..., Restock Rutin, dll">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Catatan Tambahan</label>
                                        <textarea class="form-control" v-model="form.notes" rows="2"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Tanggal</label>
                                        <input class="form-control" v-model="form.date" :class="{ 'is-invalid': form.errors.date }" type="date">
                                        <div v-if="form.errors.date" class="invalid-feedback d-block">{{ form.errors.date }}</div>
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
    props: { items: Array, departments: Array },
    setup() {
        const form = useForm({
            item_id: null,
            qty: 1,
            department_id: null,
            source: '',
            notes: '',
            date: new Date().toISOString().slice(0, 10),
        });

        const submit = () => {
            form.post('/apps/stock-ins', {
                onSuccess: () => Swal.fire({ title: 'Berhasil!', text: 'Material masuk dicatat.', icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, submit };
    },
};
</script>

<style>
.border-top-success { border-top: 3px solid #198754 !important; }
</style>