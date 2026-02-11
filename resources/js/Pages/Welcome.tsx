import { Head, Link, usePage } from '@inertiajs/react';
import { Shirt, Camera, Wand2, Sparkles, ArrowRight, Upload, Eye } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function Welcome() {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const isLoggedIn = !!auth?.user;

    return (
        <>
            <Head title={t('welcome.pageTitle')} />
            <div className="min-h-screen bg-white">
                {/* Navbar */}
                <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-surface-100">
                    <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                        <div className="flex items-center gap-2.5">
                            <div className="flex items-center justify-center h-9 w-9 rounded-xl bg-brand-600">
                                <Shirt className="h-5 w-5 text-white" />
                            </div>
                            <span className="text-heading-sm text-surface-900">{t('nav.brandName')}</span>
                        </div>
                        <div className="flex items-center gap-3">
                            {isLoggedIn ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center gap-2 rounded-button bg-brand-600 px-4 py-2 text-body-sm font-medium text-white hover:bg-brand-700 transition-colors"
                                >
                                    {t('welcome.dashboard')} <ArrowRight className="h-4 w-4" />
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="rounded-button px-4 py-2 text-body-sm font-medium text-surface-700 hover:text-surface-900 hover:bg-surface-50 transition-colors"
                                    >
                                        {t('welcome.logIn')}
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="rounded-button bg-brand-600 px-4 py-2 text-body-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-soft"
                                    >
                                        {t('welcome.getStarted')}
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </nav>

                {/* Hero */}
                <section className="pt-32 pb-20 px-4">
                    <div className="max-w-4xl mx-auto text-center">
                        <div className="inline-flex items-center gap-2 rounded-pill bg-brand-50 px-4 py-1.5 text-body-sm font-medium text-brand-700 mb-6">
                            <Sparkles className="h-4 w-4" /> {t('welcome.badge')}
                        </div>
                        <h1 className="text-display-lg sm:text-display-lg text-surface-900 mb-6">
                            {t('welcome.heroTitle1')}
                            <span className="bg-gradient-to-r from-brand-600 via-purple-600 to-rose-500 bg-clip-text text-transparent">
                                {t('welcome.heroTitle2')}
                            </span>
                        </h1>
                        <p className="text-body-lg text-surface-500 max-w-2xl mx-auto mb-10">
                            {t('welcome.heroDesc')}
                        </p>
                        <div className="flex items-center justify-center gap-4">
                            <Link
                                href={isLoggedIn ? route('tryon.index') : route('register')}
                                className="inline-flex items-center gap-2 rounded-button bg-brand-600 px-6 py-3 text-body font-semibold text-white hover:bg-brand-700 shadow-soft hover:shadow-medium transition-all"
                            >
                                {t('welcome.tryItFree')} <ArrowRight className="h-5 w-5" />
                            </Link>
                            <a
                                href="#how-it-works"
                                className="inline-flex items-center gap-2 rounded-button border border-surface-200 px-6 py-3 text-body font-semibold text-surface-700 hover:bg-surface-50 transition-colors"
                            >
                                {t('welcome.howItWorks')}
                            </a>
                        </div>
                    </div>
                </section>

                {/* How It Works */}
                <section id="how-it-works" className="py-20 bg-surface-50 px-4">
                    <div className="max-w-5xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-heading-xl text-surface-900 mb-3">{t('welcome.howItWorks')}</h2>
                            <p className="text-body-lg text-surface-500">{t('welcome.howSubtitle')}</p>
                        </div>
                        <div className="grid md:grid-cols-3 gap-8">
                            {[
                                {
                                    step: 1,
                                    titleKey: 'welcome.step1Title',
                                    descKey: 'welcome.step1Desc',
                                    icon: Upload,
                                    color: 'bg-blue-50 text-blue-600',
                                },
                                {
                                    step: 2,
                                    titleKey: 'welcome.step2Title',
                                    descKey: 'welcome.step2Desc',
                                    icon: Shirt,
                                    color: 'bg-emerald-50 text-emerald-600',
                                },
                                {
                                    step: 3,
                                    titleKey: 'welcome.step3Title',
                                    descKey: 'welcome.step3Desc',
                                    icon: Eye,
                                    color: 'bg-purple-50 text-purple-600',
                                },
                            ].map((item) => (
                                <div key={item.step} className="text-center">
                                    <div className="relative inline-flex mb-6">
                                        <div className={`flex items-center justify-center h-16 w-16 rounded-2xl ${item.color}`}>
                                            <item.icon className="h-8 w-8" />
                                        </div>
                                        <span className="absolute -top-2 -right-2 flex items-center justify-center h-7 w-7 rounded-full bg-brand-600 text-white text-caption font-bold">
                                            {item.step}
                                        </span>
                                    </div>
                                    <h3 className="text-heading-sm text-surface-900 mb-2">{t(item.titleKey)}</h3>
                                    <p className="text-body-sm text-surface-500">{t(item.descKey)}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Features */}
                <section className="py-20 px-4">
                    <div className="max-w-5xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-heading-xl text-surface-900 mb-3">{t('welcome.whyTitle')}</h2>
                            <p className="text-body-lg text-surface-500">{t('welcome.whySubtitle')}</p>
                        </div>
                        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {[
                                { titleKey: 'welcome.feat1Title', descKey: 'welcome.feat1Desc', icon: Camera, key: 'feat1' },
                                { titleKey: 'welcome.feat2Title', descKey: 'welcome.feat2Desc', icon: Shirt, key: 'feat2' },
                                { titleKey: 'welcome.feat3Title', descKey: 'welcome.feat3Desc', icon: Sparkles, key: 'feat3' },
                                { titleKey: 'welcome.feat4Title', descKey: 'welcome.feat4Desc', icon: Eye, key: 'feat4' },
                                { titleKey: 'welcome.feat5Title', descKey: 'welcome.feat5Desc', icon: Upload, key: 'feat5' },
                                { titleKey: 'welcome.feat6Title', descKey: 'welcome.feat6Desc', icon: Wand2, key: 'feat6' },
                            ].map((feature) => (
                                <div key={feature.key} className="rounded-card border border-surface-200 p-6 hover:shadow-soft transition-shadow">
                                    <div className="flex items-center justify-center h-11 w-11 rounded-xl bg-brand-50 mb-4">
                                        <feature.icon className="h-5 w-5 text-brand-600" />
                                    </div>
                                    <h3 className="text-heading-sm text-surface-900 mb-1.5">{t(feature.titleKey)}</h3>
                                    <p className="text-body-sm text-surface-500">{t(feature.descKey)}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="py-20 px-4 bg-gradient-to-b from-brand-50 to-white">
                    <div className="max-w-2xl mx-auto text-center">
                        <h2 className="text-heading-xl text-surface-900 mb-4">{t('welcome.ctaTitle')}</h2>
                        <p className="text-body-lg text-surface-500 mb-8">
                            {t('welcome.ctaDesc')}
                        </p>
                        <Link
                            href={isLoggedIn ? route('tryon.index') : route('register')}
                            className="inline-flex items-center gap-2 rounded-button bg-brand-600 px-8 py-3.5 text-body font-semibold text-white hover:bg-brand-700 shadow-soft hover:shadow-medium transition-all"
                        >
                            {t('welcome.ctaButton')} <ArrowRight className="h-5 w-5" />
                        </Link>
                    </div>
                </section>

                {/* Footer */}
                <footer className="py-8 border-t border-surface-100">
                    <div className="max-w-6xl mx-auto px-4 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Shirt className="h-5 w-5 text-brand-600" />
                            <span className="text-body-sm font-semibold text-surface-700">{t('nav.brandName')}</span>
                        </div>
                        <p className="text-caption text-surface-400">{t('welcome.footerPowered')}</p>
                    </div>
                </footer>
            </div>
        </>
    );
}
