import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { StatCard } from '@/Components/ui/StatCard';
import { Card, CardBody, CardHeader } from '@/Components/ui/Card';
import { ProgressBar } from '@/Components/ui/ProgressBar';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Badge } from '@/Components/ui/Badge';
import { Head, Link, usePage } from '@inertiajs/react';
import { Camera, Shirt, Wand2, Sparkles, ArrowRight, Heart, Ruler } from 'lucide-react';
import { TryOnResult, WardrobeStats } from '@/types';
import { useTranslation } from 'react-i18next';

interface Props {
    recentTryOns: TryOnResult[];
    wardrobeStats: WardrobeStats;
    modelImageCount: number;
    savedSuggestionCount: number;
    hasMeasurements: boolean;
}

export default function Dashboard({ recentTryOns, wardrobeStats, modelImageCount, savedSuggestionCount, hasMeasurements }: Props) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const hour = new Date().getHours();
    const greeting = hour < 12 ? t('dashboard.goodMorning') : hour < 18 ? t('dashboard.goodAfternoon') : t('dashboard.goodEvening');

    return (
        <AuthenticatedLayout>
            <Head title={t('nav.dashboard')} />

            <PageHeader
                title={`${greeting}, ${auth.user.name.split(' ')[0]}`}
                description={t('dashboard.subtitle')}
            />

            {/* Complete Profile Prompt */}
            {!hasMeasurements && (
                <Link href={route('profile.edit')} className="block mb-6">
                    <Card variant="interactive" className="bg-gradient-to-r from-brand-50 to-surface-50 border-brand-100">
                        <CardBody>
                            <div className="flex items-center gap-4">
                                <div className="flex items-center justify-center h-10 w-10 rounded-xl bg-brand-100">
                                    <Ruler className="h-5 w-5 text-brand-600" />
                                </div>
                                <div className="flex-1">
                                    <p className="text-body-sm font-medium text-surface-900">{t('dashboard.completeProfile')}</p>
                                    <p className="text-caption text-surface-500">{t('dashboard.completeProfileDesc')}</p>
                                </div>
                                <ArrowRight className="h-5 w-5 text-brand-400" />
                            </div>
                        </CardBody>
                    </Card>
                </Link>
            )}

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <StatCard label={t('dashboard.statPhotos')} value={modelImageCount} icon={Camera} href={route('model-images.index')} iconColor="text-blue-600" iconBg="bg-blue-50" />
                <StatCard label={t('dashboard.statWardrobe')} value={wardrobeStats.total} icon={Shirt} href={route('wardrobe.index')} iconColor="text-emerald-600" iconBg="bg-emerald-50" />
                <StatCard label={t('dashboard.statSaved')} value={savedSuggestionCount} icon={Sparkles} href={route('outfits.saved')} iconColor="text-rose-600" iconBg="bg-rose-50" />
            </div>

            {/* Quick Actions */}
            <div className="mb-8">
                <h3 className="text-heading-sm text-surface-900 mb-4">{t('dashboard.quickActions')}</h3>
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    {[
                        { labelKey: 'dashboard.actionTryOn', descKey: 'dashboard.actionTryOnDesc', icon: Wand2, href: route('tryon.index'), gradient: 'from-brand-600 to-brand-700', key: 'tryon' },
                        { labelKey: 'dashboard.actionPhoto', descKey: 'dashboard.actionPhotoDesc', icon: Camera, href: route('model-images.index'), gradient: 'from-blue-600 to-blue-700', key: 'photo' },
                        { labelKey: 'dashboard.actionClothing', descKey: 'dashboard.actionClothingDesc', icon: Shirt, href: route('wardrobe.index'), gradient: 'from-emerald-600 to-emerald-700', key: 'clothing' },
                        { labelKey: 'dashboard.actionSuggestions', descKey: 'dashboard.actionSuggestionsDesc', icon: Sparkles, href: route('outfits.index'), gradient: 'from-rose-500 to-rose-600', key: 'suggestions' },
                    ].map((action) => (
                        <Link key={action.key} href={action.href}>
                            <div className={`rounded-card bg-gradient-to-br ${action.gradient} p-5 text-white hover:shadow-medium transition-shadow h-full`}>
                                <action.icon className="h-6 w-6 mb-3 opacity-90" />
                                <p className="text-body-sm font-semibold">{t(action.labelKey)}</p>
                                <p className="text-caption opacity-80 mt-0.5">{t(action.descKey)}</p>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>

            {/* Wardrobe Breakdown */}
            {wardrobeStats.total > 0 && (
                <Card className="mb-8">
                    <CardHeader>
                        <h3 className="text-heading-sm text-surface-900">{t('dashboard.wardrobeBreakdown')}</h3>
                    </CardHeader>
                    <CardBody>
                        <div className="space-y-4">
                            <ProgressBar label={t('dashboard.tops')} value={wardrobeStats.upper} max={wardrobeStats.total} showValue color="sky" />
                            <ProgressBar label={t('dashboard.bottoms')} value={wardrobeStats.lower} max={wardrobeStats.total} showValue color="emerald" />
                            <ProgressBar label={t('dashboard.dresses')} value={wardrobeStats.dress} max={wardrobeStats.total} showValue color="rose" />
                        </div>
                    </CardBody>
                </Card>
            )}

            {/* Recent Try-Ons */}
            <Card>
                <CardHeader>
                    <div className="flex items-center justify-between">
                        <h3 className="text-heading-sm text-surface-900">{t('dashboard.recentTryOns')}</h3>
                        {recentTryOns.length > 0 && (
                            <Link href={route('tryon.history')} className="text-body-sm text-brand-600 hover:text-brand-700 flex items-center gap-1 font-medium">
                                {t('dashboard.viewAll')} <ArrowRight className="h-4 w-4" />
                            </Link>
                        )}
                    </div>
                </CardHeader>
                <CardBody>
                    {recentTryOns.length === 0 ? (
                        <EmptyState
                            icon={Wand2}
                            title={t('dashboard.noTryOns')}
                            description={t('dashboard.noTryOnsDesc')}
                            action={
                                <Link href={route('tryon.index')}>
                                    <Button>
                                        <Wand2 className="h-4 w-4" /> {t('dashboard.createFirstTryOn')}
                                    </Button>
                                </Link>
                            }
                        />
                    ) : (
                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            {recentTryOns.map((result) => (
                                <Link key={result.id} href={route('tryon.show', result.id)} className="group relative aspect-square rounded-xl overflow-hidden bg-surface-50">
                                    {result.result_url ? (
                                        <img src={result.result_url} alt="Try-on result" className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-normal" loading="lazy" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center">
                                            <div className="h-6 w-6 border-2 border-brand-200 border-t-brand-600 rounded-full animate-spin" />
                                        </div>
                                    )}
                                    {result.is_favorite && (
                                        <div className="absolute top-1.5 right-1.5 p-1 bg-white/80 rounded-full">
                                            <Heart className="h-3 w-3 text-red-500 fill-current" />
                                        </div>
                                    )}
                                </Link>
                            ))}
                        </div>
                    )}
                </CardBody>
            </Card>
        </AuthenticatedLayout>
    );
}
