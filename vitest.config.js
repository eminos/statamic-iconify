import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag === 'iconify-icon',
                },
            },
        }),
    ],
    test: {
        environment: 'happy-dom',
        globals: true,
        include: ['tests/js/**/*.test.js'],
    },
    resolve: {
        alias: {
            '@statamic/cms': './tests/js/__mocks__/statamic-cms.js',
            '@statamic/cms/ui': './tests/js/__mocks__/statamic-cms-ui.js',
        },
    },
});
