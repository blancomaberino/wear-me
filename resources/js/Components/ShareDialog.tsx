import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { useState } from 'react';
import { Link2, Copy, Check } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface Props {
    open: boolean;
    onClose: () => void;
    shareableType: 'lookbook' | 'tryon_result';
    shareableId: number;
}

export default function ShareDialog({ open, onClose, shareableType, shareableId }: Props) {
    const { t } = useTranslation();
    const [loading, setLoading] = useState(false);
    const [shareUrl, setShareUrl] = useState<string | null>(null);
    const [copied, setCopied] = useState(false);
    const [expiresIn, setExpiresIn] = useState('never');
    const [error, setError] = useState<string | null>(null);

    const expiryOptions = [
        { value: 'never', label: t('share.expiresNever') },
        { value: '1_day', label: t('share.expires1Day') },
        { value: '7_days', label: t('share.expires7Days') },
        { value: '30_days', label: t('share.expires30Days') },
    ];

    const handleCreate = async () => {
        setLoading(true);
        setError(null);
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';
        try {
            const response = await fetch(route('share.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    shareable_type: shareableType,
                    shareable_id: shareableId,
                    expires_in: expiresIn,
                }),
            });
            if (!response.ok) throw new Error('Failed to create share link');
            const data = await response.json();
            if (data.link?.url) {
                setShareUrl(data.link.url);
            }
        } catch {
            setError(t('share.error'));
        } finally {
            setLoading(false);
        }
    };

    const handleCopy = async () => {
        if (shareUrl) {
            await navigator.clipboard.writeText(shareUrl);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    const handleClose = () => {
        setShareUrl(null);
        setCopied(false);
        setError(null);
        onClose();
    };

    return (
        <Dialog open={open} onClose={handleClose} title={t('share.title')} size="sm">
            <div className="space-y-4">
                {error && (
                    <p className="text-caption text-red-600">{error}</p>
                )}
                {!shareUrl ? (
                    <>
                        <div>
                            <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('share.expires')}</label>
                            <div className="flex flex-wrap gap-2">
                                {expiryOptions.map((opt) => (
                                    <button
                                        key={opt.value}
                                        onClick={() => setExpiresIn(opt.value)}
                                        className={cn(
                                            'px-3 py-1.5 rounded-pill text-caption font-medium transition-colors',
                                            expiresIn === opt.value ? 'bg-brand-600 text-white' : 'bg-surface-100 text-surface-600 hover:bg-surface-200',
                                        )}
                                    >
                                        {opt.label}
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className="flex justify-end gap-3">
                            <Button variant="ghost" onClick={handleClose}>{t('common.cancel')}</Button>
                            <Button onClick={handleCreate} loading={loading}>
                                <Link2 className="h-4 w-4" /> {t('share.createLink')}
                            </Button>
                        </div>
                    </>
                ) : (
                    <div className="space-y-3">
                        <div className="flex items-center gap-2 p-3 bg-surface-50 rounded-input border border-surface-200">
                            <input readOnly value={shareUrl} className="flex-1 bg-transparent text-body-sm text-surface-700 outline-none" />
                            <Button size="sm" variant="outline" onClick={handleCopy}>
                                {copied ? <Check className="h-4 w-4 text-emerald-600" /> : <Copy className="h-4 w-4" />}
                                {copied ? t('share.copied') : t('share.copyLink')}
                            </Button>
                        </div>
                        <div className="flex justify-end">
                            <Button variant="ghost" onClick={handleClose}>{t('common.close')}</Button>
                        </div>
                    </div>
                )}
            </div>
        </Dialog>
    );
}
