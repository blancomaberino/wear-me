import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, Link, router } from '@inertiajs/react';
import { OutfitSuggestion } from '@/types';
import { Bookmark, Wand2, Sparkles } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    savedSuggestions: OutfitSuggestion[];
}

export default function Saved({ savedSuggestions }: Props) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout>
            <Head title={t('outfits.savedTitle')} />

            <PageHeader
                title={t('outfits.savedTitle')}
                description={t('outfits.savedCount', { count: savedSuggestions.length })}
                actions={
                    <Link href={route('outfits.index')}>
                        <Button variant="outline"><Sparkles className="h-4 w-4" /> {t('outfits.getMoreSuggestions')}</Button>
                    </Link>
                }
            />

            {savedSuggestions.length === 0 ? (
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
                    {savedSuggestions.map((suggestion) => (
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
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => router.patch(route('outfits.save', suggestion.id))}
                                    >
                                        <Bookmark className="h-3.5 w-3.5 fill-current" /> {t('outfits.unsave')}
                                    </Button>
                                    <Link href={route('tryon.index')}>
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
