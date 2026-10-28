<template>
    <Head><title>Ajukan Penyesuaian Stok - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-balance-scale text-indigo-600" aria-hidden="true"></i> Ajukan Penyesuaian Stok</span>
                    <Link href="/apps/stock-adjustments" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <div class="mb-4 p-3 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-sm">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        Penyesuaian stok memerlukan persetujuan dan tercatat permanen di ledger audit.
                        Gunakan angka <strong>positif (+)</strong> untuk menambah stok dan <strong>negatif (-)</strong> untuk mengurangi.
                    </div>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">Material</label>
                                <select v-model="form.item_id" class="mtx-select" :class="{ 'mtx-select-error': errors.item_id }" required>
                                    <option value="">Pilih material...</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        {{ item.code }} - {{ item.name }} (stok: {{ item.current_stock }} {{ item.unit }})
                                    </option>
                                </select>
                                <p v-if="errors.item_id" class="mtx-error-text">{{ errors.item_id }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">Qty (bertanda)</label>
                                <input v-model.number="form.qty" type="number" class="mtx-input" :class="{ 'mtx-input-error': errors.qty }" placeholder="contoh: 10 atau -5" required>
                                <p v-if="errors.qty" class="mtx-error-text">{{ errors.qty }}</p>
                            </div>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">Alasan (wajib)</label>
                            <textarea v-model="form.reason" class="mtx-textarea" :class="{ 'mtx-textarea-error': errors.reason }" rows="2" placeholder="Jelaskan alasan penyesuaian..." required></textarea>
                            <p v-if="errors.reason" class="mtx-error-text">{{ errors.reason }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">Catatan (opsional)</label>
                            <textarea v-model="form.notes" class="mtx-textarea" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                        <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="submitting">
                            <i class="fa fa-save" aria-hidden="true"></i> Ajukan Penyesuaian
                        </button>
                    </form>
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
