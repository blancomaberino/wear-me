import { Garment } from '@/types';
import { Badge } from '@/Components/ui/Badge';
import { cn } from '@/lib/utils';

interface Props {
    garment: Garment;
    selected?: boolean;
    onClick?: () => void;
    className?: string;
}

export default function GarmentCard({ garment, selected, onClick, className }: Props) {
    return (
        <div
            onClick={onClick}
            className={cn(
                'group relative aspect-square rounded-card overflow-hidden bg-surface-50 transition-all duration-fast',
                onClick && 'cursor-pointer',
                selected
                    ? 'ring-[3px] ring-brand-500 ring-offset-2'
                    : onClick && 'hover:ring-2 hover:ring-surface-300 hover:shadow-soft',
                className,
            )}
        >
            <img
                src={garment.thumbnail_url || garment.url}
                alt={garment.name || garment.original_filename}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-normal"
                loading="lazy"
            />
            {selected && (
                <div className="absolute top-2 left-2 h-6 w-6 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-soft text-caption font-bold">
                    ✓
                </div>
            )}
            <div className="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent p-3 pt-8">
                <p className="text-white text-body-sm font-medium truncate">
                    {garment.name || garment.original_filename}
                </p>
                <div className="flex items-center gap-1.5 mt-1">
                    {garment.size_label && (
                        <span className="text-[10px] font-medium bg-white/20 text-white rounded px-1.5 py-0.5">
                            {garment.size_label}
                        </span>
                    )}
                    {garment.brand && (
                        <span className="text-[10px] text-white/70 truncate">{garment.brand}</span>
                    )}
                </div>
            </div>
        </div>
    );
}
