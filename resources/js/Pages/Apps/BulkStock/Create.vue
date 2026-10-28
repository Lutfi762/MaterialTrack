<template>
    <Head :title="$t('bulk_stock.add')" />
    <main class="p-6">
        <div class="max-w-5xl mx-auto">
            <div class="mtx-page-header">
                <div>
                    <h1 class="mtx-page-title">{{ $t('bulk_stock.heading') }}</h1>
                    <Breadcrumb :items="[{ label: 'Dashboard', url: '/apps/dashboard' }, { label: $t('bulk_stock.heading'), url: '/apps/bulk-stock' }, { label: $t('bulk_stock.add') }]" />
                </div>
                <Link href="/apps/bulk-stock" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                </Link>
            </div>

            <div class="mtx-card">
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('bulk_stock.type') }} <span class="mtx-required">*</span></label>
                                <select v-model="form.type" class="mtx-select" required>
                                    <option value="in">{{ $t('bulk_stock.type_in') }}</option>
                                    <option value="out">{{ $t('bulk_stock.type_out') }}</option>
                                </select>
                            </div>
                            <div class="mtx-field md:col-span-2">
                                <label class="mtx-label">{{ $t('common.notes') }}</label>
                                <input type="text" class="mtx-input" v-model="form.notes" :placeholder="$t('bulk_stock.notes_placeholder')">
                            </div>
                        </div>

                        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $t('bulk_stock.items_list') }}</h3>

                        <div v-for="(entry, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-x-3 gap-y-2 items-end mb-3 p-3 bg-gray-50 rounded-md border border-gray-100">
                            <div class="col-span-12 md:col-span-5">
                                <label class="mtx-label">{{ $t('bulk_stock.material') }} <span class="mtx-required">*</span></label>
                                <select class="mtx-select" v-model="entry.item_id" required>
                                    <option value="">{{ $t('bulk_stock.material_placeholder') }}</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        [{{ item.code }}] {{ item.name }} ({{ $t('bulk_stock.stock_label') }} {{ item.current_stock }} {{ item.unit }})
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <label class="mtx-label">{{ $t('bulk_stock.qty') }} <span class="mtx-required">*</span></label>
                                <input type="number" class="mtx-input" v-model.number="entry.qty" min="1" required>
                            </div>
                            <div class="col-span-6 md:col-span-4">
                                <label class="mtx-label">{{ $t('bulk_stock.source') }}</label>
                                <input type="text" class="mtx-input" v-model="entry.source" :placeholder="$t('bulk_stock.source_placeholder')">
                            </div>
                            <div class="col-span-12 md:col-span-1 flex md:justify-center pb-0.5">
                                <button type="button" class="mtx-icon-btn mtx-icon-btn-danger" @click="removeItem(idx)" :disabled="form.items.length <= 1" :title="$t('common.delete')">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" class="mtx-btn mtx-btn-secondary mtx-btn-sm mb-4" @click="addItem">
                            <i class="fa fa-plus" aria-hidden="true"></i> {{ $t('bulk_stock.add_item') }}
                        </button>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing || form.items.length === 0">
                                <i v-if="form.processing" class="fa fa-circle-notch fa-spin" aria-hidden="true"></i>
                                <i v-else class="fa fa-check" aria-hidden="true"></i>
                                {{ form.processing ? $t('common.saving') : $t('bulk_stock.execute') }}
                            </button>
                            <Link href="/apps/bulk-stock" class="mtx-btn mtx-btn-secondary">{{ $t('common.cancel') }}</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

export default {
    layout: LayoutApp,
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
