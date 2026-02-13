import { Loader2, CheckCircle2, XCircle, Clock, AlertTriangle } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { cn } from '@/lib/utils';

interface ProcessingStatusProps {
    status: 'pending' | 'processing' | 'completed' | 'failed';
    errorMessage?: string | null;
    className?: string;
}

export default function ProcessingStatus({ status, errorMessage, className = '' }: ProcessingStatusProps) {
    const { t } = useTranslation();

    if (status === 'pending') {
        return (
            <div className={cn('rounded-card border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50 p-5', className)} aria-live="polite">
                <div className="flex items-start gap-4">
                    <div className="flex items-center justify-center h-10 w-10 rounded-full bg-amber-100 flex-shrink-0">
                        <Clock className="h-5 w-5 text-amber-600" />
                    </div>
                    <div className="min-w-0">
                        <p className="text-body-sm font-semibold text-amber-800">{t('processing.pending')}</p>
                        <p className="text-caption text-amber-600 mt-0.5">{t('processing.pendingHint')}</p>
                    </div>
                </div>
            </div>
        );
    }

    if (status === 'processing') {
        return (
            <div className={cn('rounded-card border border-brand-200 bg-gradient-to-r from-brand-50 to-indigo-50 p-5', className)} aria-live="polite">
                <div className="flex items-start gap-4">
                    <div className="relative flex items-center justify-center h-10 w-10 flex-shrink-0">
                        <div className="absolute inset-0 rounded-full bg-brand-100 animate-ping opacity-30" />
                        <div className="relative flex items-center justify-center h-10 w-10 rounded-full bg-brand-100">
                            <Loader2 className="h-5 w-5 text-brand-600 animate-spin" />
                        </div>
                    </div>
                    <div className="min-w-0">
                        <p className="text-body-sm font-semibold text-brand-800">{t('processing.processing')}</p>
                        <p className="text-caption text-brand-600 mt-0.5">{t('processing.processingHint')}</p>
                        <div className="mt-3 h-1.5 w-full max-w-xs rounded-full bg-brand-100 overflow-hidden">
                            <div className="h-full w-2/3 rounded-full bg-brand-500 animate-pulse" />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    if (status === 'completed') {
        return (
            <div className={cn('rounded-card border border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 p-5', className)} aria-live="polite">
                <div className="flex items-center gap-4">
                    <div className="flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 flex-shrink-0">
                        <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                    </div>
                    <p className="text-body-sm font-semibold text-emerald-800">{t('processing.completed')}</p>
                </div>
            </div>
        );
    }

    // Map error keys to translation keys
    const errorKeyMap: Record<string, string> = {
        'error.safety_blocked': 'processing.errorSafetyBlocked',
        'error.content_filtered': 'processing.errorContentFiltered',
        'error.no_image': 'processing.errorNoImage',
        'error.generic': 'processing.errorGeneric',
    };

    const translatedError = errorMessage && errorKeyMap[errorMessage]
        ? t(errorKeyMap[errorMessage])
        : errorMessage;

    // failed
    return (
        <div className={cn('rounded-card border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-5', className)} aria-live="polite">
            <div className="flex items-start gap-4">
                <div className="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 flex-shrink-0">
                    <AlertTriangle className="h-5 w-5 text-red-600" />
                </div>
                <div className="min-w-0">
                    <p className="text-body-sm font-semibold text-red-800">{t('processing.failed')}</p>
                    <p className="text-caption text-red-600 mt-0.5">
                        {translatedError || t('processing.failedHint')}
                    </p>
                </div>
            </div>
        </div>
    );
}
