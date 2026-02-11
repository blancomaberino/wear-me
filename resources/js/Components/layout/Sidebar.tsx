import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import { Avatar } from '@/Components/ui/Avatar';
import { LanguageSwitcher } from '@/Components/ui/LanguageSwitcher';
import {
    LayoutDashboard,
    Camera,
    Shirt,
    Wand2,
    Sparkles,
    User,
    LogOut,
    ChevronDown,
} from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

interface SidebarProps {
    className?: string;
}

function Sidebar({ className }: SidebarProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const user = auth.user;
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    const navItems = [
        { name: t('nav.dashboard'), route: 'dashboard', icon: LayoutDashboard },
        { name: t('nav.myPhotos'), route: 'model-images.index', icon: Camera },
        { name: t('nav.wardrobe'), route: 'wardrobe.index', icon: Shirt },
        { name: t('nav.tryOn'), route: 'tryon.index', icon: Wand2 },
        { name: t('nav.outfits'), route: 'outfits.index', icon: Sparkles },
    ];

    return (
        <aside
            className={cn(
                'fixed left-0 top-0 bottom-0 z-40 flex flex-col bg-white border-r border-surface-200',
                'w-64 lg:w-64',
                className,
            )}
        >
            {/* Logo */}
            <div className="flex items-center gap-2.5 px-6 h-16 border-b border-surface-100">
                <Link href="/" className="flex items-center gap-2.5">
                    <div className="flex items-center justify-center h-9 w-9 rounded-xl bg-brand-600">
                        <Shirt className="h-5 w-5 text-white" />
                    </div>
                    <span className="text-heading-sm text-surface-900">{t('nav.brandName')}</span>
                </Link>
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                {navItems.map((item) => {
                    const isActive = route().current(item.route) || route().current(item.route.replace('.index', '.*'));
                    return (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            className={cn(
                                'flex items-center gap-3 px-3 py-2.5 rounded-button text-body-sm font-medium transition-colors duration-fast',
                                isActive
                                    ? 'bg-brand-50 text-brand-700'
                                    : 'text-surface-600 hover:bg-surface-50 hover:text-surface-900',
                            )}
                        >
                            <item.icon className={cn('h-5 w-5', isActive ? 'text-brand-600' : 'text-surface-400')} />
                            {item.name}
                        </Link>
                    );
                })}
            </nav>

            {/* User section */}
            <div className="border-t border-surface-100 p-3">
                <LanguageSwitcher className="mx-3 mb-2" />
                <div className="relative">
                    <button
                        onClick={() => setUserMenuOpen(!userMenuOpen)}
                        className="flex items-center gap-3 w-full px-3 py-2.5 rounded-button text-left hover:bg-surface-50 transition-colors duration-fast"
                    >
                        <Avatar src={user.avatar} name={user.name} size="sm" />
                        <div className="flex-1 min-w-0">
                            <p className="text-body-sm font-medium text-surface-900 truncate">{user.name}</p>
                            <p className="text-caption text-surface-500 truncate">{user.email}</p>
                        </div>
                        <ChevronDown className={cn('h-4 w-4 text-surface-400 transition-transform', userMenuOpen && 'rotate-180')} />
                    </button>

                    {userMenuOpen && (
                        <div className="absolute bottom-full left-0 right-0 mb-1 bg-white rounded-card border border-surface-200 shadow-medium py-1 animate-scale-in">
                            <Link
                                href={route('profile.edit')}
                                className="flex items-center gap-2 px-3 py-2 text-body-sm text-surface-700 hover:bg-surface-50"
                            >
                                <User className="h-4 w-4" /> {t('nav.profile')}
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="flex items-center gap-2 px-3 py-2 text-body-sm text-red-600 hover:bg-red-50 w-full text-left"
                            >
                                <LogOut className="h-4 w-4" /> {t('nav.logOut')}
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </aside>
    );
}

export { Sidebar };
