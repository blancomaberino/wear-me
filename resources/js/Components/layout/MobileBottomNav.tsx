import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import { LayoutDashboard, Camera, Wand2, Shirt, MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { LanguageSwitcher } from '@/Components/ui/LanguageSwitcher';

function MobileBottomNav() {
    const { t } = useTranslation();
    const [showMore, setShowMore] = useState(false);

    const navItems = [
        { name: t('nav.home'), route: 'dashboard', icon: LayoutDashboard },
        { name: t('nav.photos'), route: 'model-images.index', icon: Camera },
        { name: t('nav.tryOn'), route: 'tryon.index', icon: Wand2, prominent: true },
        { name: t('nav.wardrobe'), route: 'wardrobe.index', icon: Shirt },
        { name: t('nav.more'), route: '', icon: MoreHorizontal },
    ];

    const moreItems = [
        { name: t('nav.outfits'), route: 'outfits.index' },
        { name: t('nav.lookbooks'), route: 'lookbooks.index' },
        { name: t('nav.videos'), route: 'videos.index' },
        { name: t('nav.packing'), route: 'packing-lists.index' },
        { name: t('nav.history'), route: 'tryon.history' },
        { name: t('nav.profile'), route: 'profile.edit' },
    ];

    return (
        <>
            {showMore && (
                <div className="fixed inset-0 z-40 bg-black/20" onClick={() => setShowMore(false)} />
            )}

            {showMore && (
                <div className="fixed bottom-[4.5rem] right-4 z-50 bg-white rounded-card border border-surface-200 shadow-large py-1 min-w-[160px] animate-slide-up">
                    {moreItems.map((item) => (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            className="block px-4 py-2.5 text-body-sm text-surface-700 hover:bg-surface-50"
                            onClick={() => setShowMore(false)}
                        >
                            {item.name}
                        </Link>
                    ))}
                    <LanguageSwitcher className="mx-4 my-2" />
                </div>
            )}

            <nav className="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-surface-200 md:hidden safe-area-pb">
                <div className="flex items-center justify-around h-16 px-2">
                    {navItems.map((item) => {
                        if (item.route === '') {
                            return (
                                <button
                                    key="more"
                                    onClick={() => setShowMore(!showMore)}
                                    className="flex flex-col items-center gap-0.5 px-3 py-1 text-surface-400"
                                >
                                    <item.icon className="h-5 w-5" />
                                    <span className="text-[10px] font-medium">{item.name}</span>
                                </button>
                            );
                        }

                        const isActive = route().current(item.route) || route().current(item.route.replace('.index', '.*'));

                        if (item.prominent) {
                            return (
                                <Link
                                    key={item.route}
                                    href={route(item.route)}
                                    className="flex items-center justify-center h-12 w-12 -mt-4 rounded-full bg-brand-600 text-white shadow-medium"
                                >
                                    <item.icon className="h-6 w-6" />
                                </Link>
                            );
                        }

                        return (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                className={cn(
                                    'flex flex-col items-center gap-0.5 px-3 py-1',
                                    isActive ? 'text-brand-600' : 'text-surface-400',
                                )}
                            >
                                <item.icon className="h-5 w-5" />
                                <span className="text-[10px] font-medium">{item.name}</span>
                            </Link>
                        );
                    })}
                </div>
            </nav>
        </>
    );
}

export { MobileBottomNav };
