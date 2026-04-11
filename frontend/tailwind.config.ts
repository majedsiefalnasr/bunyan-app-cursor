import type { Config } from 'tailwindcss';

export default {
    content: [
        './components/**/*.{js,vue,ts}',
        './layouts/**/*.vue',
        './pages/**/*.vue',
        './plugins/**/*.{js,ts}',
        './app.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Geist', 'system-ui', 'sans-serif'],
                mono: ['Geist Mono', 'monospace'],
            },
        },
    },
    plugins: [],
} satisfies Config;
