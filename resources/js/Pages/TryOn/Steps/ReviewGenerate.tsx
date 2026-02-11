import { Card, CardBody } from '@/Components/ui/Card';
import { Badge } from '@/Components/ui/Badge';
import { ModelImage, Garment } from '@/types';
import { useTranslation } from 'react-i18next';

interface Props {
    selectedPhoto: ModelImage | null;
    sourceResult: { id: number; result_url: string } | null;
    selectedGarments: Garment[];
    promptHint: string;
}

export default function ReviewGenerate({ selectedPhoto, sourceResult, selectedGarments, promptHint }: Props) {
    const { t } = useTranslation();
    const photoUrl = sourceResult?.result_url || selectedPhoto?.thumbnail_url || selectedPhoto?.url;

    return (
        <div className="space-y-6">
            <div>
                <h3 className="text-heading-sm text-surface-900 mb-1">{t('tryon.reviewTitle')}</h3>
                <p className="text-body-sm text-surface-500">{t('tryon.reviewDesc')}</p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {/* Photo */}
                <Card>
                    <CardBody>
                        <p className="text-caption font-medium text-surface-500 mb-3">{t('tryon.yourPhoto')}</p>
                        {photoUrl && (
                            <img src={photoUrl} alt="Selected photo" className="w-full h-48 object-cover rounded-lg" />
                        )}
                        {sourceResult && <Badge variant="brand" className="mt-2">{t('tryon.usingPreviousResult')}</Badge>}
                    </CardBody>
                </Card>

                {/* Garments */}
                <Card>
                    <CardBody>
                        <p className="text-caption font-medium text-surface-500 mb-3">{t('tryon.garments', { count: selectedGarments.length })}</p>
                        <div className="space-y-3">
                            {selectedGarments.map((g) => (
                                <div key={g.id} className="flex items-center gap-3">
                                    <img
                                        src={g.thumbnail_url || g.url}
                                        alt={g.name || ''}
                                        className="h-16 w-16 rounded-lg object-cover bg-surface-50"
                                    />
                                    <div>
                                        <p className="text-body-sm font-medium text-surface-900">{g.name || g.original_filename}</p>
                                        <div className="flex items-center gap-1.5 mt-0.5">
                                            <Badge variant="neutral" size="sm">{g.category}</Badge>
                                            {g.size_label && <Badge variant="brand" size="sm">{g.size_label}</Badge>}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardBody>
                </Card>
            </div>

            {/* Style */}
            {promptHint && (
                <Card>
                    <CardBody>
                        <p className="text-caption font-medium text-surface-500 mb-2">{t('tryon.styleInstructions')}</p>
                        <p className="text-body-sm text-surface-700">{promptHint}</p>
                    </CardBody>
                </Card>
            )}
        </div>
    );
}
