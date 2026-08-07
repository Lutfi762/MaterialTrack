import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import VueApexCharts from 'vue3-apexcharts';

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueApexCharts)
            .mixin({
                methods: {
                    // Cek apakah user memiliki salah satu dari permission yang diberikan
                    hasAnyPermission: function (permissions) {
                        let allPermissions = this.$page.props.auth?.permissions || [];
                        return permissions.some(function (item) {
                            return allPermissions.includes(item);
                        });
                    },

                    // Format angka ke format mata uang (Rp.)
                    formatPrice(value) {
                        let val = (value / 1).toFixed(0).replace('.', ',');
                        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    },
                }
            })
            .mount(el);
    },
});