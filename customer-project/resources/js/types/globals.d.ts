import type { route as routeFn } from 'ziggy-js';
import type { Page } from '@inertiajs/intertia';
declare global {
    const route: typeof routeFn;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
      $page: Page;
    }
}
