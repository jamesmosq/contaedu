import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                display: ['Playfair Display', ...defaultTheme.fontFamily.serif],
            },

            colors: {
                // ── brand → Verde forestal (alias del sistema de diseño) ──
                brand: {
                    50:  '#edf8f2',
                    100: '#d4f0e1',
                    200: '#a7dfc0',
                    300: '#71c99c',
                    400: '#3db872',
                    500: '#279558',
                    600: '#1e7d47',
                    700: '#165e36',
                    800: '#10472a',
                    900: '#0a2e1a',
                    950: '#051a0f',
                },

                // ── accent → Dorado (alias del sistema de diseño) ──
                accent: {
                    50:  '#fffcf0',
                    100: '#fdf3d0',
                    200: '#fae89a',
                    300: '#f0cc5a',
                    400: '#e8b828',
                    500: '#d4a017',
                    600: '#b8860b',
                    700: '#956808',
                    800: '#6e4c06',
                    900: '#4a3204',
                },

                // ── forest — token semántico primario ──
                forest: {
                    950: '#051a0f',
                    900: '#0a2e1a',
                    800: '#10472a',
                    700: '#165e36',
                    600: '#1e7d47',
                    500: '#279558',
                    400: '#3db872',
                    300: '#71c99c',
                    200: '#a7dfc0',
                    100: '#d4f0e1',
                    50:  '#edf8f2',
                },

                // ── gold — acento ──
                gold: {
                    600: '#b8860b',
                    500: '#d4a017',
                    400: '#e8b828',
                    300: '#f0cc5a',
                    100: '#fdf3d0',
                    50:  '#fffcf0',
                },

                // ── cream — fondos neutros cálidos ──
                cream: {
                    300: '#d5d2c3',
                    200: '#e8e6dc',
                    100: '#f3f2ec',
                    50:  '#fafaf7',
                },
            },

            borderRadius: {
                xl:  '14px',
                '2xl': '20px',
                '3xl': '28px',
                '4xl': '36px',
            },

            boxShadow: {
                'card-sm': '0 1px 4px rgba(10,46,26,.07)',
                'card':    '0 4px 20px rgba(10,46,26,.10)',
                'card-lg': '0 12px 48px rgba(10,46,26,.15)',
                'gold':    '0 4px 20px rgba(212,160,23,.35)',
            },
        },
    },

    plugins: [forms],
}
