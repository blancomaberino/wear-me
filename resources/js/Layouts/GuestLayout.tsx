import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { useTranslation } from 'react-i18next';

export default function Guest({ children }: PropsWithChildren) {
    const { t } = useTranslation();
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-brand-50 via-white to-surface-50 px-4 py-8">
            <div className="mb-8">
                <Link href="/">
                    <ApplicationLogo />
                </Link>
            </div>

            <div className="w-full max-w-md overflow-hidden rounded-card bg-white px-8 py-8 shadow-medium">
                {children}
            </div>

            <p className="mt-8 text-caption text-surface-400">
                {t('nav.poweredByAi')}
            </p>
        </div>
    );
}
