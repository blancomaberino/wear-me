import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { useState, useEffect, useRef } from 'react';
import { Download, Loader2, CheckCircle, XCircle } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { ExportStatus } from '@/types';

interface Props {
    open: boolean;
    onClose: () => void;
}

export default function ExportDialog({ open, onClose }: Props) {
    const { t } = useTranslation();
    const [includeImages, setIncludeImages] = useState(true);
    const [includeResults, setIncludeResults] = useState(false);
    const [exportData, setExportData] = useState<ExportStatus | null>(null);
    const [loading, setLoading] = useState(false);
    const pollRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';

    const startExport = async () => {
        setLoading(true);
        try {
            const response = await fetch(route('export.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ include_images: includeImages, include_results: includeResults }),
            });
            const data = await response.json();
            setExportData(data.export);
            startPolling(data.export.id);
        } catch {
            setExportData({ id: 0, status: 'failed', file_size_bytes: null, download_url: null, created_at: '' });
        } finally {
            setLoading(false);
        }
    };

    const startPolling = (exportId: number) => {
        if (pollRef.current) clearInterval(pollRef.current);
        pollRef.current = setInterval(async () => {
            try {
                const response = await fetch(route('export.status', { export: exportId }), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();
                setExportData(data.export);
                if (data.export.status === 'completed' || data.export.status === 'failed') {
                    if (pollRef.current) clearInterval(pollRef.current);
                }
            } catch {
                if (pollRef.current) clearInterval(pollRef.current);
            }
        }, 3000);
    };

    useEffect(() => {
        return () => {
            if (pollRef.current) clearInterval(pollRef.current);
        };
    }, []);

    const handleClose = () => {
        if (pollRef.current) clearInterval(pollRef.current);
        setExportData(null);
        onClose();
    };

    const formatSize = (bytes: number | null) => {
        if (!bytes) return '';
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`;
        return `${(bytes / 1048576).toFixed(1)} MB`;
    };

    return (
        <Dialog open={open} onClose={handleClose} title={t('export.title')} size="md">
            <div className="space-y-5">
                {!exportData ? (
                    <>
                        <p className="text-body-sm text-surface-600">{t('export.desc')}</p>

                        <div className="space-y-3">
                            <label className="flex items-center gap-3 p-3 rounded-card border border-surface-200 hover:bg-surface-50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={includeImages}
                                    onChange={(e) => setIncludeImages(e.target.checked)}
                                    className="rounded border-surface-300 text-brand-600 focus:ring-brand-500"
                                />
                                <span className="text-body-sm text-surface-700">{t('export.includeImages')}</span>
                            </label>
                            <label className="flex items-center gap-3 p-3 rounded-card border border-surface-200 hover:bg-surface-50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={includeResults}
                                    onChange={(e) => setIncludeResults(e.target.checked)}
                                    className="rounded border-surface-300 text-brand-600 focus:ring-brand-500"
                                />
                                <span className="text-body-sm text-surface-700">{t('export.includeResults')}</span>
                            </label>
                        </div>

                        <div className="flex justify-end gap-3">
                            <Button variant="ghost" onClick={handleClose}>{t('common.cancel')}</Button>
                            <Button onClick={startExport} loading={loading}>
                                <Download className="h-4 w-4" /> {t('export.startExport')}
                            </Button>
                        </div>
                    </>
                ) : (
                    <div className="text-center py-4 space-y-4">
                        {(exportData.status === 'pending' || exportData.status === 'processing') && (
                            <>
                                <Loader2 className="h-10 w-10 text-brand-600 mx-auto animate-spin" />
                                <p className="text-body-sm text-surface-600">{t('export.processing')}</p>
                            </>
                        )}

                        {exportData.status === 'completed' && (
                            <>
                                <CheckCircle className="h-10 w-10 text-emerald-600 mx-auto" />
                                <p className="text-body-sm text-surface-900 font-medium">{t('export.ready')}</p>
                                {exportData.file_size_bytes && (
                                    <p className="text-caption text-surface-500">
                                        {t('export.fileSize', { size: formatSize(exportData.file_size_bytes) })}
                                    </p>
                                )}
                                <p className="text-caption text-surface-400">{t('export.expiresIn')}</p>
                                <a
                                    href={exportData.download_url || '#'}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-button text-body-sm font-medium hover:bg-brand-700 transition-colors"
                                >
                                    <Download className="h-4 w-4" /> {t('export.download')}
                                </a>
                            </>
                        )}

                        {exportData.status === 'failed' && (
                            <>
                                <XCircle className="h-10 w-10 text-red-500 mx-auto" />
                                <p className="text-body-sm text-red-600">{t('export.failed')}</p>
                                <Button onClick={() => setExportData(null)}>{t('common.retry')}</Button>
                            </>
                        )}
                    </div>
                )}
            </div>
        </Dialog>
    );
}
