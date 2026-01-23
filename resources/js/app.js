import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

const loadAdminChat = () => {
    const hasChat = document.getElementById('admin-chat-form')
        || document.getElementById('admin-chat-messages');

    if (hasChat) {
        import('./admin-chat.js');
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAdminChat);
} else {
    loadAdminChat();
}
