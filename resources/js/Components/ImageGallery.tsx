import { X, Star } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface GalleryItem {
    id: number;
    url?: string;
    thumbnail_url: string | null;
    original_filename?: string;
    name?: string | null;
    is_primary?: boolean;
}

interface ImageGalleryProps {
    items: GalleryItem[];
    onDelete?: (id: number) => void;
    onSelect?: (id: number) => void;
    onSetPrimary?: (id: number) => void;
    selectedId?: number | null;
    columns?: number;
    showPrimary?: boolean;
}

export default function ImageGallery({
    items,
    onDelete,
    onSelect,
    onSetPrimary,
    selectedId,
    columns = 4,
    showPrimary = false,
}: ImageGalleryProps) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return (
            <div className="text-center py-12 text-gray-500">
                <p className="text-lg">{t('common.noImages')}</p>
                <p className="text-sm mt-1">{t('common.uploadFirst')}</p>
            </div>
        );
    }

    return (
        <div className={`grid grid-cols-2 md:grid-cols-3 lg:grid-cols-${columns} gap-4`}>
            {items.map((item) => (
                <div
                    key={item.id}
                    onClick={() => onSelect?.(item.id)}
                    className={`group relative aspect-square rounded-xl overflow-hidden bg-gray-100 ${
                        onSelect ? 'cursor-pointer' : ''
                    } ${
                        selectedId === item.id
                            ? 'ring-4 ring-brand-500 ring-offset-2'
                            : 'hover:ring-2 hover:ring-gray-300'
                    } transition-all duration-200`}
                >
                    <img
                        src={item.thumbnail_url || item.url}
                        alt={item.name || item.original_filename || t('common.image')}
                        className="w-full h-full object-cover"
                        loading="lazy"
                    />

                    {showPrimary && item.is_primary && (
                        <div className="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
                            <Star className="w-3 h-3 fill-current" /> {t('photos.primary')}
                        </div>
                    )}

                    <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200" />

                    <div className="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        {showPrimary && !item.is_primary && onSetPrimary && (
                            <button
                                onClick={(e) => { e.stopPropagation(); onSetPrimary(item.id); }}
                                className="p-1.5 bg-white rounded-full shadow hover:bg-yellow-50 transition-colors"
                                title={t('photos.setPrimary')}
                            >
                                <Star className="w-4 h-4 text-gray-600" />
                            </button>
                        )}
                        {onDelete && (
                            <button
                                onClick={(e) => { e.stopPropagation(); onDelete(item.id); }}
                                className="p-1.5 bg-white rounded-full shadow hover:bg-red-50 transition-colors"
                                title={t('common.delete')}
                            >
                                <X className="w-4 h-4 text-red-500" />
                            </button>
                        )}
                    </div>

                    {item.name && (
                        <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-3">
                            <p className="text-white text-sm font-medium truncate">{item.name}</p>
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
