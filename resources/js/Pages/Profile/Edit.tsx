import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import ColorPaletteForm from './Partials/ColorPaletteForm';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import BodyMeasurementsForm from './Partials/BodyMeasurementsForm';
import { PageHeader } from '@/Components/layout/PageHeader';
import { useTranslation } from 'react-i18next';

interface ModelImageOption {
    id: number;
    thumbnail_url: string;
}

export default function Edit({
    auth,
    mustVerifyEmail,
    status,
    colorPalette,
    modelImages,
}: PageProps<{
    mustVerifyEmail: boolean;
    status?: string;
    colorPalette: string[];
    modelImages: ModelImageOption[];
}>) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout>
            <Head title={t('profile.title')} />

            <PageHeader title={t('profile.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </div>

                    <BodyMeasurementsForm user={auth.user} />

                    <div className="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                        <ColorPaletteForm
                            colorPalette={colorPalette}
                            modelImages={modelImages}
                            className="max-w-xl"
                        />
                    </div>

                    <div className="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                        <UpdatePasswordForm className="max-w-xl" />
                    </div>

                    <div className="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                        <DeleteUserForm className="max-w-xl" />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
