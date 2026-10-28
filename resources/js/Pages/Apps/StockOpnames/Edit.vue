<template>
    <Head>
        <title>{{ $t('stock_opname.edit_title') }} - MaterialTrack</title>
    </Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-edit text-indigo-600" aria-hidden="true"></i> {{ $t('stock_opname.edit_heading') }}</span>
                    <Link :href="`/apps/stock-opnames?period=${opname.period}`" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <div class="mb-4 p-3 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-sm">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        Revisi hanya bisa dilakukan sebelum opname diverifikasi. Setelah diverifikasi, stok sistem tidak berubah dan perubahan harus melalui penyesuaian stok baru.
                    </div>

                    <div class="mb-4 text-sm text-gray-700 space-y-1">
                        <div><strong>{{ $t('stock_opname.ref_label') }}</strong> <code>{{ opname.reference_no }}</code></div>
                        <div><strong>{{ $t('stock_opname.material_label') }}</strong> {{ opname.item?.name }} ({{ opname.item?.code }})</div>
                        <div><strong>{{ $t('stock_opname.system_stock_label') }}</strong> {{ opname.system_stock }} {{ opname.item?.unit }}</div>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_opname.physical_stock_rev') }} <span class="mtx-required">*</span></label>
                            <input type="number" class="mtx-input" :class="{ 'mtx-input-error': form.errors.physical_stock }"
                                v-model="form.physical_stock" min="0" required />
                            <p v-if="form.errors.physical_stock" class="mtx-error-text">{{ form.errors.physical_stock }}</p>
                            <div class="mt-1.5 text-sm">
                                <strong>{{ $t('stock_opname.diff_label') }} </strong>
                                <span :class="selisih >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ selisih >= 0 ? '+' : '' }}{{ selisih }}
                                </span>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('stock_opname.revision_notes') }}</label>
                            <textarea class="mtx-textarea" v-model="form.notes" rows="3" :placeholder="$t('stock_opname.revision_notes_placeholder')"></textarea>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="confirmDelete" class="mtx-btn mtx-btn-danger">
                                <i class="fa fa-trash" aria-hidden="true"></i> {{ $t('common.delete') }}
                            </button>
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">
                                <i class="fa fa-save" aria-hidden="true"></i>
                                <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                <span v-else>{{ $t('stock_opname.save_revision') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        opname: Object,
    },
    setup(props) {
        const form = useForm({
            physical_stock: props.opname.physical_stock,
            notes: props.opname.notes ?? '',
        });

        const selisih = computed(() => {
            return (form.physical_stock ?? 0) - props.opname.system_stock;
        });

        const submit = () => {
            form.put(`/apps/stock-opnames/${props.opname.id}`);
        };

        const confirmDelete = () => {
            if (confirm($t('stock_opname.delete_confirm'))) {
                router.delete(`/apps/stock-opnames/${props.opname.id}`);
            }
        };

        return { form, selisih, submit, confirmDelete };
    },
};
</script>
