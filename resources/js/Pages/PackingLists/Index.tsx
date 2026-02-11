import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Dialog } from '@/Components/ui/Dialog';
import { Input } from '@/Components/ui/Input';
import { Head, router, useForm } from '@inertiajs/react';
import { PackingList } from '@/types';
import { useState, FormEventHandler } from 'react';
import { Luggage, Plus, MapPin, Calendar } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    packingLists: PackingList[];
}

export default function Index({ packingLists }: Props) {
    const { t } = useTranslation();
    const [showCreate, setShowCreate] = useState(false);
    const { data, setData, post, processing, reset, errors } = useForm({
        name: '',
        destination: '',
        start_date: '',
        end_date: '',
        notes: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('packing-lists.store'), {
            onSuccess: () => { reset(); setShowCreate(false); },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('packing.title')} />

            <PageHeader
                title={t('packing.title')}
                description={t('packing.subtitle')}
                actions={
                    <Button onClick={() => setShowCreate(true)}>
                        <Plus className="h-4 w-4" /> {t('packing.create')}
                    </Button>
                }
            />

            {packingLists.length === 0 ? (
                <EmptyState
                    icon={Luggage}
                    title={t('packing.empty')}
                    description={t('packing.emptyDesc')}
                    action={<Button onClick={() => setShowCreate(true)}><Plus className="h-4 w-4" /> {t('packing.create')}</Button>}
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {packingLists.map((list) => (
                        <Card key={list.id} className="cursor-pointer hover:shadow-medium transition-shadow"
                            onClick={() => router.visit(route('packing-lists.show', list.id))}>
                            <CardBody>
                                <h3 className="text-body-sm font-medium text-surface-900">{list.name}</h3>
                                {list.destination && (
                                    <p className="text-caption text-surface-500 flex items-center gap-1 mt-1">
                                        <MapPin className="h-3 w-3" /> {list.destination}
                                    </p>
                                )}
                                {list.start_date && (
                                    <p className="text-caption text-surface-400 flex items-center gap-1 mt-1">
                                        <Calendar className="h-3 w-3" /> {list.start_date}{list.end_date ? ` - ${list.end_date}` : ''}
                                    </p>
                                )}
                                <div className="mt-3 flex items-center justify-between">
                                    <span className="text-caption text-surface-400">
                                        {t('packing.packedCount', { packed: list.packed_count, total: list.total_count })}
                                    </span>
                                    {list.total_count > 0 && (
                                        <div className="w-16 h-1.5 bg-surface-200 rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-brand-600 rounded-full transition-all"
                                                style={{ width: `${(list.packed_count / list.total_count) * 100}%` }}
                                            />
                                        </div>
                                    )}
                                </div>
                            </CardBody>
                        </Card>
                    ))}
                </div>
            )}

            <Dialog open={showCreate} onClose={() => { reset(); setShowCreate(false); }} title={t('packing.create')} size="md">
                <form onSubmit={submit} className="space-y-4">
                    <Input label={t('packing.name')} value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder={t('packing.namePlaceholder')} error={errors.name} />
                    <Input label={t('packing.destination')} value={data.destination} onChange={(e) => setData('destination', e.target.value)} placeholder={t('packing.destPlaceholder')} />
                    <div className="grid grid-cols-2 gap-4">
                        <Input label={t('packing.startDate')} type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                        <Input label={t('packing.endDate')} type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                    </div>
                    <div>
                        <label className="block text-body-sm font-medium text-surface-700 mb-1">{t('packing.notes')}</label>
                        <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} placeholder={t('packing.notesPlaceholder')} className="w-full rounded-input border border-surface-300 px-3 py-2 text-body-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500" rows={2} />
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="ghost" onClick={() => { reset(); setShowCreate(false); }}>{t('common.cancel')}</Button>
                        <Button type="submit" loading={processing}>{t('common.save')}</Button>
                    </div>
                </form>
            </Dialog>
        </AuthenticatedLayout>
    );
}
