<template>

    <Head>
        <title>Dashboard - Aplikasi Kasir</title>
    </Head>

    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">

                <!-- Baris 1: Sales Chart & Ringkasan Sales/Profits Today -->
                <div class="row">
                    <div class="col-md-8">
                        <div v-if="hasAnyPermission(['dashboard.sales_chart'])" class="card border-0 rounded-3 shadow border-top-purple">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-chart-bar"></i> SALES CHART 7 DAYS</span>
                            </div>
                            <div class="card-body">
                                <!-- Area Sales Chart -->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div v-if="hasAnyPermission(['dashboard.sales_today'])" class="card border-0 rounded-3 shadow border-top-info mb-4">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-chart-line"></i> SALES TODAY</span>
                            </div>
                            <div class="card-body">
                                <strong>{{ count_sales_today || 0 }}</strong> SALES
                                <hr>
                                <h5 class="fw-bold">Rp. {{ sum_sales_today || 0 }}</h5>
                            </div>
                        </div>

                        <div v-if="hasAnyPermission(['dashboard.profits_today'])" class="card border-0 rounded-3 shadow border-top-success">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-chart-bar"></i> PROFITS TODAY</span>
                            </div>
                            <div class="card-body">
                                <h5 class="fw-bold">Rp. {{ sum_profits_today || 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris 2: Best Selling Product & Product Stock -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div v-if="hasAnyPermission(['dashboard.best_selling_product'])" class="card border-0 rounded-3 shadow border-top-warning">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-chart-pie"></i> BEST SELLING PRODUCT</span>
                            </div>
                            <div class="card-body">
                                <!-- Area Donut Chart -->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div v-if="hasAnyPermission(['dashboard.product_stock'])" class="card border-0 rounded-3 shadow border-top-danger">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-box-open"></i> PRODUCT STOCK</span>
                            </div>
                            <div class="card-body">
                                <!-- Area Limit Stock -->
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
    import { Head } from '@inertiajs/vue3';

    export default {
        layout: LayoutApp,

        components: {
            Head,
        },

        props: {
            count_sales_today: Number,
            sum_sales_today: Number,
            sum_profits_today: Number,
        }
    }
</script>

<style>
.border-top-purple {
    border-top: 3px solid #6f42c1 !important;
}
.border-top-info {
    border-top: 3px solid #0dcaf0 !important;
}
.border-top-success {
    border-top: 3px solid #198754 !important;
}
.border-top-warning {
    border-top: 3px solid #ffc107 !important;
}
.border-top-danger {
    border-top: 3px solid #dc3545 !important;
}
</style>