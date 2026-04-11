export default defineNuxtConfig({
    modules: ['@nuxt/ui', '@nuxtjs/i18n', '@pinia/nuxt'],

    ui: {
        // @ts-expect-error Nuxt UI adds `icons`; default Nuxt `ModuleOptions` typing omits it here.
        icons: ['heroicons'],
    },

    i18n: {
        restructureDir: false,
        bundle: {
            optimizeTranslationDirective: false,
        },
        locales: [
            { code: 'ar', language: 'ar-SA', dir: 'rtl', name: 'العربية' },
            { code: 'en', language: 'en-US', dir: 'ltr', name: 'English' },
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
        },
    },

    ssr: true,

    typescript: {
        strict: true,
        typeCheck: false,
    },
});
