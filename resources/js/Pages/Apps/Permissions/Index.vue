<template>
    <Head>
        <title>Permissions - MaterialTrack</title>
    </Head>

    <div class="dashboard-page">
        <div class="row">
            <div class="col-md-12">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <span class="chart-icon">🔑</span> {{ $t('permissions.heading') }}
                    </div>
                    <div class="chart-card-body">

                        <!-- Search -->
                        <form @submit.prevent="handleSearch" class="mb-3">
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search"
                                    :placeholder="$t('permissions.search_placeholder')"
                                >
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search me-1"></i> {{ $t('common.search') }}
                                </button>
                            </div>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Permission Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="permissions.data.length === 0">
                                        <td colspan="2" class="text-center text-muted">{{ $t('permissions.empty') }}</td>
                                    </tr>
                                    <tr v-for="(permission, index) in permissions.data" :key="permission.id">
                                        <td>{{ permissions.from + index }}</td>
                                        <td>{{ permission.name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-end mt-3">
                            <Pagination :links="permissions.links" align="end" />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },

    props: {
        permissions: Object,
    },

    setup() {
        const search = ref('' || (new URL(document.location)).searchParams.get('q'));

        const handleSearch = () => {
            router.get('/apps/permissions', {
                q: search.value,
            });
        };

        return { search, handleSearch };
    }
}
</script>
