import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { router } from '@inertiajs/react';
import { Lookbook } from '@/types';
import { BookOpen } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    open: boolean;
    onClose: () => void;
    lookbooks: Lookbook[];
    itemableType: 'tryon_result' | 'outfit_suggestion';
    itemableId: number;
}

export default function AddToLookbookDialog({ open, onClose, lookbooks, itemableType, itemableId }: Props) {
    const { t } = useTranslation();

    const handleSelect = (lookbookId: number) => {
        router.post(route('lookbooks.items.add', lookbookId), {
            itemable_type: itemableType,
            itemable_id: itemableId,
        }, {
            onSuccess: () => onClose(),
        });
    };

    return (
        <Dialog open={open} onClose={onClose} title={t('lookbooks.selectLookbook')} size="sm">
            {lookbooks.length === 0 ? (
                <p className="text-body-sm text-surface-500 py-4 text-center">{t('lookbooks.empty')}</p>
            ) : (
                <div className="space-y-2">
                    {lookbooks.map((lookbook) => (
                        <button
                            key={lookbook.id}
                            onClick={() => handleSelect(lookbook.id)}
                            className="flex items-center gap-3 w-full p-3 rounded-card border border-surface-200 hover:border-brand-400 hover:bg-brand-50 transition-colors text-left"
                        >
                            <BookOpen className="h-5 w-5 text-surface-400" />
                            <div>
                                <p className="text-body-sm font-medium text-surface-900">{lookbook.name}</p>
                                <p className="text-caption text-surface-400">{t('lookbooks.itemCount', { count: lookbook.items_count })}</p>
                            </div>
                        </button>
                    ))}
                </div>
            )}
        </Dialog>
    );
}
