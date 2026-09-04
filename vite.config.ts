import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
  // The third argument lifts the prefix filter so the Laravel .env is read
  // as it stands, rather than only its VITE_ keys.
  const env = loadEnv(mode, process.cwd(), '');

  // The browser has to reach the HMR socket by a name it can resolve, and
  // that name differs per machine: localhost locally, the LAN hostname on
  // the dev server.
  const hmrHost = env.VITE_HMR_HOST || 'localhost';

  return {
    server: {
      host: '0.0.0.0',
      port: 5173,
      hmr: {
        host: hmrHost,
        port: 5173,
      },
    },
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.tsx'],
        ssr: 'resources/js/ssr.tsx',
        refresh: true,
      }),
      react(),
      tailwindcss(),
    ],
    esbuild: {
      jsx: 'automatic',
    },
    resolve: {
      alias: {
        'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
      },
    },
  };
});
