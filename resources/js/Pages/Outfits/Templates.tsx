import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, Link } from '@inertiajs/react';
import { OutfitTemplate } from '@/types';
import { Layout } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    templates: OutfitTemplate[];
}

export default function Templates({ templates }: Props) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout>
            <Head title={t('outfits.templatesTitle')} />

            <PageHeader
                title={t('outfits.templatesTitle')}
                description={t('outfits.templatesDesc')}
            />

            <div className="flex gap-2 mb-4">
                <Link href={route('outfits.index')}>
                    <Button variant="ghost" size="sm">{t('outfits.suggestionsTab')}</Button>
                </Link>
                <Button variant="primary" size="sm">{t('outfits.templatesTab')}</Button>
                <Link href={route('my-outfits.index')}>
                    <Button variant="ghost" size="sm">{t('outfits.myOutfitsTab')}</Button>
                </Link>
                <Link href={route('outfits.saved')}>
                    <Button variant="ghost" size="sm">{t('outfits.savedTab')}</Button>
                </Link>
            </div>

            {templates.length === 0 ? (
                <EmptyState icon={Layout} title={t('outfits.noTemplates')} description={t('outfits.noTemplatesDesc')} />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {templates.map((template) => (
                        <Link key={template.id} href={route('my-outfits.index', { template_id: template.id })}>
                            <Card className="hover:shadow-medium transition-shadow cursor-pointer h-full">
                                <CardBody>
                                    <h3 className="text-body font-medium text-surface-900 mb-1">{template.name}</h3>
                                    <Badge variant="brand" size="sm" className="mb-3">{template.occasion}</Badge>
                                    {template.description && (
                                        <p className="text-caption text-surface-500 mb-3">{template.description}</p>
                                    )}
                                    <div className="space-y-1">
                                        <p className="text-caption text-surface-400 font-medium">{t('outfits.fillSlots')}:</p>
                                        {template.slots.map((slot, i) => (
                                            <div key={i} className="flex items-center gap-2">
                                                <span className="text-caption text-surface-600">{slot.label}</span>
                                                <Badge variant={slot.required ? 'brand' : 'neutral'} size="sm">
                                                    {slot.required ? t('outfits.slotRequired') : t('outfits.slotOptional')}
                                                </Badge>
                                            </div>
                                        ))}
                                    </div>
                                </CardBody>
                            </Card>
                        </Link>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
