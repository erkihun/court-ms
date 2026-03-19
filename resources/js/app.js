import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;

import Alpine from 'alpinejs';

window.themeSystem = function themeSystem() {
    return {
        mode: localStorage.getItem('theme') || 'system',
        media: window.matchMedia('(prefers-color-scheme: dark)'),
        init() {
            this.apply();

            const sync = () => {
                if (this.mode === 'system') {
                    this.apply();
                }
            };

            if (typeof this.media.addEventListener === 'function') {
                this.media.addEventListener('change', sync);
            } else if (typeof this.media.addListener === 'function') {
                this.media.addListener(sync);
            }
        },
        apply() {
            const shouldUseDark = this.mode === 'dark'
                || (this.mode === 'system' && this.media.matches);

            document.documentElement.classList.toggle('dark', shouldUseDark);
            document.documentElement.dataset.theme = this.mode;
        },
        set(mode) {
            this.mode = mode;
            localStorage.setItem('theme', mode);
            this.apply();
        },
        isActive(mode) {
            return this.mode === mode;
        },
    };
};

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.data('themeSystem', window.themeSystem);
    Alpine.start();
}
