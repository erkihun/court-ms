import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;

import Alpine from 'alpinejs';
import { ethDatePicker } from './eth-date-picker';

const registerToastStore = (alpine) => {
    if (alpine.store('toasts')) {
        return;
    }

    alpine.store('toasts', {
        items: [],
        _id: 0,

        initFromServer(serverToasts = []) {
            serverToasts.forEach((toast, index) => {
                const id = ++this._id;
                this.items.push({
                    id,
                    message: toast.message,
                    type: toast.type || 'success',
                    details: toast.details || [],
                    show: true,
                });
                setTimeout(() => this.dismiss(id), 4500 + index * 400);
            });
        },

        add(message, type = 'success', duration = 4500) {
            const id = ++this._id;
            this.items.push({ id, message, type, details: [], show: true });
            setTimeout(() => this.dismiss(id), duration);
        },

        dismiss(id) {
            const item = this.items.find((toast) => toast.id === id);
            if (item) {
                item.show = false;
            }
            setTimeout(() => {
                this.items = this.items.filter((toast) => toast.id !== id);
            }, 350);
        },
    });

    window.toast = (message, type, duration) => alpine.store('toasts').add(message, type, duration);
};

window.themeSystem = function themeSystem() {
    return {
        mode: localStorage.getItem('theme') || 'system',
        accent: localStorage.getItem('accent') || 'blue',
        media: window.matchMedia('(prefers-color-scheme: dark)'),
        init() {
            this.apply();
            this.applyAccent();

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
        applyAccent() {
            document.documentElement.dataset.accent = this.accent;
        },
        set(mode) {
            this.mode = mode;
            localStorage.setItem('theme', mode);
            this.apply();
        },
        setAccent(accent) {
            this.accent = accent;
            localStorage.setItem('accent', accent);
            this.applyAccent();
        },
        isActive(mode) {
            return this.mode === mode;
        },
        isAccent(accent) {
            return this.accent === accent;
        },
    };
};

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.data('themeSystem', window.themeSystem);
    Alpine.data('ethDatePicker', ethDatePicker);
    registerToastStore(Alpine);
    Alpine.start();
} else {
    if (typeof window.Alpine.data === 'function') {
        window.Alpine.data('ethDatePicker', ethDatePicker);
    }
    registerToastStore(window.Alpine);
}
