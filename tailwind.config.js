import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Enums/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                display: ['"Baloo 2"', 'Nunito', ...defaultTheme.fontFamily.sans],
                script: ['"Dancing Script"', 'cursive'],
            },

            colors: {
                'sibaya-navy': {
                    50: '#eef3fb',
                    100: '#dce7f6',
                    200: '#b9cfec',
                    500: '#2c5a9e',
                    600: '#1f4580',
                    700: '#183a6e',
                    800: '#132f5a',
                    900: '#0e2447',
                },

                'sibaya-gold': {
                    300: '#f3d57a',
                    400: '#edc04f',
                    500: '#e5ab2a',
                    600: '#c98f14',
                    DEFAULT: '#C9A227',
                },

                'sibaya-cream': {
                    50: '#fffdf8',
                    100: '#fbf8f0',
                    200: '#f4eedd',
                },

                'sibaya-ink': '#263238',

                'sibaya-gold-soft': '#F3D27A',

                'sibaya-sky': {
                    100: '#e0f2fe',
                    200: '#bae6fd',
                },
            },

            outlineColor: {
                'sibaya-gold': '#C9A227',
            },
        },
    },

    plugins: [forms],
};