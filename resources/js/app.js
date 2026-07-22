import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
  title: (title) => `${title}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .mixin({
        methods: {
          hasAnyPermission: function (permissions) {
            let allPermissions = this.$page.props.auth?.permissions || [];
            let hasPermission = false;
            permissions.forEach(function (item) {
              if (allPermissions[item]) hasPermission = true;
            });
            return hasPermission;
          }
        }
      })
      .mount(el);
  },
});