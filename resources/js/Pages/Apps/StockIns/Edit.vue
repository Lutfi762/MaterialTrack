<template>
    <Head><title>{{ $t('stock_in.edit') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-success">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-pencil-alt me-1"></i> {{ $t('stock_in.heading') }}</span>
                                <Link href="/apps/stock-ins" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    {{ $t('stock_in.edit_alert') }}
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold text-muted small">{{ $t('stock_in.ref_no') }}</label>
                                        <p class="mb-0">{{ stockIn.reference_no }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="fw-bold text-muted small">{{ $t('stock_in.material') }}</label>
                                        <p class="mb-0">{{ stockIn.item?.name }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="fw-bold text-muted small">Qty</label>
                                        <p class="mb-0">{{ stockIn.qty }} {{ stockIn.item?.unit }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="fw-bold text-muted small">{{ $t('stock_in.po_number') }}</label>
                                        <p class="mb-0">{{ stockIn.po_number || '-' }}</p>
                                    </div>
                                </div>

                                <hr>

                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.po_number') }}</label>
                                            <input class="form-control" v-model="form.po_number" type="text">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">{{ $t('stock_in.supplier') }}</label>
                                            <select class="form-select" v-model="form.supplier_id">
                                                <option :value="null">{{ $t('stock_in.supplier_none') }}</option>
                                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_in.source') }}</label>
                                        <input class="form-control" v-model="form.source" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">{{ $t('stock_in.additional_notes') }}</label>
                                        <textarea class="form-control" v-model="form.notes" rows="3"></textarea>
                                    </div>

                                    <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                        <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                        <span v-else>{{ $t('common.update') }}</span>
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
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { stockIn: Object, suppliers: { type: Array, default: () => [] } },
    setup(props) {
        const { t } = useI18n();
        const form = useForm({
            po_number: props.stockIn.po_number ?? '',
            supplier_id: props.stockIn.supplier_id ?? null,
            source: props.stockIn.source ?? '',
            notes: props.stockIn.notes ?? '',
        });

        const submit = () => {
            form.put(`/apps/stock-ins/${props.stockIn.id}`, {
                onSuccess: () => Swal.fire({ title: t('common.saved_success'), text: t('stock_in.success_update'), icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, submit };
    },
};
</script>

<style>
.border-top-success { border-top: 3px solid #198754 !important; }
</style>