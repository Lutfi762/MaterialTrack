<template>
    <Head><title>{{ $t('stock_in.edit') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-pencil-alt text-indigo-600" aria-hidden="true"></i> {{ $t('stock_in.heading') }}</span>
                    <Link href="/apps/stock-ins" class="mtx-btn mtx-btn-secondary mtx-btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}</Link>
                </div>
                <div class="mtx-card-body">
                    <div class="mb-4 p-3 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-sm">
                        {{ $t('stock_in.edit_alert') }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-x-4 mb-4">
                        <div>
                            <label class="mtx-label">{{ $t('stock_in.ref_no') }}</label>
                            <p class="text-sm text-gray-900">{{ stockIn.reference_no }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">{{ $t('stock_in.material') }}</label>
                            <p class="text-sm text-gray-900">{{ stockIn.item?.name }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">Qty</label>
                            <p class="text-sm text-gray-900">{{ stockIn.qty }} {{ stockIn.item?.unit }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">{{ $t('stock_in.po_number') }}</label>
                            <p class="text-sm text-gray-900">{{ stockIn.po_number || '-' }}</p>
                        </div>
                    </div>

                    <hr class="border-gray-200 mb-4">

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.po_number') }}</label>
                                <input class="mtx-input" v-model="form.po_number" type="text">
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('stock_in.supplier') }}</label>
                                <select class="mtx-select" v-model="form.supplier_id">
                                    <option :value="null">{{ $t('stock_in.supplier_none') }}</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_in.source') }}</label>
                            <input class="mtx-input" v-model="form.source" type="text">
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_in.additional_notes') }}</label>
                            <textarea class="mtx-textarea" v-model="form.notes" rows="3"></textarea>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>{{ $t('common.update') }}</span>
                        </button>
                    </form>
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
