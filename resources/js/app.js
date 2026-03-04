import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;

import Alpine from 'alpinejs';
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
