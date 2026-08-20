import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import VueApexCharts from 'vue3-apexcharts';
import { createI18n } from 'vue-i18n';
import { route as ziggyRoute, ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy.js';
import en from './locales/en.json';
import id from './locales/id.json';

// Ziggy routes eksternal (resources/js/ziggy.js, hasil `php artisan ziggy:generate`)
// — tanpa inline <script> di blade, jadi CSP script-src tidak butuh 'unsafe-inline'.
window.route = (name, params, absolute) => ziggyRoute(name, params, absolute, Ziggy);

// Get saved locale from localStorage or default to 'id'
const savedLocale = localStorage.getItem('locale') || 'id';

const i18n = createI18n({
    legacy: false,
    locale: savedLocale,
    fallbackLocale: 'en',
    messages: {
        en,
        id,
    },
});

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const userLocale = props.initialPage.props.auth?.user?.locale;
        const initialLocale = localStorage.getItem('locale') || userLocale || 'id';

        if (['id', 'en'].includes(initialLocale)) {
            i18n.global.locale.value = initialLocale;
        }

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(VueApexCharts)
            .use(ZiggyVue, Ziggy)
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

                    // Ziggy route helper (disediakan @routes di app.blade.php)
                    // Bungkus agar tersedia sebagai method komponen (dipakai halaman ERP).
                    route: function (...args) {
                        return window.route(...args);
                    },
                }
            })
            .mount(el);
    },
});
