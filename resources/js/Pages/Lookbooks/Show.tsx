import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, router } from '@inertiajs/react';
import { Lookbook, LookbookItem } from '@/types';
import { BookOpen, Trash2, ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    lookbook: Lookbook;
}

export default function Show({ lookbook }: Props) {
    const { t } = useTranslation();
    const items = lookbook.items || [];

    const handleRemoveItem = (itemId: number) => {
        router.delete(route('lookbooks.items.remove', { lookbook: lookbook.id, item: itemId }));
    };

    const handleDelete = () => {
        if (confirm(t('lookbooks.confirmDelete'))) {
            router.delete(route('lookbooks.destroy', lookbook.id));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={lookbook.name} />

            <PageHeader
                title={lookbook.name}
                description={lookbook.description || undefined}
                actions={
                    <div className="flex gap-2">
                        <Button variant="ghost" onClick={() => router.visit(route('lookbooks.index'))}>
                            <ArrowLeft className="h-4 w-4" /> {t('common.back')}
                        </Button>
                        <Button variant="outline" size="sm" onClick={handleDelete}>
                            <Trash2 className="h-4 w-4" /> {t('lookbooks.deleteLookbook')}
                        </Button>
                    </div>
                }
            />

            {items.length === 0 ? (
                <EmptyState
                    icon={BookOpen}
                    title={t('lookbooks.empty')}
                    description={t('lookbooks.emptyDesc')}
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {items.map((item) => (
                        <Card key={item.id}>
                            <CardBody>
                                {item.item && 'result_url' in item.item && item.item.result_url && (
                                    <img src={item.item.result_url} alt="" className="w-full h-48 object-cover rounded-lg mb-3 bg-surface-50" />
                                )}
                                {item.note && <p className="text-caption text-surface-500 mb-2">{item.note}</p>}
                                <Button variant="ghost" size="sm" onClick={() => handleRemoveItem(item.id)}>
                                    <Trash2 className="h-3.5 w-3.5" /> {t('lookbooks.removeItem')}
                                </Button>
                            </CardBody>
                        </Card>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
