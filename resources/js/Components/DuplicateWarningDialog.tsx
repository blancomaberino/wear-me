import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { AlertTriangle } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface DuplicateMatch {
    id: number;
    name: string | null;
    thumbnail_url: string | null;
    similarity: number;
}

interface Props {
    open: boolean;
    onClose: () => void;
    duplicates: DuplicateMatch[];
    onKeep: () => void;
    onSkip: () => void;
}

export default function DuplicateWarningDialog({ open, onClose, duplicates, onKeep, onSkip }: Props) {
    const { t } = useTranslation();

    return (
        <Dialog open={open} onClose={onClose} title={t('import.duplicateFound')} size="md">
            <div className="space-y-4">
                <div className="flex items-start gap-3 p-3 rounded-card bg-amber-50 border border-amber-200">
                    <AlertTriangle className="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
                    <p className="text-body-sm text-amber-800">{t('import.duplicateDesc')}</p>
                </div>

                <div className="space-y-2">
                    {duplicates.map((dup) => (
                        <div key={dup.id} className="flex items-center gap-3 p-2 rounded-input bg-surface-50">
                            {dup.thumbnail_url ? (
                                <img src={dup.thumbnail_url} alt={dup.name || ''} className="w-12 h-12 object-cover rounded-input" />
                            ) : (
                                <div className="w-12 h-12 rounded-input bg-surface-200" />
                            )}
                            <div className="flex-1 min-w-0">
                                <p className="text-body-sm text-surface-900 truncate">{dup.name || t('import.unnamedGarment')}</p>
                                <p className="text-caption text-amber-600 font-medium">
                                    {t('import.duplicateMatch', { percent: dup.similarity })}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex justify-end gap-3 pt-2">
                    <Button variant="ghost" onClick={() => { onSkip(); onClose(); }}>{t('import.duplicateSkip')}</Button>
                    <Button onClick={() => { onKeep(); onClose(); }}>{t('import.duplicateKeep')}</Button>
                </div>
            </div>
        </Dialog>
    );
}
