import { cn } from '@/lib/utils';

interface AvatarProps {
    src?: string | null;
    name: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    className?: string;
}

function Avatar({ src, name, size = 'md', className }: AvatarProps) {
    const initials = name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);

    const sizeClasses = {
        sm: 'h-8 w-8 text-caption',
        md: 'h-10 w-10 text-body-sm',
        lg: 'h-12 w-12 text-body',
        xl: 'h-16 w-16 text-heading-sm',
    };

    if (src) {
        return (
            <img
                src={src}
                alt={name}
                className={cn('rounded-full object-cover', sizeClasses[size], className)}
            />
        );
    }

    return (
        <div
            className={cn(
                'rounded-full bg-brand-100 text-brand-700 font-semibold flex items-center justify-center',
                sizeClasses[size],
                className,
            )}
        >
            {initials}
        </div>
    );
}

export { Avatar, type AvatarProps };
