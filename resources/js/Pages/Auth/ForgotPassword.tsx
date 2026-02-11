import GuestLayout from '@/Layouts/GuestLayout';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { useTranslation } from 'react-i18next';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title={t('auth.forgotTitle')} />

            <h2 className="text-heading text-surface-900 text-center mb-6">{t('auth.forgotTitle')}</h2>

            <div className="mb-6 text-body-sm text-surface-600 text-center">
                {t('auth.forgotDescription')}
            </div>

            {status && (
                <div className="mb-4 text-body-sm font-medium text-emerald-600 text-center">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <Input
                    label={t('auth.email')}
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    autoComplete="username"
                />

                <Button type="submit" className="w-full" loading={processing}>
                    {t('auth.emailResetLink')}
                </Button>
            </form>
        </GuestLayout>
    );
}
