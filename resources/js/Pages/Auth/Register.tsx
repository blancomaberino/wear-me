import GuestLayout from '@/Layouts/GuestLayout';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { useTranslation } from 'react-i18next';

export default function Register() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <GuestLayout>
            <Head title={t('auth.createAccount')} />

            <h2 className="text-heading text-surface-900 text-center mb-6">{t('auth.registerTitle')}</h2>

            {/* Google OAuth */}
            <a href={route('auth.google')} className="flex items-center justify-center gap-3 w-full rounded-button border border-surface-200 bg-white px-4 py-2.5 text-body-sm font-medium text-surface-700 hover:bg-surface-50 transition-colors mb-6">
                <svg className="h-5 w-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                {t('auth.continueWithGoogle')}
            </a>

            <div className="relative mb-6">
                <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-surface-200" /></div>
                <div className="relative flex justify-center text-caption"><span className="bg-white px-3 text-surface-400">{t('auth.orRegisterWithEmail')}</span></div>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <Input label={t('auth.name')} value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} autoComplete="name" />
                <Input label={t('auth.email')} type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} autoComplete="username" />
                <Input label={t('auth.password')} type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} error={errors.password} autoComplete="new-password" />
                <Input label={t('auth.confirmPassword')} type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} error={errors.password_confirmation} autoComplete="new-password" />

                <Button type="submit" className="w-full" loading={processing}>{t('auth.createAccount')}</Button>
            </form>

            <p className="mt-6 text-center text-body-sm text-surface-500">
                {t('auth.hasAccount')}{' '}
                <Link href={route('login')} className="text-brand-600 hover:text-brand-700 font-medium">{t('auth.logIn')}</Link>
            </p>
        </GuestLayout>
    );
}
