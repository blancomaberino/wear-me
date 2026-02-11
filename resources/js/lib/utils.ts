import { clsx, type ClassValue } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

const twMerge = extendTailwindMerge({
    extend: {
        classGroups: {
            'font-size': [
                'text-display-lg', 'text-display', 'text-heading-xl', 'text-heading-lg',
                'text-heading', 'text-heading-sm', 'text-body-lg', 'text-body',
                'text-body-sm', 'text-caption',
            ],
        },
    },
});

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
