import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'brand-red': '#E32B2B',
                'brand-orange': '#F48C25',
                'brand-black': '#000000',
                'brand-dark-gray': '#121212',
                'brand-light-gray': '#F5F5F5',
                'brand-white': '#FFFFFF',
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(90deg, #E32B2B, #F48C25)',
            },
        },
    },

    plugins: [forms],
};
