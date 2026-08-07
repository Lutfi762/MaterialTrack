<template>
    <Head><title>Edit Material Masuk - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-success">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-pencil-alt me-1"></i> EDIT MATERIAL MASUK</span>
                                <Link href="/apps/stock-ins" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</Link>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    Qty dan material tidak bisa diubah. Batalkan transaksi jika salah input.
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold text-muted small">No. Referensi</label>
                                        <p class="mb-0">{{ stockIn.reference_no }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold text-muted small">Material</label>
                                        <p class="mb-0">{{ stockIn.item?.name }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold text-muted small">Qty</label>
                                        <p class="mb-0">{{ stockIn.qty }} {{ stockIn.item?.unit }}</p>
                                    </div>
                                </div>

                                <hr>

                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="fw-bold">Sumber</label>
                                        <input class="form-control" v-model="form.source" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Catatan Tambahan</label>
                                        <textarea class="form-control" v-model="form.notes" rows="3"></textarea>
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
    props: { stockIn: Object },
    setup(props) {
        const form = useForm({
            source: props.stockIn.source ?? '',
            notes: props.stockIn.notes ?? '',
        });

        const submit = () => {
            form.put(`/apps/stock-ins/${props.stockIn.id}`, {
                onSuccess: () => Swal.fire({ title: 'Berhasil!', text: 'Transaksi diperbarui.', icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, submit };
    },
};
</script>

<style>
.border-top-success { border-top: 3px solid #198754 !important; }
</style>