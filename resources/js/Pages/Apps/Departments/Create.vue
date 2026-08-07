<template>
    <Head><title>Tambah Department - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-building me-1"></i> TAMBAH DEPARTMENT</span>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nama Department <span class="text-danger">*</span></label>
                                        <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" placeholder="Contoh: Produksi">
                                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Kode Department</label>
                                        <input v-model="form.code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.code }" placeholder="Contoh: PROD">
                                        <div class="invalid-feedback">{{ form.errors.code }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }">
                                            <option value="active">Aktif</option>
                                            <option value="inactive">Non Aktif</option>
                                        </select>
                                        <div class="invalid-feedback">{{ form.errors.status }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Deskripsi</label>
                                        <textarea v-model="form.description" class="form-control" rows="3" placeholder="Deskripsi department (opsional)"></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                            Simpan
                                        </button>
                                        <Link href="/apps/departments" class="btn btn-secondary">Batal</Link>
                                    </div>
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

export default {
    layout: LayoutApp,
    components: { Head, Link },
    setup() {
        const form = useForm({ name: '', code: '', description: '', status: 'active' });
        const submit = () => form.post('/apps/departments');
        return { form, submit };
    }
}
</script>