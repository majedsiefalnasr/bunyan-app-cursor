import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [vue()],
    test: {
        globals: true,
        environment: 'happy-dom',
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
