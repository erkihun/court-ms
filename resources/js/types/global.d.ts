import type { Config } from 'ziggy-js';
import type { SharedProps } from './index';

declare global {
    interface Window {
        Ziggy: Config;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedProps;
    }
}

export {};
