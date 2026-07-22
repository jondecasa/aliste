import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Karla', ...defaultTheme.fontFamily.sans],
                serif: ['Newsreader', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                crema: {
                    DEFAULT: 'var(--color-crema)',
                    suave: 'var(--color-crema-suave)',
                },
                tinta: {
                    DEFAULT: 'var(--color-tinta)',
                    muted: 'var(--color-tinta-muted)',
                    borde: 'var(--color-tinta-borde)',
                },
                terracota: {
                    DEFAULT: 'var(--color-terracota)',
                    dark: 'var(--color-terracota-dark)',
                },
                verde: {
                    DEFAULT: 'var(--color-verde)',
                },
            },
        },
    },

    plugins: [forms, typography],
};
