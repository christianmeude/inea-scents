import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                'logo-sans': ['"Josefin Sans"', 'sans-serif'],
                'logo-script': ['"Great Vibes"', 'cursive'],
            },
            colors: {
                burgundy: {
                    50: '#fdf4f5',
                    100: '#fae9eb',
                    200: '#f4d3d7',
                    300: '#ecadb4',
                    400: '#e07e8a',
                    500: '#cf5163',
                    600: '#b7374b',
                    700: '#9a293b',
                    800: '#802635',
                    900: '#6f2431',
                    950: '#3e1018',
                },
                brand: {
                    primary: '#6a4053',
                    muted: '#99868c',
                    cream: '#fdf4f5',
                }
            },
            boxShadow: {
                'ambient': '0 10px 25px -5px rgba(106, 64, 83, 0.05), 0 8px 10px -6px rgba(106, 64, 83, 0.02)',
            }
        },
    },

    plugins: [forms],
};
