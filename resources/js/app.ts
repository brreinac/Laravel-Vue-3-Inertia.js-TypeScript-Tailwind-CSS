import '../css/app.css';
import { createApp, h, type Component } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob<{ default: Component }>('./Pages/**/*.vue', { eager: true });
    const page = pages[`./Pages/${name}.vue`];

    if (!page) throw new Error(`Página Inertia no encontrada: ${name}`);
    return page.default;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      .mount(el);
  },
  progress: { color: '#2563eb' },
});
