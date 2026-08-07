<template>
    <Head>
        <title>Edit Transaksi Material Keluar - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-danger">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-pencil-alt me-1"></i> EDIT TRANSAKSI MATERIAL KELUAR</span>
                                <Link href="/apps/stock-outs" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> Kembali
                                </Link>
                            </div>
                            <div class="card-body">

                                <div class="alert alert-info">
                                    <i class="fa fa-circle-info me-1"></i>
                                    Qty, material, department, dan PIC tidak bisa diubah setelah transaksi tercatat
                                    (untuk menjaga konsistensi stok). Jika ada kesalahan input, batalkan transaksi ini
                                    lalu buat transaksi baru.
                                </div>

                                <!-- Info transaksi (read-only) -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold text-muted small">No. Referensi</label>
                                        <p class="mb-0">{{ stockOut.reference_no }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="fw-bold text-muted small">Material</label>
                                        <p class="mb-0">{{ stockOut.item?.name }}</p>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold text-muted small">Qty</label>
                                        <p class="mb-0">{{ stockOut.qty }} {{ stockOut.item?.unit }}</p>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold text-muted small">Department</label>
                                        <p class="mb-0">{{ stockOut.department?.name }}</p>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold text-muted small">PIC</label>
                                        <p class="mb-0">{{ stockOut.pic?.name }}</p>
                                    </div>
                                </div>

                                <hr>

                                <form @submit.prevent="submit">

                                    <div class="mb-3">
                                        <label class="fw-bold">Keperluan Penggunaan</label>
                                        <input
                                            class="form-control"
                                            v-model="form.purpose"
                                            :class="{ 'is-invalid': form.errors.purpose }"
                                            type="text"
                                        >
                                        <div v-if="form.errors.purpose" class="invalid-feedback d-block">{{ form.errors.purpose }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Catatan Tambahan</label>
                                        <textarea
                                            class="form-control"
                                            v-model="form.notes"
                                            rows="3"
                                        ></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary shadow-sm rounded-sm"
                                                type="submit"
                                                :disabled="form.processing"
                                            >
                                                <span v-if="form.processing">Menyimpan...</span>
                                                <span v-else>UPDATE</span>
                                            </button>
                                        </div>
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
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
    },

    props: {
        stockOut: Object,
    },

    setup(props) {
        const form = useForm({
            purpose: props.stockOut.purpose,
            notes: props.stockOut.notes ?? '',
        });

        const submit = () => {
            form.put(`/apps/stock-outs/${props.stockOut.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Transaksi berhasil diperbarui.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                },
            });
        };

        return { form, submit };
    },
}
</script>

<style>
.border-top-danger {
    border-top: 3px solid #dc3545 !important;
}
</style>