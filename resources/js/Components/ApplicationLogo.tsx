interface ApplicationLogoProps {
    className?: string;
    showText?: boolean;
}

export default function ApplicationLogo({ className, showText = true }: ApplicationLogoProps) {
    return (
        <div className="flex items-center gap-3">
            <img src="/icons/logo.png" alt="WearMe" className="h-12 w-12" />
            {showText && (
                <span className="text-heading-lg text-surface-900">WearMe</span>
            )}
        </div>
    );
}
