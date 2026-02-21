import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

// Extend ImportMeta interface for Vite
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Animate the dots in "Loading..."
let dotsInterval: number | null = null;
function animateDots() {
    const dotsElement = document.getElementById('dots');
    if (!dotsElement) return;

    let dotCount = 0;
    dotsInterval = window.setInterval(() => {
        dotCount = (dotCount + 1) % 4;
        dotsElement.textContent = '.'.repeat(dotCount);
    }, 400);
}
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Register all components globally
        const components = import.meta.glob<DefineComponent>(
            './components/**/*.vue',
            { eager: true }
        );

        Object.entries(components).forEach(([path, definition]) => {
            // Extract component name from path (e.g., './components/CustomInput.vue' -> 'CustomInput')
            const componentName = path
                .split('/')
                .pop()
                ?.replace(/\.\w+$/, '') || '';

            // Register component globally if it has a default export
            if (definition.default) {
                app.component(componentName, definition.default);
            }
        });

        app.use(plugin).use(ZiggyVue).mount(el);

    },
});

// Initialize theme on page load
initializeTheme();
