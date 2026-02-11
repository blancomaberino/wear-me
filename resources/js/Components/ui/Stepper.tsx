import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Step {
    id: string;
    label: string;
}

interface StepperProps {
    steps: Step[];
    currentStep: number;
    className?: string;
}

function Stepper({ steps, currentStep, className }: StepperProps) {
    const { t } = useTranslation();
    return (
        <>
            {/* Desktop stepper */}
            <nav aria-label="Progress" className={cn('hidden sm:block', className)}>
                <ol className="flex items-center">
                    {steps.map((step, index) => {
                        const isCompleted = index < currentStep;
                        const isCurrent = index === currentStep;

                        return (
                            <li key={step.id} className={cn('relative flex items-center', index < steps.length - 1 && 'flex-1')}>
                                <div className="flex items-center gap-3">
                                    <div
                                        className={cn(
                                            'flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-body-sm font-semibold transition-all duration-normal',
                                            isCompleted && 'border-brand-600 bg-brand-600 text-white',
                                            isCurrent && 'border-brand-600 bg-brand-50 text-brand-600 shadow-glow-brand',
                                            !isCompleted && !isCurrent && 'border-surface-300 bg-white text-surface-400',
                                        )}
                                    >
                                        {isCompleted ? <Check className="h-4 w-4" /> : index + 1}
                                    </div>
                                    <span
                                        className={cn(
                                            'text-body-sm font-medium whitespace-nowrap',
                                            isCurrent ? 'text-brand-600' : isCompleted ? 'text-surface-700' : 'text-surface-400',
                                        )}
                                    >
                                        {step.label}
                                    </span>
                                </div>
                                {index < steps.length - 1 && (
                                    <div className="ml-3 flex-1 h-0.5 min-w-[2rem]">
                                        <div
                                            className={cn(
                                                'h-full rounded-full transition-colors duration-normal',
                                                isCompleted ? 'bg-brand-600' : 'bg-surface-200',
                                            )}
                                        />
                                    </div>
                                )}
                            </li>
                        );
                    })}
                </ol>
            </nav>

            {/* Mobile stepper */}
            <div className={cn('sm:hidden', className)}>
                <div className="flex items-center justify-between mb-2">
                    <span className="text-body-sm font-medium text-surface-900">
                        {t('common.stepOf', { current: currentStep + 1, total: steps.length })}
                    </span>
                    <span className="text-body-sm text-brand-600 font-medium">
                        {steps[currentStep]?.label}
                    </span>
                </div>
                <div className="h-1.5 w-full rounded-pill bg-surface-100 overflow-hidden">
                    <div
                        className="h-full rounded-pill bg-brand-600 transition-all duration-slow"
                        style={{ width: `${((currentStep + 1) / steps.length) * 100}%` }}
                    />
                </div>
            </div>
        </>
    );
}

export { Stepper, type StepperProps, type Step };
