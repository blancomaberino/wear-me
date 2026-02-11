import { Shirt } from 'lucide-react';

interface ApplicationLogoProps {
    className?: string;
    showText?: boolean;
}

export default function ApplicationLogo({ className, showText = true }: ApplicationLogoProps) {
    return (
        <div className="flex items-center gap-3">
            <div className="flex items-center justify-center h-12 w-12 rounded-2xl bg-brand-600 shadow-soft">
                <Shirt className="h-7 w-7 text-white" />
            </div>
            {showText && (
                <span className="text-heading-lg text-surface-900">WearMe</span>
            )}
        </div>
    );
}
