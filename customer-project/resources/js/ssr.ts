import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { route as ziggyRoute, Router } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';


createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: async (name) => {
            const page = await resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')) as { default: DefineComponent};
            return page.default;
        },
        setup({ App, props, plugin }) {
            const app = createSSRApp({ render: () => h(App, props) });

            if (!page.props.ziggy || typeof page.props.ziggy !== 'object') {
                throw new Error('Ziggy config is missing or invalid');
            }
              
            const ziggy = page.props.ziggy as {
                location: string,
                routes: Record<string, any>;
                defaults: Record<string, any>;
            };

            // Configure Ziggy for SSR...
            const ziggyConfig = {
                ...ziggy,
                location: new URL(ziggy.location),
            };

            // Create route function...
            // @ts-ignore
            const route = (name: string, params?: any, absolute?: boolean) => ziggyRoute(name, params, absolute, ziggyConfig);

            // Make route function available globally...
            // @ts-ignore
            app.config.globalProperties.route = route;

            // Make route function available globally for SSR...
            if (typeof window === 'undefined') {
                // @ts-ignore
                global.route = route;
            }

            app.use(plugin);

            return app;
        },
    }),
);
