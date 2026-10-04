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
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                brand: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },

            // OhMyWash brand identity: yellow + black on white/neutral greys.
            colors: {
                brand: {
                    DEFAULT: '#FACC15',
                    light: '#FDE68A',
                    soft: '#FEF9C3',
                    dark: '#A16207',
                    deep: '#713F12',
                },
                ink: {
                    DEFAULT: '#111111',
                    soft: '#1F1F1F',
                    muted: '#374151',
                },
            },

            boxShadow: {
                card: '0 1px 2px rgba(17,17,17,.05), 0 8px 24px rgba(17,17,17,.06)',
                brand: '0 4px 14px rgba(250,204,21,.35)',
            },

            borderRadius: {
                '4xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
