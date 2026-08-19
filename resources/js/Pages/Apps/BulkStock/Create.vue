<template>
    <div>
        <Head :title="$t('bulk_stock.add')" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-1">{{ $t('bulk_stock.heading') }}</h1>
                <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('bulk_stock.heading'), url: '/apps/bulk-stock' }, { label: $t('bulk_stock.add') }]" />
            </div>
            <Link href="/apps/bulk-stock" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}
            </Link>
        </div>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ $t('bulk_stock.type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" v-model="form.type" required>
                                <option value="in">{{ $t('bulk_stock.type_in') }}</option>
                                <option value="out">{{ $t('bulk_stock.type_out') }}</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">{{ $t('common.notes') }}</label>
                            <input type="text" class="form-control" v-model="form.notes" :placeholder="$t('bulk_stock.notes_placeholder')">
                        </div>
                    </div>

                    <h5 class="mb-3">{{ $t('bulk_stock.items_list') }}</h5>

                    <div v-for="(entry, idx) in form.items" :key="idx" class="row g-2 align-items-end mb-3 p-3 bg-light rounded">
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">{{ $t('bulk_stock.material') }} <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" v-model="entry.item_id" required>
                                <option value="">{{ $t('bulk_stock.material_placeholder') }}</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">
                                    [{{ item.code }}] {{ item.name }} ({{ $t('bulk_stock.stock_label') }} {{ item.current_stock }} {{ item.unit }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">{{ $t('bulk_stock.qty') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" v-model.number="entry.qty" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">{{ $t('bulk_stock.source') }}</label>
                            <input type="text" class="form-control form-control-sm" v-model="entry.source" :placeholder="$t('bulk_stock.source_placeholder')">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="removeItem(idx)" :disabled="form.items.length <= 1" :title="$t('common.delete')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-success btn-sm mb-4" @click="addItem">
                        <i class="fa fa-plus me-1"></i> {{ $t('bulk_stock.add_item') }}
                    </button>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing || form.items.length === 0">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fa fa-check me-1"></i>
                            {{ form.processing ? $t('common.saving') : $t('bulk_stock.execute') }}
                        </button>
                        <Link href="/apps/bulk-stock" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

export default {
    components: { Head, Link, Breadcrumb },
    props: { items: Array },
    setup() {
        const { t } = useI18n();
        const form = useForm({
            type: 'in',
            notes: '',
            items: [{ item_id: '', qty: 1, source: '' }],
        });

        const addItem = () => {
            form.items.push({ item_id: '', qty: 1, source: '' });
        };

        const removeItem = (idx) => {
            if (form.items.length > 1) {
                form.items.splice(idx, 1);
            }
        };

        const submit = () => {
            form.post('/apps/bulk-stock', {
                onSuccess: () => {
                    Swal.fire({ title: t('common.saved_success'), icon: 'success', showConfirmButton: false, timer: 2000 });
                },
                onError: () => {
                    Swal.fire({ title: t('common.save_error'), text: t('common.save_error_desc'), icon: 'error', confirmButtonText: 'OK' });
                },
            });
        };

        return { form, addItem, removeItem, submit };
    },
};
</script>
