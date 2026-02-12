import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { CheckCircle, XCircle, Info, X } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function FlashMessages() {
    const { flash } = usePage().props as any;
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState({ type: '', text: '' });

    useEffect(() => {
        if (flash?.success) {
            setMessage({ type: 'success', text: flash.success });
            setVisible(true);
        } else if (flash?.error) {
            setMessage({ type: 'error', text: flash.error });
            setVisible(true);
        } else if (flash?.info) {
            setMessage({ type: 'info', text: flash.info });
            setVisible(true);
        }
    }, [flash?.success, flash?.error, flash?.info]);

    useEffect(() => {
        if (visible) {
            const timer = setTimeout(() => setVisible(false), 5000);
            return () => clearTimeout(timer);
        }
    }, [visible]);

    if (!visible) return null;

    return (
        <div className="fixed top-4 right-4 z-50 animate-slide-down" role="alert" aria-live="polite">
            <div className={cn(
                'flex items-center gap-3 rounded-card px-4 py-3 shadow-medium border min-w-[300px] max-w-md',
                message.type === 'success' && 'bg-emerald-50 border-emerald-200 text-emerald-800',
                message.type === 'error' && 'bg-red-50 border-red-200 text-red-800',
                message.type === 'info' && 'bg-blue-50 border-blue-200 text-blue-800',
            )}>
                {message.type === 'success' ? (
                    <CheckCircle className="h-5 w-5 text-emerald-500 shrink-0" />
                ) : message.type === 'info' ? (
                    <Info className="h-5 w-5 text-blue-500 shrink-0" />
                ) : (
                    <XCircle className="h-5 w-5 text-red-500 shrink-0" />
                )}
                <p className="text-body-sm font-medium flex-1">{message.text}</p>
                <button onClick={() => setVisible(false)} className="shrink-0 p-0.5 rounded hover:bg-black/5 transition-colors">
                    <X className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
