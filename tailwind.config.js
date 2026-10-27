import defaultTheme from 'tailwindcss-v3/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/legacy/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['NotoEthiopic', 'system-ui', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
