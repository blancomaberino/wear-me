import { Sidebar } from '@/Components/layout/Sidebar';
import { MobileBottomNav } from '@/Components/layout/MobileBottomNav';
import FlashMessages from '@/Components/FlashMessages';
import { Avatar } from '@/Components/ui/Avatar';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

import { useTranslation } from 'react-i18next';

export default function Authenticated({ children }: PropsWithChildren) {
    const { t } = useTranslation();
    const user = (usePage().props as any).auth.user;

    return (
        <div className="min-h-screen bg-surface-50">
            <a href="#main-content" className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-button focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-white focus:text-body-sm focus:shadow-medium">
                {t('nav.skipToContent')}
            </a>
            <FlashMessages />

            {/* Desktop/Tablet Sidebar */}
            <div className="hidden md:block">
                <Sidebar />
            </div>

            {/* Mobile Top Bar */}
            <div className="md:hidden fixed top-0 left-0 right-0 z-30 bg-white border-b border-surface-200">
                <div className="flex items-center justify-between h-14 px-4">
                    <Link href="/" className="flex items-center gap-2">
                        <img src="/icons/logo.png" alt="WearMe" className="h-8 w-8" />
                        <span className="text-heading-sm text-surface-900">{t('nav.brandName')}</span>
                    </Link>
                    <Link href={route('profile.edit')}>
                        <Avatar src={user.avatar} name={user.name} size="sm" />
                    </Link>
                </div>
            </div>

            {/* Main Content */}
            <main id="main-content" className="md:ml-64 min-h-screen">
                <div className="pt-14 md:pt-0 pb-20 md:pb-0">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6 animate-fade-in">
                        {children}
                    </div>
                </div>
            </main>

            {/* Mobile Bottom Nav */}
            <MobileBottomNav />
        </div>
    );
}
