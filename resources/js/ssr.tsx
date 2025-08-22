import ReactDOMServer from 'react-dom/server';
import { type RouteName, route } from 'ziggy-js';
import { createElement } from 'react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Create an Express server for SSR
export default function createSSRHandler(url: string) {
    // Set up global route helper if needed
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (global as { route?: any }).route = (name: RouteName, params?: Record<string, unknown>, absolute?: boolean) => {
        return route(name, params, absolute);
    };

    // Render the app to a string
    const appHtml = ReactDOMServer.renderToString(
        createElement('div', { id: 'app' }, 
            createElement('div', { id: 'root' }, 
                // Your application content will be rendered here
                // You can add your components here based on the URL
                createElement('div', null, `Rendering page for URL: ${url}`)
            )
        )
    );

    // Return the rendered HTML
    return {
        html: appHtml,
        title: appName
    };
}
