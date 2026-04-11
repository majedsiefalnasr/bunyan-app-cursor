import path from 'path';
import { fileURLToPath } from 'url';

import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

const __dirname = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
    // @ts-expect-error Vitest’s nested Vite types differ from root `vite` / Rolldown metadata (runtime OK).
    plugins: [vue()],
    test: {
        globals: true,
        environment: 'happy-dom',
        include: ['tests/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}'],
        passWithNoTests: true,
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
            include: ['components/**/*.vue', 'composables/**/*.ts', 'utils/**/*.ts'],
        },
    },
    resolve: {
        alias: {
            '~': path.resolve(__dirname),
            '@': path.resolve(__dirname),
        },
    },
});
