<template>
    <Head>
        <title>{{ $t('stock_opname.edit_title') }} - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-warning">
                                    <i class="fa fa-edit me-1"></i> {{ $t('stock_opname.edit_heading') }}
                                </h6>
                                <Link :href="`/apps/stock-opnames?period=${opname.period}`" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
                                </Link>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info small mb-3">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Revisi hanya bisa dilakukan sebelum opname diverifikasi. Setelah diverifikasi, stok sistem tidak berubah dan perubahan harus melalui penyesuaian stok baru.
                                </div>

                                <div class="mb-3">
                                    <strong>{{ $t('stock_opname.ref_label') }}</strong> <code>{{ opname.reference_no }}</code>
                                </div>
                                <div class="mb-3">
                                    <strong>{{ $t('stock_opname.material_label') }}</strong> {{ opname.item?.name }} ({{ opname.item?.code }})
                                </div>
                                <div class="mb-3">
                                    <strong>{{ $t('stock_opname.system_stock_label') }}</strong> {{ opname.system_stock }} {{ opname.item?.unit }}
                                </div>

                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ $t('stock_opname.physical_stock_rev') }} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" :class="{ 'is-invalid': form.errors.physical_stock }"
                                            v-model="form.physical_stock" min="0" required />
                                        <div v-if="form.errors.physical_stock" class="invalid-feedback">{{ form.errors.physical_stock }}</div>
                                        <div class="mt-1">
                                            <strong>{{ $t('stock_opname.diff_label') }} </strong>
                                            <span :class="selisih >= 0 ? 'text-success' : 'text-danger'">
                                                {{ selisih >= 0 ? '+' : '' }}{{ selisih }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ $t('stock_opname.revision_notes') }}</label>
                                        <textarea class="form-control" v-model="form.notes" rows="3" :placeholder="$t('stock_opname.revision_notes_placeholder')"></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" @click="confirmDelete" class="btn btn-danger">
                                            <i class="fa fa-trash me-1"></i> {{ $t('common.delete') }}
                                        </button>
                                        <button type="submit" class="btn btn-warning" :disabled="form.processing">
                                            <i class="fa fa-save me-1"></i>
                                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                            <span v-else>{{ $t('stock_opname.save_revision') }}</span>
                                        </button>
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
