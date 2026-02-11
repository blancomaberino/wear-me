import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Dialog } from '@/Components/ui/Dialog';
import { Input } from '@/Components/ui/Input';
import { Head, router, useForm } from '@inertiajs/react';
import { Lookbook } from '@/types';
import { useState, FormEventHandler } from 'react';
import { BookOpen, Plus, Trash2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    lookbooks: Lookbook[];
}

export default function Index({ lookbooks }: Props) {
    const { t } = useTranslation();
    const [showCreate, setShowCreate] = useState(false);
    const { data, setData, post, processing, reset, errors } = useForm({
        name: '',
        description: '',
        is_public: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('lookbooks.store'), {
            onSuccess: () => { reset(); setShowCreate(false); },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('lookbooks.title')} />

            <PageHeader
                title={t('lookbooks.title')}
                description={t('lookbooks.subtitle')}
                actions={
                    <Button onClick={() => setShowCreate(true)}>
                        <Plus className="h-4 w-4" /> {t('lookbooks.create')}
                    </Button>
                }
            />

            {lookbooks.length === 0 ? (
                <EmptyState
                    icon={BookOpen}
                    title={t('lookbooks.empty')}
                    description={t('lookbooks.emptyDesc')}
                    action={
                        <Button onClick={() => setShowCreate(true)}>
                            <Plus className="h-4 w-4" /> {t('lookbooks.create')}
                        </Button>
                    }
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {lookbooks.map((lookbook) => (
                        <Card key={lookbook.id} className="cursor-pointer hover:shadow-medium transition-shadow"
                            onClick={() => router.visit(route('lookbooks.show', lookbook.id))}>
                            <div className="aspect-video bg-surface-100 rounded-t-card overflow-hidden">
                                {lookbook.cover_image_url ? (
                                    <img src={lookbook.cover_image_url} alt={lookbook.name} className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center">
                                        <BookOpen className="h-10 w-10 text-surface-300" />
                                    </div>
                                )}
                            </div>
                            <CardBody>
                                <h3 className="text-body-sm font-medium text-surface-900">{lookbook.name}</h3>
                                {lookbook.description && (
                                    <p className="text-caption text-surface-500 mt-1 line-clamp-2">{lookbook.description}</p>
                                )}
                                <p className="text-caption text-surface-400 mt-2">
                                    {t('lookbooks.itemCount', { count: lookbook.items_count })}
                                </p>
                            </CardBody>
                        </Card>
                    ))}
                </div>
            )}

            <Dialog open={showCreate} onClose={() => { reset(); setShowCreate(false); }} title={t('lookbooks.create')} size="md">
                <form onSubmit={submit} className="space-y-4">
                    <Input label={t('lookbooks.name')} value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder={t('lookbooks.namePlaceholder')} error={errors.name} />
                    <div>
                        <label className="block text-body-sm font-medium text-surface-700 mb-1">{t('lookbooks.description')}</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder={t('lookbooks.descPlaceholder')}
                            className="w-full rounded-input border border-surface-300 px-3 py-2 text-body-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                            rows={3}
                        />
                    </div>
                    <label className="flex items-center gap-2">
                        <input type="checkbox" checked={data.is_public} onChange={(e) => setData('is_public', e.target.checked)} className="rounded border-surface-300 text-brand-600 focus:ring-brand-500" />
                        <span className="text-body-sm text-surface-700">{t('lookbooks.makePublic')}</span>
                    </label>
                    <div className="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="ghost" onClick={() => { reset(); setShowCreate(false); }}>{t('common.cancel')}</Button>
                        <Button type="submit" loading={processing}>{t('common.save')}</Button>
                    </div>
                </form>
            </Dialog>
        </AuthenticatedLayout>
    );
}
