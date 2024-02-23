import { defineConfig } from 'vitepress'

export default defineConfig({
    title: 'swisnl/php-http-fixture-client',
    description: 'Fixture client for PHP-HTTP',
    base: '/php-http-fixture-client/',
    themeConfig: {
        repo: 'swisnl/php-http-fixture-client',
        editLinks: false,
    },
    vite: {
        server: process.env.IS_DDEV_PROJECT ? {
            strictPort: true,
            host: true,
            hmr: {
                host: process.env.DDEV_HOSTNAME.split(',')[0],
                protocol: 'wss',
            }
        } : {},
    }
})
