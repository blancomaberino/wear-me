import { Button } from '@/Components/ui/Button';
import { Dialog } from '@/Components/ui/Dialog';
import { Input } from '@/Components/ui/Input';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function DeleteUserForm({
    className = '',
}: {
    className?: string;
}) {
    const { t } = useTranslation();
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);

        clearErrors();
        reset();
    };

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-heading-sm text-surface-900">
                    {t('profile.deleteTitle')}
                </h2>

                <p className="mt-1 text-body-sm text-surface-500">
                    {t('profile.deleteDesc')}
                </p>
            </header>

            <Button variant="danger" onClick={confirmUserDeletion}>
                {t('profile.deleteButton')}
            </Button>

            <Dialog
                open={confirmingUserDeletion}
                onClose={closeModal}
                title={t('profile.deleteConfirmTitle')}
                description={t('profile.deleteConfirmDesc')}
                size="md"
            >
                <form onSubmit={deleteUser} className="mt-6">
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        ref={passwordInput}
                        value={data.password}
                        onChange={(e) =>
                            setData('password', e.target.value)
                        }
                        error={errors.password}
                        autoFocus
                        placeholder={t('auth.password')}
                        className="w-full"
                    />

                    <div className="mt-6 flex justify-end gap-3">
                        <Button variant="ghost" onClick={closeModal}>
                            {t('common.cancel')}
                        </Button>

                        <Button variant="danger" disabled={processing}>
                            {t('profile.deleteButton')}
                        </Button>
                    </div>
                </form>
            </Dialog>
        </section>
    );
}
