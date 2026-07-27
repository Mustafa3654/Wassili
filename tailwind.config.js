import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    // Class-based dark mode so the Alpine toggle + localStorage can drive it.
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Filament/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50:  '#ecfdf5',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                },
            },
            fontFamily: {
                // Tajawal renders Arabic + Latin cleanly for both directions.
                sans: ['Tajawal', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
