// @ts-nocheck — Nuxt module option types lag @nuxt/ui / @pinia/nuxt runtime config
export default defineNuxtConfig({
    modules: ['@nuxt/ui', '@nuxtjs/i18n', '@pinia/nuxt'],

    ui: {
        icons: ['heroicons'],
    },

    i18n: {
        locales: [
            { code: 'ar', iso: 'ar-SA', dir: 'rtl', name: 'العربية' },
            { code: 'en', iso: 'en-US', dir: 'ltr', name: 'English' },
        ],
        defaultLocale: 'ar',
        strategy: 'prefix',
        detectBrowserLanguage: false,
        vueI18n: './i18n.config.ts',
    },

    pinia: {
        autoImports: ['defineStore'],
    },

    css: ['~/assets/css/main.css'],

    ssr: true,

    typescript: {
        strict: true,
        typeCheck: true,
    },
});
