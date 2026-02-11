import '../css/app.css';
import './bootstrap';
import './i18n';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import i18n from './i18n';
import { router } from '@inertiajs/react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Sync locale from Inertia shared props on navigation
router.on('navigate', (event) => {
    const locale = (event.detail.page.props as any).locale;
    if (locale && locale !== i18n.language) {
        i18n.changeLanguage(locale);
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        // Sync initial locale
        const initialLocale = (props.initialPage.props as any).locale;
        if (initialLocale) {
            i18n.changeLanguage(initialLocale);
        }

        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
