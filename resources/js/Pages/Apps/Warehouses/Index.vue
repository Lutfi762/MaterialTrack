<template>
    <Head><title>{{ $t('warehouses.title') }} - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="fw-bold"><i class="fa fa-warehouse me-2 text-primary"></i>{{ $t('warehouses.heading') }}</h4>
                    </div>
                    <div class="col-md-4 text-end">
                        <Link href="/apps/warehouses/create" class="btn btn-primary rounded-pill" v-if="hasAnyPermission(['items.create'])">
                            <i class="fa fa-plus me-1"></i> {{ $t('warehouses.add') }}
                        </Link>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-xl">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ $t('common.code') }}</th>
                                        <th>{{ $t('common.name') }}</th>
                                        <th>{{ $t('common.description') }}</th>
                                        <th>{{ $t('warehouses.items_count') }}</th>
                                        <th>{{ $t('common.status') }}</th>
                                        <th class="text-center">{{ $t('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="warehouse in warehouses.data" :key="warehouse.id">
                                        <td><code>{{ warehouse.code }}</code></td>
                                        <td class="fw-bold">{{ warehouse.name }}</td>
                                        <td>{{ warehouse.address || '-' }}</td>
                                        <td><span class="badge bg-info">{{ warehouse.items_count }} {{ $t('warehouses.items') }}</span></td>
                                        <td>
                                            <span class="badge" :class="warehouse.is_active ? 'bg-success' : 'bg-secondary'">
                                                {{ warehouse.is_active ? $t('common.active') : $t('common.inactive') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <Link :href="'/apps/warehouses/' + warehouse.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" v-if="hasAnyPermission(['items.edit'])">
                                                <i class="fa fa-edit"></i>
                                            </Link>
                                            <button class="btn btn-sm btn-outline-danger" @click="deleteWarehouse(warehouse)" v-if="hasAnyPermission(['items.delete'])">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="warehouses.data.length === 0">
                                        <td colspan="6" class="text-center text-muted py-4">{{ $t('warehouses.empty') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="warehouses.links" />
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: {
        warehouses: Object,
    },
    methods: {
        async deleteWarehouse(warehouse) {
            const confirmed = await confirmDelete(this.$t('warehouses.delete_confirm'));
            if (confirmed) {
                router.delete('/apps/warehouses/' + warehouse.id);
            }
        },
    },
};
</script>
