import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';
import { ReactNode } from 'react';

interface ImageSelectorItem {
    id: number;
    url: string;
    thumbnail_url?: string | null;
    label?: string;
}

interface ImageSelectorProps {
    items: ImageSelectorItem[];
    selectedId: number | null;
    onSelect: (id: number | null) => void;
    columns?: number;
    emptyText?: string;
    prependSlot?: ReactNode;
    className?: string;
}

function ImageSelector({ items, selectedId, onSelect, columns = 4, emptyText, prependSlot, className }: ImageSelectorProps) {
    if (items.length === 0 && !prependSlot) {
        return emptyText ? <p className="text-body-sm text-surface-400 italic">{emptyText}</p> : null;
    }

    const gridCols: Record<number, string> = {
        3: 'grid-cols-3',
        4: 'grid-cols-3 sm:grid-cols-4',
        5: 'grid-cols-3 sm:grid-cols-5',
        6: 'grid-cols-4 sm:grid-cols-6',
    };

    return (
        <div className={cn('grid gap-3', gridCols[columns] || gridCols[4], className)}>
            {prependSlot}
            {items.map((item) => {
                const isSelected = selectedId === item.id;
                return (
                    <button
                        key={item.id}
                        type="button"
                        onClick={() => onSelect(isSelected ? null : item.id)}
                        className={cn(
                            'relative aspect-square rounded-xl overflow-hidden transition-all duration-fast group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2',
                            isSelected
                                ? 'ring-[3px] ring-brand-500 ring-offset-2'
                                : 'hover:ring-2 hover:ring-surface-300',
                        )}
                    >
                        <img
                            src={item.thumbnail_url || item.url}
                            alt={item.label || ''}
                            className="w-full h-full object-cover"
                            loading="lazy"
                        />
                        {isSelected && (
                            <div className="absolute top-2 left-2 h-6 w-6 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-soft">
                                <Check className="h-3.5 w-3.5" />
                            </div>
                        )}
                        {item.label && (
                            <div className="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent p-2 pt-6">
                                <p className="text-white text-caption truncate">{item.label}</p>
                            </div>
                        )}
                    </button>
                );
            })}
        </div>
    );
}

export { ImageSelector, type ImageSelectorProps, type ImageSelectorItem };
