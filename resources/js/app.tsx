import '../css/app.css';

import { RouterProvider } from 'react-router-dom';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import router from './router';

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('app');
    
    if (container) {
        const root = createRoot(container);
        root.render(<RouterProvider router={router} />);
    }
});

// This will set light / dark mode on load...
initializeTheme();
