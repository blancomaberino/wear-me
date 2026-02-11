import GuestLayout from '@/Layouts/GuestLayout';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { useTranslation } from 'react-i18next';

export default function ConfirmPassword() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title={t('auth.confirmTitle')} />

            <h2 className="text-heading text-surface-900 text-center mb-6">{t('auth.confirmTitle')}</h2>

            <div className="mb-6 text-body-sm text-surface-600 text-center">
                {t('auth.confirmDescription')}
            </div>

            <form onSubmit={submit} className="space-y-4">
                <Input
                    label={t('auth.password')}
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                    autoComplete="current-password"
                />

                <Button type="submit" className="w-full" loading={processing}>
                    {t('common.confirm')}
                </Button>
            </form>
        </GuestLayout>
    );
}
