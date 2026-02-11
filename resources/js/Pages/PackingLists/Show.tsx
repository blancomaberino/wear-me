import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { Dialog } from '@/Components/ui/Dialog';
import { Head, router } from '@inertiajs/react';
import { PackingList, Garment } from '@/types';
import { useState } from 'react';
import { ArrowLeft, Plus, Check, Trash2, Luggage } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    packingList: PackingList;
    garments: Garment[];
}

export default function Show({ packingList, garments }: Props) {
    const { t } = useTranslation();
    const [showAddGarment, setShowAddGarment] = useState(false);
    const items = packingList.items || [];

    const handleTogglePacked = async (itemId: number) => {
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';
        await fetch(route('packing-lists.items.toggle', { packingList: packingList.id, item: itemId }), {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        router.reload();
    };

    const handleAddGarment = (garmentId: number) => {
        router.post(route('packing-lists.items.add', packingList.id), { garment_id: garmentId }, {
            onSuccess: () => setShowAddGarment(false),
        });
    };

    const handleRemoveItem = (itemId: number) => {
        router.delete(route('packing-lists.items.remove', { packingList: packingList.id, item: itemId }));
    };

    const handleDelete = () => {
        if (confirm(t('packing.confirmDelete'))) {
            router.delete(route('packing-lists.destroy', packingList.id));
        }
    };

    // Group items by day
    const generalItems = items.filter((i) => i.day_number == null);
    const dayMap = new Map<number, typeof items>();
    items.filter((i) => i.day_number != null).forEach((item) => {
        const day = item.day_number!;
        if (!dayMap.has(day)) dayMap.set(day, []);
        dayMap.get(day)!.push(item);
    });
    const sortedDays = Array.from(dayMap.keys()).sort((a, b) => a - b);

    return (
        <AuthenticatedLayout>
            <Head title={packingList.name} />

            <PageHeader
                title={packingList.name}
                description={packingList.destination || undefined}
                actions={
                    <div className="flex gap-2">
                        <Button variant="ghost" onClick={() => router.visit(route('packing-lists.index'))}>
                            <ArrowLeft className="h-4 w-4" /> {t('common.back')}
                        </Button>
                        <Button onClick={() => setShowAddGarment(true)}>
                            <Plus className="h-4 w-4" /> {t('packing.addGarment')}
                        </Button>
                        <Button variant="outline" size="sm" onClick={handleDelete}>
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    </div>
                }
            />

            <div className="mb-4">
                <span className="text-body-sm text-surface-600">
                    {t('packing.packedCount', { packed: packingList.packed_count, total: packingList.total_count })}
                </span>
            </div>

            {/* General Items */}
            {generalItems.length > 0 && (
                <div className="mb-6">
                    <h3 className="text-heading-sm text-surface-900 mb-3">{t('packing.general')}</h3>
                    <div className="space-y-2">
                        {generalItems.map((item) => (
                            <PackingItem key={item.id} item={item} onToggle={handleTogglePacked} onRemove={handleRemoveItem} t={t} />
                        ))}
                    </div>
                </div>
            )}

            {/* Day-by-day Items */}
            {sortedDays.map((day) => (
                <div key={day} className="mb-6">
                    <h3 className="text-heading-sm text-surface-900 mb-3">{t('packing.dayNumber', { number: day })}</h3>
                    <div className="space-y-2">
                        {dayMap.get(day)!.map((item) => (
                            <PackingItem key={item.id} item={item} onToggle={handleTogglePacked} onRemove={handleRemoveItem} t={t} />
                        ))}
                    </div>
                </div>
            ))}

            {items.length === 0 && (
                <div className="text-center py-8 text-surface-400 text-body-sm">
                    {t('packing.emptyDesc')}
                </div>
            )}

            {/* Add Garment Dialog */}
            <Dialog open={showAddGarment} onClose={() => setShowAddGarment(false)} title={t('packing.addGarment')} size="lg">
                <div className="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-96 overflow-y-auto">
                    {garments.map((g) => (
                        <button
                            key={g.id}
                            onClick={() => handleAddGarment(g.id)}
                            className="text-left rounded-card border border-surface-200 hover:border-brand-400 hover:shadow-soft transition-all overflow-hidden"
                        >
                            <img src={g.thumbnail_url || g.url} alt={g.name || ''} className="w-full h-20 object-cover bg-surface-50" />
                            <div className="p-1.5">
                                <p className="text-caption text-surface-700 truncate">{g.name || g.category}</p>
                            </div>
                        </button>
                    ))}
                </div>
            </Dialog>
        </AuthenticatedLayout>
    );
}

function PackingItem({ item, onToggle, onRemove, t }: any) {
    return (
        <div className="flex items-center gap-3 p-3 rounded-card bg-white border border-surface-200">
            <button onClick={() => onToggle(item.id)} className={`flex-shrink-0 h-5 w-5 rounded-full border-2 flex items-center justify-center transition-colors ${item.is_packed ? 'bg-brand-600 border-brand-600' : 'border-surface-300 hover:border-brand-400'}`}>
                {item.is_packed && <Check className="h-3 w-3 text-white" />}
            </button>
            {item.garment?.thumbnail_url && (
                <img src={item.garment.thumbnail_url} alt="" className="h-10 w-10 rounded-input object-cover bg-surface-50" />
            )}
            <div className="flex-1 min-w-0">
                <p className={`text-body-sm ${item.is_packed ? 'text-surface-400 line-through' : 'text-surface-900'}`}>
                    {item.garment?.name || item.garment?.category || 'Garment'}
                </p>
                {item.occasion && <Badge variant="neutral" size="sm">{item.occasion}</Badge>}
            </div>
            <button onClick={() => onRemove(item.id)} className="text-surface-300 hover:text-red-500 transition-colors">
                <Trash2 className="h-4 w-4" />
            </button>
        </div>
    );
}
