import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

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
                sans: ['Karla', ...defaultTheme.fontFamily.sans],
                serif: ['Newsreader', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                crema: {
                    DEFAULT: 'oklch(97% 0.015 70)',
                    suave: 'oklch(94% 0.005 250)',
                },
                tinta: {
                    DEFAULT: 'oklch(22% 0.02 60)',
                    muted: 'oklch(45% 0.02 60)',
                    borde: 'oklch(85% 0.02 60)',
                },
                terracota: {
                    DEFAULT: 'oklch(50% 0.14 40)',
                    dark: 'oklch(40% 0.14 40)',
                },
                verde: {
                    DEFAULT: 'oklch(55% 0.14 130)',
                },
            },
        },
    },

    plugins: [forms, typography],
};
