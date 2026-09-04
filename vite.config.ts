import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defaultAllowedOrigins, defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
  // The third argument lifts the prefix filter so the Laravel .env is read
  // as it stands, rather than only its VITE_ keys.
  const env = loadEnv(mode, process.cwd(), '');

  // The browser has to reach the HMR socket by a name it can resolve, and
  // that name differs per machine: localhost locally, the LAN hostname on
  // the dev server.
  const hmrHost = env.VITE_HMR_HOST || 'localhost';

  // Vite only serves assets to localhost by default, so the same host the
  // browser loads the app from has to be allowed explicitly or every module
  // request is blocked by CORS. The scheme is left open because the app is
  // reached over either depending on the machine.
  const hmrOrigin = new RegExp(`^https?://${hmrHost.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}(?::\\d+)?$`);

  return {
    server: {
      host: '0.0.0.0',
      port: 5173,
      cors: {
        origin: [defaultAllowedOrigins, hmrOrigin, ...(env.APP_URL ? [env.APP_URL] : [])],
      },
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
