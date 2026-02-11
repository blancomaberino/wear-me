import { cn } from '@/lib/utils';
import { Stepper, Step } from './Stepper';
import { Button } from './Button';
import { ArrowLeft, ArrowRight, Sparkles } from 'lucide-react';
import { ReactNode, useState } from 'react';
import { useTranslation } from 'react-i18next';

interface WizardShellProps {
    steps: Step[];
    currentStep: number;
    onNext: () => void;
    onBack: () => void;
    onComplete: () => void;
    canProceed: boolean;
    isSubmitting?: boolean;
    completeLabel?: string;
    children: ReactNode;
    className?: string;
}

function WizardShell({
    steps,
    currentStep,
    onNext,
    onBack,
    onComplete,
    canProceed,
    isSubmitting,
    completeLabel = 'Generate',
    children,
    className,
}: WizardShellProps) {
    const { t } = useTranslation();
    const isFirstStep = currentStep === 0;
    const isLastStep = currentStep === steps.length - 1;

    return (
        <div className={cn('space-y-6', className)}>
            <Stepper steps={steps} currentStep={currentStep} />

            <div className="min-h-[300px] animate-fade-in" key={currentStep}>
                {children}
            </div>

            <div className="flex items-center justify-between pt-4 border-t border-surface-100">
                <div>
                    {!isFirstStep && (
                        <Button variant="ghost" onClick={onBack} disabled={isSubmitting}>
                            <ArrowLeft className="h-4 w-4" />
                            {t('common.back')}
                        </Button>
                    )}
                </div>
                <div className="flex items-center gap-3">
                    {!isLastStep ? (
                        <Button onClick={onNext} disabled={!canProceed}>
                            {t('common.next')}
                            <ArrowRight className="h-4 w-4" />
                        </Button>
                    ) : (
                        <Button
                            size="lg"
                            onClick={onComplete}
                            disabled={!canProceed || isSubmitting}
                            loading={isSubmitting}
                        >
                            <Sparkles className="h-5 w-5" />
                            {completeLabel}
                        </Button>
                    )}
                </div>
            </div>
        </div>
    );
}

export { WizardShell, type WizardShellProps };
