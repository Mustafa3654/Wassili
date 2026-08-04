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
                // Warm paper + deep pine ink: a Levantine kitchen palette
                // rather than the usual cool grays.
                paper: {
                    DEFAULT: '#FCF8F2',
                    sunk: '#F4EDE3',
                    edge: '#E7DCCB',
                },
                ink: {
                    DEFAULT: '#10241C',
                    soft: '#3D554A',
                    faint: '#7B8F84',
                },
                // Primary: a deeper, herbier green than stock emerald.
                zaatar: {
                    50:  '#EDF7F1',
                    100: '#D3EADD',
                    200: '#A6D5BC',
                    400: '#3E9670',
                    500: '#237A55',
                    600: '#1B6B4C',
                    700: '#14523A',
                    900: '#0B2E21',
                },
                // Action accent: Levantine citrus. Reserved for "do this now".
                tangerine: {
                    50:  '#FFF2EA',
                    100: '#FFDFCC',
                    400: '#FF8A47',
                    500: '#F26B21',
                    600: '#D95511',
                },
                saffron: {
                    100: '#FDF0CC',
                    500: '#F2B705',
                    700: '#9A7404',
                },
                // Alias kept so existing views keep compiling.
                brand: {
                    50:  '#EDF7F1',
                    500: '#237A55',
                    600: '#1B6B4C',
                    700: '#14523A',
                },
            },
            fontFamily: {
                // Rubik carries the display voice (Arabic + Latin, geometric with
                // slightly rounded terminals); Tajawal keeps body text quiet.
                display: ['Rubik', 'Tajawal', ...defaultTheme.fontFamily.sans],
                sans: ['Tajawal', 'Rubik', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                '4xl': '2rem',
            },
            boxShadow: {
                card: '0 1px 2px rgba(16,36,28,.04), 0 8px 24px -12px rgba(16,36,28,.18)',
                lift: '0 2px 4px rgba(16,36,28,.06), 0 16px 32px -16px rgba(16,36,28,.28)',
            },
            keyframes: {
                rise: {
                    '0%':   { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                rise: 'rise .34s cubic-bezier(.2,.8,.2,1) both',
            },
        },
    },
    plugins: [],
};
