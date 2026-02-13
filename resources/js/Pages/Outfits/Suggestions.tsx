import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Tabs } from '@/Components/ui/Tabs';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, Link, router } from '@inertiajs/react';
import { OutfitSuggestion, Lookbook } from '@/types';
import { useState } from 'react';
import { Sparkles, Wand2, Heart, Bookmark, BookOpen } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { HarmonyBadge } from '@/Components/HarmonyBadge';
import AddToLookbookDialog from '@/Components/AddToLookbookDialog';

interface Props {
    suggestions: OutfitSuggestion[];
    garmentCount: number;
    lookbooks: Lookbook[];
}

export default function Suggestions({ suggestions, garmentCount, lookbooks }: Props) {
    const { t } = useTranslation();
    const [selectedOccasion, setSelectedOccasion] = useState('casual');
    const [generating, setGenerating] = useState(false);
    const [lookbookTarget, setLookbookTarget] = useState<number | null>(null);

    const occasions = [
        { id: 'casual', label: t('outfits.occasionCasual') },
        { id: 'work', label: t('outfits.occasionWork') },
        { id: 'evening', label: t('outfits.occasionEvening') },
        { id: 'sport', label: t('outfits.occasionSport') },
        { id: 'date', label: t('outfits.occasionDate') },
    ];

    const handleGenerate = () => {
        router.post(route('outfits.generate'), { occasion: selectedOccasion }, {
            onStart: () => setGenerating(true),
            onFinish: () => setGenerating(false),
        });
    };

    const filteredSuggestions = suggestions.filter(
        (s) => !s.occasion || s.occasion === selectedOccasion
    );

    return (
        <AuthenticatedLayout>
            <Head title={t('outfits.suggestionsTitle')} />

            <PageHeader
                title={t('outfits.suggestionsTitle')}
                description={t('outfits.suggestionsDesc')}
                actions={
                    <Button onClick={handleGenerate} loading={generating}>
                        <Sparkles className="h-4 w-4" /> {t('outfits.generateSuggestions')}
                    </Button>
                }
            />

            <div className="flex gap-2 mb-4">
                <Button variant="primary" size="sm">{t('outfits.suggestionsTab')}</Button>
                <Link href={route('outfits.templates')}>
                    <Button variant="ghost" size="sm">{t('outfits.templatesTab')}</Button>
                </Link>
                <Link href={route('my-outfits.index')}>
                    <Button variant="ghost" size="sm">{t('outfits.myOutfitsTab')}</Button>
                </Link>
                <Link href={route('outfits.saved')}>
                    <Button variant="ghost" size="sm">{t('outfits.savedTab')}</Button>
                </Link>
            </div>

            {/* Occasion Tabs */}
            <Tabs
                tabs={occasions.map((o) => ({ id: o.id, label: o.label }))}
                activeTab={selectedOccasion}
                onChange={setSelectedOccasion}
                variant="pill"
                className="mb-4"
            />

            {filteredSuggestions.length === 0 ? (
                <EmptyState
                    icon={Sparkles}
                    title={t('outfits.noSuggestions')}
                    description={t('outfits.noSuggestionsDesc')}
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {filteredSuggestions.map((suggestion) => (
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

                                {suggestion.garments.length >= 2 && (
                                    <HarmonyBadge
                                        colors={suggestion.garments.flatMap((g) => (g.color_tags || []).map((c: any) => typeof c === 'string' ? c : c.name))}
                                        score={suggestion.harmony_score}
                                        className="mb-3"
                                    />
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
                                    <Button variant="ghost" size="sm" onClick={(e) => { e.stopPropagation(); setLookbookTarget(suggestion.id); }}>
                                        <BookOpen className="h-3.5 w-3.5" /> {t('lookbooks.addItem')}
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

            <AddToLookbookDialog
                open={lookbookTarget !== null}
                onClose={() => setLookbookTarget(null)}
                lookbooks={lookbooks}
                itemableType="outfit_suggestion"
                itemableId={lookbookTarget!}
            />
        </AuthenticatedLayout>
    );
}
