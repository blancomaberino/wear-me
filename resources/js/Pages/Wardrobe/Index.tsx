import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import GarmentCard from '@/Components/GarmentCard';
import GarmentUploadDialog from '@/Components/GarmentUploadDialog';
import GarmentDetailSheet from '@/Components/GarmentDetailSheet';
import BulkUploadDialog from '@/Components/BulkUploadDialog';
import ImportFromUrlDialog from '@/Components/ImportFromUrlDialog';
import ExportDialog from '@/Components/ExportDialog';
import { Button } from '@/Components/ui/Button';
import { Tabs } from '@/Components/ui/Tabs';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, usePage } from '@inertiajs/react';
import { Garment, User } from '@/types';
import { useState } from 'react';
import { Plus, Shirt, Upload, Link2, Download } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    garments: Garment[];
    currentCategory: string;
    categories: string[];
}

export default function Index({ garments, currentCategory }: Props) {
    const { t } = useTranslation();
    const [showUpload, setShowUpload] = useState(false);
    const [showBulkUpload, setShowBulkUpload] = useState(false);
    const [showImportUrl, setShowImportUrl] = useState(false);
    const [showExport, setShowExport] = useState(false);
    const [selectedGarment, setSelectedGarment] = useState<Garment | null>(null);
    const [activeTab, setActiveTab] = useState(currentCategory || 'all');
    const { auth } = usePage().props as any;
    const user: User = auth.user;

    const filtered = activeTab === 'all' ? garments : garments.filter((g) => g.category === activeTab);

    const counts = {
        all: garments.length,
        upper: garments.filter((g) => g.category === 'upper').length,
        lower: garments.filter((g) => g.category === 'lower').length,
        dress: garments.filter((g) => g.category === 'dress').length,
    };

    const tabs = [
        { id: 'all', label: t('wardrobe.all'), count: counts.all },
        { id: 'upper', label: t('wardrobe.tops'), count: counts.upper },
        { id: 'lower', label: t('wardrobe.bottoms'), count: counts.lower },
        { id: 'dress', label: t('wardrobe.dresses'), count: counts.dress },
    ];

    return (
        <AuthenticatedLayout>
            <Head title={t('wardrobe.title')} />

            <PageHeader
                title={t('wardrobe.title')}
                description={t('wardrobe.count', { count: garments.length })}
                actions={
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" onClick={() => setShowImportUrl(true)}>
                            <Link2 className="h-4 w-4" /> {t('wardrobe.importUrl')}
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setShowBulkUpload(true)}>
                            <Upload className="h-4 w-4" /> {t('wardrobe.bulkUpload')}
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setShowExport(true)}>
                            <Download className="h-4 w-4" /> {t('wardrobe.exportWardrobe')}
                        </Button>
                        <Button onClick={() => setShowUpload(true)}>
                            <Plus className="h-4 w-4" /> {t('wardrobe.addItem')}
                        </Button>
                    </div>
                }
            />

            <Tabs tabs={tabs} activeTab={activeTab} onChange={setActiveTab} variant="underline" className="mb-6" />

            {filtered.length === 0 ? (
                <EmptyState
                    icon={Shirt}
                    title={activeTab === 'all' ? t('wardrobe.emptyAll') : activeTab === 'upper' ? t('wardrobe.emptyTops') : activeTab === 'lower' ? t('wardrobe.emptyBottoms') : t('wardrobe.emptyDresses')}
                    description={t('wardrobe.emptyDesc')}
                    action={
                        <Button onClick={() => setShowUpload(true)}>
                            <Plus className="h-4 w-4" /> {t('wardrobe.addFirst')}
                        </Button>
                    }
                />
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    {filtered.map((garment) => (
                        <GarmentCard
                            key={garment.id}
                            garment={garment}
                            onClick={() => setSelectedGarment(garment)}
                        />
                    ))}
                </div>
            )}

            <GarmentUploadDialog open={showUpload} onClose={() => setShowUpload(false)} />
            <BulkUploadDialog open={showBulkUpload} onClose={() => setShowBulkUpload(false)} />
            <ImportFromUrlDialog open={showImportUrl} onClose={() => setShowImportUrl(false)} />
            <ExportDialog open={showExport} onClose={() => setShowExport(false)} />
            <GarmentDetailSheet key={selectedGarment?.id ?? 'closed'} garment={selectedGarment} open={!!selectedGarment} onClose={() => setSelectedGarment(null)} />
        </AuthenticatedLayout>
    );
}
