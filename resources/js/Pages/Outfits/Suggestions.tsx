import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Tabs } from '@/Components/ui/Tabs';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Spinner } from '@/Components/ui/Spinner';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { OutfitSuggestion, WardrobeStats } from '@/types';
import { useState } from 'react';
import { Sparkles, Wand2, Heart, Bookmark } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    suggestions: OutfitSuggestion[];
    wardrobeStats: WardrobeStats;
    currentOccasion: string | null;
}

export default function Suggestions({ suggestions, wardrobeStats, currentOccasion }: Props) {
    const { t } = useTranslation();
    const [selectedOccasion, setSelectedOccasion] = useState(currentOccasion || 'casual');
    const { post, processing } = useForm({});

    const occasions = [
        { id: 'casual', label: t('outfits.occasionCasual') },
        { id: 'formal', label: t('outfits.occasionFormal') },
        { id: 'business', label: t('outfits.occasionBusiness') },
        { id: 'date', label: t('outfits.occasionDate') },
        { id: 'party', label: t('outfits.occasionParty') },
        { id: 'workout', label: t('outfits.occasionWorkout') },
    ];

    const handleGenerate = () => {
        post(route('outfits.generate', { occasion: selectedOccasion }));
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('outfits.suggestionsTitle')} />

            <PageHeader
                title={t('outfits.suggestionsTitle')}
                description={t('outfits.suggestionsDesc')}
                actions={
                    <Link href={route('outfits.saved')} className="text-body-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                        <Bookmark className="h-4 w-4" /> {t('outfits.saved')}
                    </Link>
                }
            />

            {/* Occasion Tabs */}
            <Tabs
                tabs={occasions.map((o) => ({ id: o.id, label: o.label }))}
                activeTab={selectedOccasion}
                onChange={setSelectedOccasion}
                variant="pill"
                className="mb-4"
            />

            <Button onClick={handleGenerate} loading={processing} className="mb-6">
                <Sparkles className="h-4 w-4" /> {t('outfits.generateSuggestions')}
            </Button>

            {suggestions.length === 0 ? (
                <EmptyState
                    icon={Sparkles}
                    title={t('outfits.noSuggestions')}
                    description={t('outfits.noSuggestionsDesc')}
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {suggestions.map((suggestion) => (
                        <Card key={suggestion.id}>
                            <CardBody>
                                {/* Garment thumbnails */}
                                <div className="flex gap-2 mb-3">
                                    {suggestion.garments.map((g) => (
                                        <div key={g.id} className="relative">
                                            <img
                                                src={g.thumbnail_url || ''}
                                                alt={g.name}
                                                className="h-16 w-16 rounded-lg object-cover bg-surface-50"
                                                loading="lazy"
                                            />
                                            <Badge variant="neutral" size="sm" className="absolute -bottom-1 -right-1">
                                                {g.category === 'upper' ? t('outfits.catTop') : g.category === 'lower' ? t('outfits.catBottom') : t('outfits.catDress')}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>

                                <p className="text-body-sm text-surface-700 mb-3">{suggestion.suggestion_text}</p>

                                {suggestion.occasion && (
                                    <Badge variant="brand" size="sm" className="mb-3">{suggestion.occasion}</Badge>
                                )}

                                <div className="flex items-center gap-2">
                                    <Button
                                        variant={suggestion.is_saved ? 'primary' : 'outline'}
                                        size="sm"
                                        onClick={() => router.patch(route('outfits.save', suggestion.id))}
                                    >
                                        <Bookmark className={`h-3.5 w-3.5 ${suggestion.is_saved ? 'fill-current' : ''}`} />
                                        {suggestion.is_saved ? t('outfits.saved') : t('outfits.save')}
                                    </Button>
                                    <Link href={`${route('tryon.index')}?garment_ids=${suggestion.garment_ids.join(',')}`}>
                                        <Button variant="ghost" size="sm">
                                            <Wand2 className="h-3.5 w-3.5" /> {t('outfits.tryOn')}
                                        </Button>
                                    </Link>
                                </div>
                            </CardBody>
                        </Card>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
