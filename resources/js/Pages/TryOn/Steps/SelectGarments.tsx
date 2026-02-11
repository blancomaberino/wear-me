import GarmentCard from '@/Components/GarmentCard';
import GarmentUploadDialog from '@/Components/GarmentUploadDialog';
import { Tabs } from '@/Components/ui/Tabs';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Garment } from '@/types';
import { useState } from 'react';
import { Plus, Shirt } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    garments: Garment[];
    selectedTop: number | null;
    selectedBottom: number | null;
    onSelectTop: (id: number | null) => void;
    onSelectBottom: (id: number | null) => void;
    onSelectDress: (id: number) => void;
}

export default function SelectGarments({ garments, selectedTop, selectedBottom, onSelectTop, onSelectBottom, onSelectDress }: Props) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState('upper');
    const [showUpload, setShowUpload] = useState(false);

    const tops = garments.filter((g) => g.category === 'upper');
    const bottoms = garments.filter((g) => g.category === 'lower');
    const dresses = garments.filter((g) => g.category === 'dress');

    const tabs = [
        { id: 'upper', label: t('wardrobe.tops'), count: tops.length },
        { id: 'lower', label: t('wardrobe.bottoms'), count: bottoms.length },
        { id: 'dress', label: t('wardrobe.dresses'), count: dresses.length },
    ];

    const currentItems = activeTab === 'upper' ? tops : activeTab === 'lower' ? bottoms : dresses;
    const selectedId = activeTab === 'upper' ? selectedTop : activeTab === 'lower' ? selectedBottom : null;

    const handleSelect = (garment: Garment) => {
        if (activeTab === 'upper') {
            onSelectTop(selectedTop === garment.id ? null : garment.id);
        } else if (activeTab === 'lower') {
            onSelectBottom(selectedBottom === garment.id ? null : garment.id);
        } else {
            onSelectDress(garment.id);
        }
    };

    // Summary of selected items
    const selectedItems = [
        selectedTop ? garments.find((g) => g.id === selectedTop) : null,
        selectedBottom ? garments.find((g) => g.id === selectedBottom) : null,
    ].filter(Boolean) as Garment[];

    return (
        <div className="space-y-4">
            <div className="flex items-start justify-between">
                <div>
                    <h3 className="text-heading-sm text-surface-900 mb-1">{t('tryon.selectGarmentsTitle')}</h3>
                    <p className="text-body-sm text-surface-500">
                        {t('tryon.selectGarmentsDesc')}
                    </p>
                </div>
                <Button variant="outline" size="sm" onClick={() => setShowUpload(true)}>
                    <Plus className="h-4 w-4" /> {t('tryon.uploadNew')}
                </Button>
            </div>

            <Tabs tabs={tabs} activeTab={activeTab} onChange={setActiveTab} variant="pill" />

            {currentItems.length === 0 ? (
                <EmptyState
                    icon={Shirt}
                    title={activeTab === 'upper' ? t('tryon.noTops') : activeTab === 'lower' ? t('tryon.noBottoms') : t('tryon.noDresses')}
                    description={t('tryon.uploadFirst')}
                    action={
                        <Button variant="outline" size="sm" onClick={() => setShowUpload(true)}>
                            <Plus className="h-4 w-4" /> {t('common.upload')}
                        </Button>
                    }
                />
            ) : (
                <div className="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                    {currentItems.map((garment) => (
                        <GarmentCard
                            key={garment.id}
                            garment={garment}
                            selected={selectedId === garment.id}
                            onClick={() => handleSelect(garment)}
                        />
                    ))}
                </div>
            )}

            {/* Selected summary */}
            {selectedItems.length > 0 && (
                <div className="flex items-center gap-3 p-3 rounded-card bg-brand-50 border border-brand-100 animate-fade-in">
                    <span className="text-body-sm font-medium text-brand-700">{t('tryon.selected')}:</span>
                    <div className="flex items-center gap-2">
                        {selectedItems.map((g) => (
                            <div key={g.id} className="flex items-center gap-2 bg-white rounded-button px-2.5 py-1 shadow-xs">
                                <img src={g.thumbnail_url || g.url} alt="" className="h-6 w-6 rounded object-cover" />
                                <span className="text-caption font-medium text-surface-700 max-w-[100px] truncate">{g.name || g.original_filename}</span>
                                {g.size_label && <span className="text-caption text-surface-400">{g.size_label}</span>}
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <GarmentUploadDialog open={showUpload} onClose={() => setShowUpload(false)} />
        </div>
    );
}
