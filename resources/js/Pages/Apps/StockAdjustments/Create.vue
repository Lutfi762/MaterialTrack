<template>
    <Head><title>Ajukan Penyesuaian Stok - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-balance-scale me-1"></i> Ajukan Penyesuaian Stok</span>
                            <Link href="/apps/stock-adjustments" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </Link>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle me-1"></i>
                                Penyesuaian stok memerlukan persetujuan dan tercatat permanen di ledger audit.
                                Gunakan angka <strong>positif (+)</strong> untuk menambah stok dan <strong>negatif (-)</strong> untuk mengurangi.
                            </div>

                            <form @submit.prevent="submit">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Material</label>
                                        <select v-model="form.item_id" class="form-select" required>
                                            <option value="">Pilih material...</option>
                                            <option v-for="item in items" :key="item.id" :value="item.id">
                                                {{ item.code }} - {{ item.name }} (stok: {{ item.current_stock }} {{ item.unit }})
                                            </option>
                                        </select>
                                        <div v-if="errors.item_id" class="text-danger small mt-1">{{ errors.item_id }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Qty (bertanda)</label>
                                        <input v-model.number="form.qty" type="number" class="form-control" placeholder="contoh: 10 atau -5" required>
                                        <div v-if="errors.qty" class="text-danger small mt-1">{{ errors.qty }}</div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Alasan (wajib)</label>
                                        <textarea v-model="form.reason" class="form-control" rows="2" placeholder="Jelaskan alasan penyesuaian..." required></textarea>
                                        <div v-if="errors.reason" class="text-danger small mt-1">{{ errors.reason }}</div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Catatan (opsional)</label>
                                        <textarea v-model="form.notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                                            <i class="fa fa-save me-1"></i> Ajukan Penyesuaian
                                        </button>
                                    </div>
                                </div>
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
import { Head, Link, router } from '@inertiajs/vue3';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        items: Array,
        errors: Object,
    },
    data() {
        return {
            form: {
                item_id: '',
                qty: '',
                reason: '',
                notes: '',
            },
            submitting: false,
        };
    },
    methods: {
        submit() {
            this.submitting = true;
            router.post('/apps/stock-adjustments', this.form, {
                onFinish: () => { this.submitting = false; },
            });
        },
    },
};
</script>
