import GuestLayout from '@/Layouts/GuestLayout';
import { Button } from '@/Components/ui/Button';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { useTranslation } from 'react-i18next';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title={t('auth.verifyTitle')} />

            <h2 className="text-heading text-surface-900 text-center mb-6">{t('auth.verifyTitle')}</h2>

            <div className="mb-6 text-body-sm text-surface-600 text-center">
                {t('auth.verifyDescription')}
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-6 text-body-sm font-medium text-emerald-600 text-center">
                    {t('auth.verificationSent')}
                </div>
            )}

            <form onSubmit={submit}>
                <div className="flex flex-col gap-4">
                    <Button type="submit" className="w-full" loading={processing}>
                        {t('auth.resendVerification')}
                    </Button>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="text-body-sm text-surface-600 hover:text-surface-900 text-center"
                    >
                        {t('nav.logOut')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
