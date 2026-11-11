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
                // Texto general (Breeze ya pone font-sans en el <body>).
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                // Títulos: se usa con la clase font-display.
                display: ['Fredoka', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: '#0B1F5C',
                deep: '#081545',
                action: '#2A4BC0',
                amber: { DEFAULT: '#FFB020', dark: '#E68A00' },
                canvas: '#EEF1FF',
                ink: '#0B1F5C',
                soft: '#4A5578',
                line: '#C9D0EE',
                ok: { DEFAULT: '#15803D', bg: '#E7F6EC' },
                bad: { DEFAULT: '#C62840', bg: '#FDECEF' },
            },
        },
    },

    plugins: [forms],
};
