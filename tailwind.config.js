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
            },
        },
    },
    /**
     * When these are passed through as part of an object from the BE (check enums),
     * tailwind doesn't recognise them at build time, however if they were already hard-coded,
     * elsewhere in the project (like class="text-gray-800") they would work. Makes sense for,
     * other times where some classes just didn't work for seemingly no reason.
     *
     * todo: when eventually doing a design overhaul, find a way to get around this properly.
     * This fix feels sort of like a plaster and could become messy, quickly. Kinda depends on how enum stuff pans out.
     */
    safelist: [
        'bg-green-200',
        'bg-cyan-200',
    ],
    plugins: [forms],
};
