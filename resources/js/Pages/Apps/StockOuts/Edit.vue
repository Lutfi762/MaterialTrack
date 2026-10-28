<template>
    <Head>
        <title>{{ $t('stock_out.edit_heading') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-pencil-alt text-indigo-600" aria-hidden="true"></i> {{ $t('stock_out.edit_heading') }}</span>
                    <Link href="/apps/stock-outs" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">

                    <div class="mb-4 p-3 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-sm">
                        <i class="fa fa-circle-info" aria-hidden="true"></i>
                        {{ $t('stock_out.edit_info') }}
                    </div>

                    <!-- Info transaksi (read-only) -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-x-4 mb-4">
                        <div>
                            <label class="mtx-label">{{ $t('stock_out.col_ref_no') }}</label>
                            <p class="text-sm text-gray-900">{{ stockOut.reference_no }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">{{ $t('stock_out.material') }}</label>
                            <p class="text-sm text-gray-900">{{ stockOut.item?.name }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">Qty</label>
                            <p class="text-sm text-gray-900">{{ stockOut.qty }} {{ stockOut.item?.unit }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">{{ $t('stock_out.department') }}</label>
                            <p class="text-sm text-gray-900">{{ stockOut.department?.name }}</p>
                        </div>
                        <div>
                            <label class="mtx-label">{{ $t('stock_out.pic') }}</label>
                            <p class="text-sm text-gray-900">{{ stockOut.pic?.name }}</p>
                        </div>
                    </div>

                    <hr class="border-gray-200 mb-4">

                    <form @submit.prevent="submit">

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_out.purpose') }}</label>
                            <input
                                class="mtx-input"
                                v-model="form.purpose"
                                :class="{ 'mtx-input-error': form.errors.purpose }"
                                type="text"
                            >
                            <p v-if="form.errors.purpose" class="mtx-error-text">{{ form.errors.purpose }}</p>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_out.additional_notes') }}</label>
                            <textarea
                                class="mtx-textarea"
                                v-model="form.notes"
                                rows="3"
                            ></textarea>
                        </div>

                        <button
                            class="mtx-btn mtx-btn-primary"
                            type="submit"
                            :disabled="form.processing"
                        >
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
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

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
        const { t } = useI18n();
        const form = useForm({
            purpose: props.stockOut.purpose,
            notes: props.stockOut.notes ?? '',
        });

        const submit = () => {
            form.put(`/apps/stock-outs/${props.stockOut.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: t('common.success'),
                        text: t('stock_out.update_success'),
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
