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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
                script: ['Caveat', 'cursive'],
            },
            colors: {
                // Palet diambil dari 3 gambar referensi
                sibaya: {
                    navy: { 900: '#0A2A55', 800: '#12376B', 700: '#1B4A8A' },
                    gold: { DEFAULT: '#E2AC2F', soft: '#F3D27A' },
                    cream: '#FBF8F1',
                    ink: '#14274A',
                    sky: { 100: '#E8F0FC', 200: '#CFE0F7' },
                    ruang: { bg: '#E8F0FC', line: '#C7D9F4', accent: '#2B3A8F' },
                    gladhen: { bg: '#E6F4E8', line: '#BFE0C5', accent: '#3A7F50' },
                    wahana: { bg: '#FEEBDD', line: '#F7CFB0', accent: '#E0622B', ink: '#8A2F0F' },
                },
            },
        },
    },
    plugins: [forms],
};
