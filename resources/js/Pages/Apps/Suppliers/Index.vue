<template>
    <Head><title>{{ $t('suppliers.title') }} - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-truck me-1"></i> {{ $t('suppliers.heading') }}</span>
                            <Link v-if="hasAnyPermission(['items.create'])" href="/apps/suppliers/create" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus-circle me-2"></i> NEW
                            </Link>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input
                                            v-model="search"
                                            type="text"
                                            class="form-control"
                                            :placeholder="$t('suppliers.search_placeholder')"
                                            @keyup.enter="applyFilters"
                                        />
                                        <button class="btn btn-outline-secondary" @click="applyFilters">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select v-model="filterActive" class="form-select" @change="applyFilters">
                                        <option value="">{{ $t('common.all') }}</option>
                                        <option :value="1">{{ $t('common.active') }}</option>
                                        <option :value="0">{{ $t('common.inactive') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-end">
                                    <button class="btn btn-outline-secondary btn-sm" @click="resetFilters">
                                        <i class="fa fa-redo me-1"></i> {{ $t('common.reset') }}
                                    </button>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ $t('suppliers.col_no') }}</th>
                                        <th>{{ $t('suppliers.col_code') }}</th>
                                        <th>{{ $t('suppliers.col_name') }}</th>
                                        <th>{{ $t('suppliers.col_contact_person') }}</th>
                                        <th>{{ $t('suppliers.col_email') }}</th>
                                        <th>{{ $t('suppliers.col_phone') }}</th>
                                        <th class="text-center">{{ $t('suppliers.col_status') }}</th>
                                        <th class="text-center" style="width:15%">{{ $t('common.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="8" class="text-center">{{ $t('suppliers.empty') }}</td>
                                    </tr>
                                    <tr v-for="(supplier, i) in suppliers.data" :key="supplier.id">
                                        <td>{{ (suppliers.current_page - 1) * suppliers.per_page + i + 1 }}</td>
                                        <td>{{ supplier.code ?? '-' }}</td>
                                        <td>{{ supplier.name }}</td>
                                        <td>{{ supplier.contact_person ?? '-' }}</td>
                                        <td>{{ supplier.email ?? '-' }}</td>
                                        <td>{{ supplier.phone ?? '-' }}</td>
                                        <td class="text-center">
                                            <span v-if="supplier.is_active" class="badge bg-success">{{ $t('common.active') }}</span>
                                            <span v-else class="badge bg-secondary">{{ $t('common.inactive') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <Link v-if="hasAnyPermission(['items.edit'])" :href="`/apps/suppliers/${supplier.id}/edit`" class="btn btn-success btn-sm me-2">
                                                <i class="fa fa-pencil-alt"></i>
                                            </Link>
                                            <button v-if="hasAnyPermission(['items.delete'])" class="btn btn-danger btn-sm" @click="handleDelete(supplier.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="suppliers.links" align="end" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: {
        suppliers: Object,
        filters: Object,
    },
    setup(props) {
        const search = ref(props.filters?.search ?? '');
        const filterActive = ref(props.filters?.is_active ?? '');
        const isLoading = ref(false);

        const applyFilters = () => {
            router.get('/apps/suppliers', {
                search: search.value,
                is_active: filterActive.value,
            }, {
                preserveState: true,
                replace: true,
            });
        };

        const resetFilters = () => {
            search.value = '';
            filterActive.value = '';
            router.get('/apps/suppliers', {}, { preserveState: true, replace: true });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete($t('suppliers.delete_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/suppliers/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => { isLoading.value = false; },
                });
            }
        };

        return { search, filterActive, handleDelete, applyFilters, resetFilters, isLoading };
    },
};
</script>
