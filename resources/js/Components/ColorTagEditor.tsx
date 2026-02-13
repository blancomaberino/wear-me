import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { X, Plus, Palette } from 'lucide-react';
import { cn } from '@/lib/utils';

const PRESET_COLORS: Array<{ hex: string; name: string }> = [
    { hex: '#000000', name: 'Black' },
    { hex: '#FFFFFF', name: 'White' },
    { hex: '#808080', name: 'Gray' },
    { hex: '#000080', name: 'Navy' },
    { hex: '#0000FF', name: 'Blue' },
    { hex: '#87CEEB', name: 'Sky Blue' },
    { hex: '#FF0000', name: 'Red' },
    { hex: '#800020', name: 'Burgundy' },
    { hex: '#008000', name: 'Green' },
    { hex: '#808000', name: 'Olive' },
    { hex: '#FFD700', name: 'Yellow' },
    { hex: '#FF8C00', name: 'Orange' },
    { hex: '#800080', name: 'Purple' },
    { hex: '#FFC0CB', name: 'Pink' },
    { hex: '#8B4513', name: 'Brown' },
    { hex: '#F5F5DC', name: 'Beige' },
    { hex: '#D2B48C', name: 'Tan' },
    { hex: '#008080', name: 'Teal' },
];

interface Props {
    colors: Array<{ hex: string; name: string }>;
    onChange?: (colors: Array<{ hex: string; name: string }>) => void;
    readOnly?: boolean;
    className?: string;
}

export default function ColorTagEditor({ colors, onChange, readOnly, className }: Props) {
    const { t } = useTranslation();
    const [showPicker, setShowPicker] = useState(false);
    const isEditable = !readOnly && onChange;
    const maxColorsReached = colors.length >= 10;

    const handleRemove = (index: number) => {
        if (!onChange) return;
        const newColors = colors.filter((_, i) => i !== index);
        onChange(newColors);
    };

    const handleAddColor = (color: { hex: string; name: string }) => {
        if (!onChange) return;

        // Check if color already exists
        const exists = colors.some(c => c.hex.toLowerCase() === color.hex.toLowerCase());
        if (exists) {
            setShowPicker(false);
            return;
        }

        // Check max colors
        if (maxColorsReached) {
            return;
        }

        const newColors = [...colors, color];
        onChange(newColors);
        setShowPicker(false);
    };

    if (colors.length === 0 && !isEditable) {
        return (
            <div className={cn('text-surface-600 text-body-sm', className)}>
                {t('wardrobe.noColors')}
            </div>
        );
    }

    return (
        <div className={className}>
            {/* Color pills */}
            <div className="flex flex-wrap gap-2">
                {colors.map((color, index) => (
                    <div
                        key={`${color.hex}-${index}`}
                        className="px-2 py-0.5 rounded-pill bg-surface-100 text-surface-700 text-caption font-medium flex items-center gap-1.5 animate-fade-in"
                    >
                        <div
                            className="w-3 h-3 rounded-full border border-surface-200 flex-shrink-0"
                            style={{ backgroundColor: color.hex }}
                            aria-label={color.name}
                        />
                        <span>{color.name}</span>
                        {isEditable && (
                            <button
                                type="button"
                                onClick={() => handleRemove(index)}
                                className="ml-0.5 hover:text-surface-900 transition-colors"
                                aria-label={`Remove ${color.name}`}
                            >
                                <X className="w-3 h-3" />
                            </button>
                        )}
                    </div>
                ))}

                {/* Add color button (edit mode only) */}
                {isEditable && !maxColorsReached && (
                    <div className="relative">
                        <button
                            type="button"
                            onClick={() => setShowPicker(!showPicker)}
                            className="px-2 py-0.5 rounded-pill bg-surface-50 hover:bg-surface-200 text-surface-600 text-caption font-medium flex items-center gap-1 transition-colors"
                        >
                            <Plus className="w-3 h-3" />
                            <span>{t('wardrobe.addColor')}</span>
                        </button>

                        {/* Color picker popover */}
                        {showPicker && (
                            <>
                                {/* Backdrop to close picker */}
                                <div
                                    className="fixed inset-0 z-10"
                                    onClick={() => setShowPicker(false)}
                                />

                                {/* Picker popover */}
                                <div className="absolute top-full left-0 mt-2 p-3 bg-white rounded-lg shadow-lg border border-surface-200 z-20 animate-fade-in">
                                    <div className="flex items-center gap-2 mb-2 text-surface-700 text-caption font-medium">
                                        <Palette className="w-3.5 h-3.5" />
                                        <span>{t('wardrobe.addColor')}</span>
                                    </div>

                                    {/* Preset color grid - 2 rows */}
                                    <div className="grid grid-cols-9 gap-2 w-max">
                                        {PRESET_COLORS.map((color) => {
                                            const isSelected = colors.some(
                                                c => c.hex.toLowerCase() === color.hex.toLowerCase()
                                            );
                                            return (
                                                <button
                                                    key={color.hex}
                                                    type="button"
                                                    onClick={() => handleAddColor(color)}
                                                    disabled={isSelected}
                                                    className={cn(
                                                        'w-8 h-8 rounded-full border-2 transition-all',
                                                        isSelected
                                                            ? 'border-surface-400 opacity-40 cursor-not-allowed'
                                                            : 'border-surface-200 hover:border-surface-400 hover:scale-110 cursor-pointer'
                                                    )}
                                                    style={{ backgroundColor: color.hex }}
                                                    title={color.name}
                                                    aria-label={color.name}
                                                />
                                            );
                                        })}
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                )}
            </div>

            {/* Max colors message */}
            {isEditable && maxColorsReached && (
                <div className="mt-2 text-surface-600 text-caption">
                    {t('wardrobe.maxColors')}
                </div>
            )}
        </div>
    );
}
