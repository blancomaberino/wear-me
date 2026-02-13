import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { HarmonyBadge } from '@/Components/HarmonyBadge';
import { Head, Link, router } from '@inertiajs/react';
import { OutfitSuggestion, Lookbook } from '@/types';
import { Bookmark, Wand2, Sparkles, BookOpen } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import AddToLookbookDialog from '@/Components/AddToLookbookDialog';

interface Props {
    suggestions: OutfitSuggestion[];
    lookbooks: Lookbook[];
}

export default function Saved({ suggestions, lookbooks }: Props) {
    const { t } = useTranslation();
    const [lookbookTarget, setLookbookTarget] = useState<number | null>(null);

    return (
        <AuthenticatedLayout>
            <Head title={t('outfits.savedTitle')} />

            <PageHeader
                title={t('outfits.savedTitle')}
                description={t('outfits.savedCount', { count: suggestions.length })}
                actions={
                    <Link href={route('outfits.index')}>
                        <Button variant="outline"><Sparkles className="h-4 w-4" /> {t('outfits.getMoreSuggestions')}</Button>
                    </Link>
                }
            />

            <div className="flex gap-2 mb-4">
                <Link href={route('outfits.index')}>
                    <Button variant="ghost" size="sm">{t('outfits.suggestionsTab')}</Button>
                </Link>
                <Link href={route('outfits.templates')}>
                    <Button variant="ghost" size="sm">{t('outfits.templatesTab')}</Button>
                </Link>
                <Link href={route('my-outfits.index')}>
                    <Button variant="ghost" size="sm">{t('outfits.myOutfitsTab')}</Button>
                </Link>
                <Button variant="primary" size="sm">{t('outfits.savedTab')}</Button>
            </div>

            {suggestions.length === 0 ? (
                <EmptyState
                    icon={Bookmark}
                    title={t('outfits.noSaved')}
                    description={t('outfits.noSavedDesc')}
                    action={
                        <Link href={route('outfits.index')}>
                            <Button variant="outline"><Sparkles className="h-4 w-4" /> {t('outfits.getSuggestions')}</Button>
                        </Link>
                    }
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {suggestions.map((suggestion) => (
                        <Card key={suggestion.id}>
                            <CardBody>
                                <div className="flex gap-2 mb-3">
                                    {suggestion.garments.map((g) => (
                                        <img
                                            key={g.id}
                                            src={g.thumbnail_url || ''}
                                            alt={g.name}
                                            className="h-16 w-16 rounded-lg object-cover bg-surface-50"
                                            loading="lazy"
                                        />
                                    ))}
                                </div>
                                <p className="text-body-sm text-surface-700 mb-3">{suggestion.suggestion_text}</p>
                                {suggestion.occasion && <Badge variant="brand" size="sm" className="mb-3">{suggestion.occasion}</Badge>}

                                {suggestion.garments.length >= 2 && (
                                    <HarmonyBadge
                                        colors={suggestion.garments.flatMap((g: any) => g.color_tags || [])}
                                        score={suggestion.harmony_score}
                                        className="mb-3"
                                    />
                                )}

                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => router.patch(route('outfits.save', suggestion.id))}
                                    >
                                        <Bookmark className="h-3.5 w-3.5 fill-current" /> {t('outfits.unsave')}
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
