import { Loader2, CheckCircle2, XCircle, Clock } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface ProcessingStatusProps {
    status: 'pending' | 'processing' | 'completed' | 'failed';
    errorMessage?: string | null;
    className?: string;
}

export default function ProcessingStatus({ status, errorMessage, className = '' }: ProcessingStatusProps) {
    const { t } = useTranslation();

    const statusConfig = {
        pending: {
            icon: Clock,
            text: t('processing.pending'),
            color: 'text-yellow-600',
            bg: 'bg-yellow-50',
            border: 'border-yellow-200',
        },
        processing: {
            icon: Loader2,
            text: t('processing.processing'),
            color: 'text-brand-600',
            bg: 'bg-brand-50',
            border: 'border-brand-200',
        },
        completed: {
            icon: CheckCircle2,
            text: t('processing.completed'),
            color: 'text-green-600',
            bg: 'bg-green-50',
            border: 'border-green-200',
        },
        failed: {
            icon: XCircle,
            text: t('processing.failed'),
            color: 'text-red-600',
            bg: 'bg-red-50',
            border: 'border-red-200',
        },
    };

    const config = statusConfig[status];
    const Icon = config.icon;

    return (
        <div className={`flex items-center gap-3 px-4 py-3 rounded-lg border ${config.bg} ${config.border} ${className}`} aria-live="polite">
            <Icon className={`w-5 h-5 ${config.color} ${status === 'processing' ? 'animate-spin' : ''}`} />
            <div>
                <p className={`font-medium ${config.color}`}>{config.text}</p>
                {errorMessage && status === 'failed' && (
                    <p className="text-sm text-red-500 mt-1">{errorMessage}</p>
                )}
            </div>
        </div>
    );
}
