<template>
    <Head>
        <title>{{ $t('stock_transfer.title') }} - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fa fa-exchange-alt me-1"></i> {{ $t('stock_transfer.heading') }}
                                </h6>
                                <Link v-if="hasAnyPermission(['stock_ins.create'])" href="/apps/stock-transfers/create" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus me-1"></i> {{ $t('stock_transfer.add') }}
                                </Link>
                            </div>
                            <div class="card-body">

                                <!-- Filters -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" v-model="filters.status" @change="applyFilter">
                                            <option value="">{{ $t('stock_transfer.filter_all_status') }}</option>
                                            <option value="pending">{{ $t('stock_transfer.status_pending') }}</option>
                                            <option value="approved">{{ $t('stock_transfer.status_approved') }}</option>
                                            <option value="rejected">{{ $t('stock_transfer.status_rejected') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" v-model="filters.department_id" @change="applyFilter">
                                            <option value="">{{ $t('stock_transfer.filter_all_dept') }}</option>
                                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ $t('stock_transfer.col_ref') }}</th>
                                                <th>{{ $t('stock_transfer.material') }}</th>
                                                <th>{{ $t('stock_transfer.col_from') }}</th>
                                                <th>{{ $t('stock_transfer.col_to') }}</th>
                                                <th>{{ $t('stock_transfer.col_qty') }}</th>
                                                <th>{{ $t('stock_transfer.col_status') }}</th>
                                                <th>{{ $t('stock_transfer.col_date') }}</th>
                                                <th>{{ $t('stock_transfer.col_action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="transfers.data.length === 0">
                                                <td colspan="8" class="text-center text-muted">{{ $t('stock_transfer.empty') }}</td>
                                            </tr>
                                            <tr v-for="t in transfers.data" :key="t.id">
                                                <td><code>{{ t.reference_no }}</code></td>
                                                <td>{{ t.item?.name ?? '-' }}</td>
                                                <td>{{ t.from_department?.name ?? '-' }}</td>
                                                <td>{{ t.to_department?.name ?? '-' }}</td>
                                                <td class="text-center fw-bold">{{ t.qty }} {{ t.unit }}</td>
                                                <td class="text-center">
                                                    <span class="badge" :class="statusClass(t.status)">{{ t.status }}</span>
                                                </td>
                                                <td>{{ t.created_at }}</td>
                                                <td>
                                                    <Link :href="`/apps/stock-transfers/${t.id}`" class="btn btn-sm btn-outline-info">
                                                        <i class="fa fa-eye"></i>
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div v-if="transfers.last_page > 1" class="d-flex justify-content-center mt-3">
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li v-for="page in transfers.last_page" :key="page" class="page-item" :class="{ active: page === transfers.current_page }">
                                                <Link :href="`?page=${page}`" class="page-link">{{ page }}</Link>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
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
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: {
        transfers: Object,
        departments: Array,
    },
    setup(props) {
        const filters = reactive({
            status: '',
            department_id: '',
        });

        const applyFilter = () => {
            router.get('/apps/stock-transfers', {
                status: filters.status || undefined,
                department_id: filters.department_id || undefined,
            }, { preserveState: true });
        };

        const statusClass = (status) => {
            return {
                pending: 'bg-warning text-dark',
                approved: 'bg-success',
                rejected: 'bg-danger',
            }[status] || 'bg-secondary';
        };

        return { filters, applyFilter, statusClass };
    },
};
</script>
