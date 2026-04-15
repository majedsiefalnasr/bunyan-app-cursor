export default defineNuxtConfig({
    // Avoid Vite DevTools Kit RPC noise/failures when Playwright drives `nuxt dev` (sets PLAYWRIGHT_TEST=1).
    devtools: { enabled: !(process.env.CI || process.env.PLAYWRIGHT_TEST) },

    modules: ['@nuxt/ui', '@nuxtjs/i18n', '@pinia/nuxt'],

    /** Use file basename as tag (`<AppHeader>`), not `ShellAppHeader` from nested dirs. */
    components: [
        {
            path: '~/components',
            pathPrefix: false,
        },
    ],

    app: {
        head: {
            htmlAttrs: { dir: 'rtl', lang: 'ar' },
            link: [
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
                {
                    rel: 'stylesheet',
                    href: 'https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&display=swap',
                },
            ],
        },
    },

    ui: {
        // @ts-expect-error Nuxt UI adds `icons`; default Nuxt `ModuleOptions` typing omits it here.
        icons: ['heroicons'],
    },

    i18n: {
        restructureDir: false,
        langDir: 'locales',
        bundle: {
            optimizeTranslationDirective: false,
        },
        locales: [
            { code: 'ar', language: 'ar-SA', dir: 'rtl', name: 'العربية', file: 'ar.json' },
            { code: 'en', language: 'en-US', dir: 'ltr', name: 'English', file: 'en.json' },
        ],
        defaultLocale: 'ar',
        strategy: 'prefix',
        detectBrowserLanguage: false,
        vueI18n: './i18n.config.ts',
    },

    pinia: {
        // @ts-expect-error @pinia/nuxt adds `autoImports`; base schema may not list it yet.
        autoImports: ['defineStore'],
    },

    css: ['~/assets/css/main.css'],

    runtimeConfig: {
        public: {
            /** Set via `NUXT_PUBLIC_API_BASE_URL` in `.env` (see `.env.example`). */
            apiBaseUrl: '',
            /**
             * True when `nuxt dev` is started with `PLAYWRIGHT_TEST=1` (Playwright webServer).
             * Used by route middleware for SSR/client e2e RBAC edge cases only.
             */
            playwrightTest: process.env.PLAYWRIGHT_TEST === '1',
        },
    },

    ssr: true,

    typescript: {
        strict: true,
        typeCheck: false,
    },
});
