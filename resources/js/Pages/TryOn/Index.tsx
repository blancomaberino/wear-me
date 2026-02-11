import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { WizardShell } from '@/Components/ui/WizardShell';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/ui/EmptyState';
import SelectPhoto from './Steps/SelectPhoto';
import SelectGarments from './Steps/SelectGarments';
import StylePreferences from './Steps/StylePreferences';
import ReviewGenerate from './Steps/ReviewGenerate';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ModelImage, Garment } from '@/types';
import { useState } from 'react';
import { Wand2, Camera, Shirt, ArrowRight } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    modelImages: ModelImage[];
    garments: Garment[];
    sourceResult?: { id: number; result_url: string } | null;
}

export default function Index({ modelImages, garments, sourceResult: initialSourceResult }: Props) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;

    const steps = [
        { id: 'photo', label: t('tryon.stepPhoto') },
        { id: 'garments', label: t('tryon.stepGarments') },
        { id: 'style', label: t('tryon.stepStyle') },
        { id: 'review', label: t('tryon.stepReview') },
    ];
    const [currentStep, setCurrentStep] = useState(0);
    const [selectedPhotoId, setSelectedPhotoId] = useState<number | null>(
        modelImages.find((i) => i.is_primary)?.id || modelImages[0]?.id || null,
    );
    const [selectedTop, setSelectedTop] = useState<number | null>(null);
    const [selectedBottom, setSelectedBottom] = useState<number | null>(null);
    const [promptHint, setPromptHint] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [sourceResult, setSourceResult] = useState(initialSourceResult ?? null);

    // Can proceed checks per step
    const canProceedStep = [
        !!selectedPhotoId || !!sourceResult, // Step 0: photo selected
        selectedTop !== null || selectedBottom !== null, // Step 1: at least one garment
        true, // Step 2: style is optional
        true, // Step 3: review
    ];

    const selectedPhoto = modelImages.find((i) => i.id === selectedPhotoId) || null;
    const selectedGarments = [
        selectedTop ? garments.find((g) => g.id === selectedTop) : null,
        selectedBottom ? garments.find((g) => g.id === selectedBottom) : null,
    ].filter(Boolean) as Garment[];

    const handleDressSelect = (dressId: number) => {
        setSelectedTop(null);
        setSelectedBottom(null);
        // For dresses, go straight to submit
        setIsSubmitting(true);
        const data = {
            garment_ids: [dressId],
            prompt_hint: promptHint,
            ...(sourceResult ? { source_tryon_result_id: sourceResult.id } : { model_image_id: selectedPhotoId }),
        };
        router.post(route('tryon.store'), data, { onFinish: () => setIsSubmitting(false) });
    };

    const handleComplete = () => {
        setIsSubmitting(true);
        const garment_ids: number[] = [];
        if (selectedTop) garment_ids.push(selectedTop);
        if (selectedBottom) garment_ids.push(selectedBottom);

        const data = {
            garment_ids,
            prompt_hint: promptHint,
            ...(sourceResult ? { source_tryon_result_id: sourceResult.id } : { model_image_id: selectedPhotoId }),
        };

        router.post(route('tryon.store'), data, { onFinish: () => setIsSubmitting(false) });
    };

    // Empty state
    if (modelImages.length === 0 || garments.length === 0) {
        return (
            <AuthenticatedLayout>
                <Head title={t('nav.tryOn')} />
                <PageHeader title={t('tryon.title')} />
                <EmptyState
                    icon={Wand2}
                    title={t('tryon.almostReady')}
                    description={t('tryon.almostReadyDesc')}
                    action={
                        <div className="flex items-center gap-3">
                            {modelImages.length === 0 && (
                                <Link href={route('model-images.index')}>
                                    <Button variant="outline"><Camera className="h-4 w-4" /> {t('tryon.uploadPhoto')}</Button>
                                </Link>
                            )}
                            {garments.length === 0 && (
                                <Link href={route('wardrobe.index')}>
                                    <Button variant="outline"><Shirt className="h-4 w-4" /> {t('tryon.uploadClothing')}</Button>
                                </Link>
                            )}
                        </div>
                    }
                />
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title={t('nav.tryOn')} />
            <PageHeader
                title={t('tryon.title')}
                actions={
                    <Link href={route('tryon.history')} className="text-body-sm text-brand-600 hover:text-brand-700 flex items-center gap-1 font-medium">
                        {t('tryon.history')} <ArrowRight className="h-4 w-4" />
                    </Link>
                }
            />

            <WizardShell
                steps={steps}
                currentStep={currentStep}
                onNext={() => setCurrentStep((s) => Math.min(s + 1, steps.length - 1))}
                onBack={() => setCurrentStep((s) => Math.max(s - 1, 0))}
                onComplete={handleComplete}
                canProceed={canProceedStep[currentStep]}
                isSubmitting={isSubmitting}
                completeLabel={t('tryon.generateTryOn')}
            >
                {currentStep === 0 && (
                    <SelectPhoto
                        modelImages={modelImages}
                        selectedPhotoId={selectedPhotoId}
                        onSelectPhoto={setSelectedPhotoId}
                        sourceResult={sourceResult}
                        onClearSource={() => setSourceResult(null)}
                    />
                )}
                {currentStep === 1 && (
                    <SelectGarments
                        garments={garments}
                        selectedTop={selectedTop}
                        selectedBottom={selectedBottom}
                        onSelectTop={setSelectedTop}
                        onSelectBottom={setSelectedBottom}
                        onSelectDress={handleDressSelect}
                    />
                )}
                {currentStep === 2 && (
                    <StylePreferences
                        promptHint={promptHint}
                        onPromptChange={setPromptHint}
                        user={auth.user}
                        selectedGarments={selectedGarments}
                    />
                )}
                {currentStep === 3 && (
                    <ReviewGenerate
                        selectedPhoto={selectedPhoto}
                        sourceResult={sourceResult}
                        selectedGarments={selectedGarments}
                        promptHint={promptHint}
                    />
                )}
            </WizardShell>
        </AuthenticatedLayout>
    );
}
