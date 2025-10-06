import '../css/app.css';
import '@mantine/core/styles.css';

import { MantineColorsTuple, MantineProvider } from '@mantine/core';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Hypermatrix | Gestion salles';

const deepBlue: MantineColorsTuple = [
    '#f1f3f9',
    '#e0e2eb',
    '#bec3d8',
    '#99a2c5',
    '#7a86b5',
    '#6674ac',
    '#5c6ba9',
    '#4c5a94',
    '#435085',
    '#293358'
  ];

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <MantineProvider
                theme={{
                    primaryColor: "deepBlue",
                    primaryShade: {
                        light: 9
                    },
                    colors: {
                        deepBlue
                    }
                }}
            >
                <App {...props} />
            </MantineProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});