<template>
    <Head><title>{{ $t('items.title') }} - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-box-seam me-1"></i> {{ $t('items.heading') }}</span>
                            <div class="d-flex gap-2">
                                <button v-if="hasAnyPermission(['items.create'])" @click="showImportModal = true" class="btn btn-success btn-sm">
                                    <i class="fa fa-file-import me-1"></i> {{ $t('items.import_excel') }}
                                </button>
                                <Link v-if="hasAnyPermission(['items.create'])" href="/apps/items/create" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus-circle me-1"></i> NEW
                                </Link>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 mb-3">
                                <select class="form-select" style="max-width: 220px;" v-model="filterDepartment" @change="onDepartmentChange" :disabled="isLoading">
                                    <option value="">{{ $t('items.filter_all_dept') }}</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <select class="form-select" style="max-width: 220px;" v-model="filterCategory" @change="applyFilter" :disabled="isLoading">
                                    <option value="">{{ $t('items.filter_all_cat') }}</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <select class="form-select" style="max-width: 220px;" v-model="filterStatus" @change="applyFilter" :disabled="isLoading">
                                    <option value="">{{ $t('items.filter_all_status') }}</option>
                                    <option value="aman">{{ $t('items.status_safe') }}</option>
                                    <option value="menipis">{{ $t('items.status_low') }}</option>
                                </select>
                                <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary ms-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                            </div>

                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ $t('items.col_code') }}</th>
                                        <th scope="col">{{ $t('items.col_name') }}</th>
                                        <th scope="col">{{ $t('items.col_category') }}</th>
                                        <th scope="col">{{ $t('items.col_location') }}</th>
                                        <th scope="col">{{ $t('items.col_stock') }}</th>
                                        <th scope="col">{{ $t('items.col_min_stock') }}</th>
                                        <th scope="col">{{ $t('items.col_status') }}</th>
                                        <th scope="col" class="text-center" style="width:15%">{{ $t('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="items.data.length === 0">
                                        <td colspan="8" class="text-center">{{ $t('items.empty') }}</td>
                                    </tr>
                                    <tr v-for="item in items.data" :key="item.id">
                                        <td><code>{{ item.code }}</code></td>
                                        <td>{{ item.name }}</td>
                                        <td>{{ item.category?.name ?? '-' }}</td>
                                        <td>{{ item.location?.name ?? '-' }}</td>
                                        <td>{{ item.current_stock }} {{ item.unit }}</td>
                                        <td>{{ item.min_stock }} {{ item.unit }}</td>
                                        <td>
                                            <span v-if="item.current_stock <= 0" class="badge bg-danger">{{ $t('dashboard.tables.out_of_stock') }}</span>
                                            <span v-else-if="item.current_stock <= item.min_stock" class="badge bg-warning text-dark">{{ $t('dashboard.tables.low_stock') }}</span>
                                            <span v-else class="badge bg-success">{{ $t('dashboard.kpi.safe_level') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <Link :href="`/apps/items/${item.id}`" class="btn btn-info btn-sm me-2" :title="$t('stock_out.view_detail')">
                                                <i class="fa fa-eye"></i>
                                            </Link>
                                            <Link v-if="hasAnyPermission(['items.edit'])" :href="`/apps/items/${item.id}/edit`" class="btn btn-success btn-sm me-2" :aria-label="$t('items.edit')">
                                                <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                                            </Link>
                                            <button v-if="hasAnyPermission(['items.delete'])" class="btn btn-danger btn-sm" :aria-label="$t('common.delete')" @click="handleDelete(item.id)">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="items.links" align="end" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="modal-backdrop fade show" @click="showImportModal = false"></div>
        <div v-if="showImportModal" class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-file-import me-1"></i> {{ $t('items.import_title') }}</h5>
                        <button type="button" class="btn-close" @click="showImportModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Format kolom: <code>code</code> ({{ $t('common.optional') }}), <code>name</code> ({{ $t('common.required') }}), <code>description</code>, <code>category</code> ({{ $t('common.required') }}), <code>unit</code>, <code>min_stock</code>, <code>current_stock</code>.
                        </p>
                        <input type="file" class="form-control" accept=".xlsx,.xls,.csv" @change="importFile = $event.target.files[0]" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showImportModal = false">{{ $t('common.cancel') }}</button>
                        <button type="button" class="btn btn-success" @click="handleImport" :disabled="!importFile || isImporting">
                            <span v-if="isImporting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isImporting ? $t('common.saving') : $t('items.import_excel') }}
                        </button>
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
import { useI18n } from 'vue-i18n';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { items: Object, departments: Array, categories: Array, filters: Object },
    setup(props) {
        const { t } = useI18n();
        const filterDepartment = ref(props.filters?.department_id ?? '');
        const filterCategory = ref(props.filters?.category_id ?? '');
        const filterStatus = ref(props.filters?.status ?? '');
        const isLoading = ref(false);
        const showImportModal = ref(false);
        const importFile = ref(null);
        const isImporting = ref(false);

        const onDepartmentChange = () => {
            filterCategory.value = '';
            applyFilter();
        };

        const applyFilter = () => {
            isLoading.value = true;
            router.get('/apps/items', {
                department_id: filterDepartment.value || undefined,
                category_id: filterCategory.value || undefined,
                status: filterStatus.value || undefined,
            }, {
                preserveState: true,
                onFinish: () => { isLoading.value = false; }
            });
        };

        const handleDelete = async (id) => {
            if (await confirmDelete(t('items.delete_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/items/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => {
                        isLoading.value = false;
                    }
                });
            }
        };

        const handleImport = () => {
            if (!importFile.value) return;
            isImporting.value = true;
            const formData = new FormData();
            formData.append('file', importFile.value);

            router.post('/apps/items/import', formData, {
                onFinish: () => {
                    isImporting.value = false;
                    showImportModal.value = false;
                    importFile.value = null;
                }
            });
        };

        return {
            filterDepartment, filterCategory, filterStatus,
            onDepartmentChange, applyFilter, handleDelete, isLoading,
            showImportModal, importFile, isImporting, handleImport
        };
    },
};
</script>