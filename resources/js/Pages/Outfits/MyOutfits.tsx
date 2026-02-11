import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { HarmonyBadge } from '@/Components/HarmonyBadge';
import { Head, Link, router } from '@inertiajs/react';
import { Outfit } from '@/types';
import { Shirt, Trash2, Layout } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    outfits: Outfit[];
}

export default function MyOutfits({ outfits }: Props) {
    const { t } = useTranslation();

    const handleDelete = (id: number) => {
        if (confirm(t('outfits.deleteOutfit'))) {
            router.delete(route('my-outfits.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('outfits.myOutfitsTitle')} />

            <PageHeader
                title={t('outfits.myOutfitsTitle')}
                description={t('outfits.myOutfitsDesc')}
                actions={
                    <Link href={route('outfits.templates')}>
                        <Button variant="outline">
                            <Layout className="h-4 w-4" /> {t('outfits.browseTemplates')}
                        </Button>
                    </Link>
                }
            />

            {outfits.length === 0 ? (
                <EmptyState
                    icon={Shirt}
                    title={t('outfits.noOutfits')}
                    description={t('outfits.noOutfitsDesc')}
                    action={
                        <Link href={route('outfits.templates')}>
                            <Button><Layout className="h-4 w-4" /> {t('outfits.browseTemplates')}</Button>
                        </Link>
                    }
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {outfits.map((outfit) => (
                        <Card key={outfit.id}>
                            <CardBody>
                                <div className="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 className="text-body-sm font-medium text-surface-900">{outfit.name}</h3>
                                        {outfit.occasion && <Badge variant="brand" size="sm" className="mt-1">{outfit.occasion}</Badge>}
                                    </div>
                                    {outfit.harmony_score != null && (
                                        <HarmonyBadge
                                            colors={outfit.garments?.flatMap((g) => g.color_tags || []) || []}
                                            score={outfit.harmony_score}
                                            showLabel={false}
                                        />
                                    )}
                                </div>

                                <div className="flex gap-2 mb-3">
                                    {outfit.garments?.map((g) => (
                                        <img key={g.id} src={g.thumbnail_url || ''} alt={g.name || ''} className="h-14 w-14 rounded-lg object-cover bg-surface-50" loading="lazy" />
                                    ))}
                                </div>

                                {outfit.notes && <p className="text-caption text-surface-500 mb-3 line-clamp-2">{outfit.notes}</p>}

                                <div className="flex items-center justify-between">
                                    <span className="text-caption text-surface-400">{outfit.created_at}</span>
                                    <Button variant="ghost" size="sm" onClick={() => handleDelete(outfit.id)}>
                                        <Trash2 className="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </CardBody>
                        </Card>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
