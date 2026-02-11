import { Button } from '@/Components/ui/Button';
import { Transition } from '@headlessui/react';
import { router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

interface ModelImageOption {
    id: number;
    thumbnail_url: string;
}

function hexToHsl(hex: string): [number, number, number] {
    const r = parseInt(hex.slice(1, 3), 16) / 255;
    const g = parseInt(hex.slice(3, 5), 16) / 255;
    const b = parseInt(hex.slice(5, 7), 16) / 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    const l = (max + min) / 2;
    if (max === min) return [0, 0, l];
    const d = max - min;
    const s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    let h = 0;
    if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
    else if (max === g) h = ((b - r) / d + 2) / 6;
    else h = ((r - g) / d + 4) / 6;
    return [h, s, l];
}

type ToneKey = 'reds' | 'oranges' | 'yellows' | 'greens' | 'teals' | 'blues' | 'purples' | 'neutrals';

const TONE_LABELS: Record<ToneKey, string> = {
    neutrals: 'profile.toneNeutrals',
    reds: 'profile.toneReds',
    oranges: 'profile.toneOranges',
    yellows: 'profile.toneYellows',
    greens: 'profile.toneGreens',
    teals: 'profile.toneTeals',
    blues: 'profile.toneBlues',
    purples: 'profile.tonePurples',
};

function getToneGroup(hex: string): ToneKey {
    const [h, s] = hexToHsl(hex);
    if (s < 0.1) return 'neutrals';
    const deg = h * 360;
    if (deg < 15 || deg >= 345) return 'reds';
    if (deg < 45) return 'oranges';
    if (deg < 70) return 'yellows';
    if (deg < 165) return 'greens';
    if (deg < 200) return 'teals';
    if (deg < 260) return 'blues';
    return 'purples';
}

export default function ColorPaletteForm({
    colorPalette,
    modelImages,
    className = '',
}: {
    colorPalette: string[];
    modelImages: ModelImageOption[];
    className?: string;
}) {
    const { t } = useTranslation();
    const [colors, setColors] = useState<string[]>(colorPalette ?? []);
    const [detecting, setDetecting] = useState(false);
    const [detectError, setDetectError] = useState('');
    const [selectedImageId, setSelectedImageId] = useState<number | null>(null);
    const [colorCount, setColorCount] = useState(8);
    const [groupedView, setGroupedView] = useState(false);

    const { put, processing, recentlySuccessful } = useForm({});

    const addColor = () => {
        if (colors.length < 50) {
            setColors([...colors, '#6366f1']);
        }
    };

    const updateColor = (index: number, color: string) => {
        const updated = [...colors];
        updated[index] = color;
        setColors(updated);
    };

    const removeColor = (index: number) => {
        setColors(colors.filter((_, i) => i !== index));
    };

    const toneGroups = useMemo(() => {
        if (!groupedView) return null;
        const groups: Record<ToneKey, { color: string; index: number }[]> = {
            reds: [], oranges: [], yellows: [], greens: [],
            teals: [], blues: [], purples: [], neutrals: [],
        };
        colors.forEach((color, index) => {
            groups[getToneGroup(color)].push({ color, index });
        });
        // Sort each group by lightness
        for (const key of Object.keys(groups) as ToneKey[]) {
            groups[key].sort((a, b) => {
                const [, , lA] = hexToHsl(a.color);
                const [, , lB] = hexToHsl(b.color);
                return lA - lB;
            });
        }
        return (Object.keys(groups) as ToneKey[]).filter((key) => groups[key].length > 0).map((key) => ({
            key,
            label: TONE_LABELS[key],
            items: groups[key],
        }));
    }, [colors, groupedView]);

    const savePalette: FormEventHandler = (e) => {
        e.preventDefault();
        router.put(route('profile.palette.update'), { colors }, {
            preserveScroll: true,
        });
    };

    const detectColors = async () => {
        if (!selectedImageId) return;

        setDetecting(true);
        setDetectError('');

        try {
            const response = await axios.post(route('profile.palette.detect'), {
                model_image_id: selectedImageId,
                color_count: colorCount,
            });

            if (response.data.colors && Array.isArray(response.data.colors)) {
                setColors(response.data.colors);
            }
        } catch (err: unknown) {
            if (axios.isAxiosError(err)) {
                setDetectError(err.response?.data?.message ?? t('profile.detectError'));
            } else {
                setDetectError(t('profile.detectError'));
            }
        } finally {
            setDetecting(false);
        }
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-heading-sm text-surface-900">
                    {t('profile.paletteTitle')}
                </h2>
                <p className="mt-1 text-body-sm text-surface-500">
                    {t('profile.paletteDesc')}
                </p>
            </header>

            <form onSubmit={savePalette} className="mt-6 space-y-6">
                <div>
                    {groupedView && toneGroups ? (
                        <div className="space-y-4">
                            {toneGroups.map((group) => (
                                <div key={group.key}>
                                    <p className="text-caption font-medium text-surface-500 mb-2">{t(group.label)}</p>
                                    <div className="flex flex-wrap items-center gap-3">
                                        {group.items.map(({ color, index }) => (
                                            <div key={index} className="relative group">
                                                <input
                                                    type="color"
                                                    value={color}
                                                    onChange={(e) => updateColor(index, e.target.value)}
                                                    className="h-12 w-12 cursor-pointer rounded-lg border-2 border-gray-200 p-0.5 transition hover:border-gray-400"
                                                    title={color}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => removeColor(index)}
                                                    className="absolute -right-1.5 -top-1.5 hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white group-hover:flex"
                                                >
                                                    X
                                                </button>
                                                <span className="mt-1 block text-center text-xs text-surface-500">
                                                    {color.toUpperCase()}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                            {colors.length < 50 && (
                                <button
                                    type="button"
                                    onClick={addColor}
                                    className="flex h-12 w-12 items-center justify-center rounded-lg border-2 border-dashed border-surface-300 text-surface-400 transition hover:border-surface-400 hover:text-surface-600"
                                    title={t('profile.addColor')}
                                >
                                    +
                                </button>
                            )}
                        </div>
                    ) : (
                        <div className="flex flex-wrap items-center gap-3">
                            {colors.map((color, index) => (
                                <div key={index} className="relative group">
                                    <input
                                        type="color"
                                        value={color}
                                        onChange={(e) => updateColor(index, e.target.value)}
                                        className="h-12 w-12 cursor-pointer rounded-lg border-2 border-gray-200 p-0.5 transition hover:border-gray-400"
                                        title={color}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => removeColor(index)}
                                        className="absolute -right-1.5 -top-1.5 hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white group-hover:flex"
                                    >
                                        X
                                    </button>
                                    <span className="mt-1 block text-center text-xs text-surface-500">
                                        {color.toUpperCase()}
                                    </span>
                                </div>
                            ))}

                            {colors.length < 50 && (
                                <button
                                    type="button"
                                    onClick={addColor}
                                    className="flex h-12 w-12 items-center justify-center rounded-lg border-2 border-dashed border-surface-300 text-surface-400 transition hover:border-surface-400 hover:text-surface-600"
                                    title={t('profile.addColor')}
                                >
                                    +
                                </button>
                            )}
                        </div>
                    )}

                    {colors.length >= 2 && (
                        <button
                            type="button"
                            onClick={() => setGroupedView(!groupedView)}
                            className="mt-3 text-sm text-brand-600 hover:text-brand-800 transition"
                        >
                            {groupedView ? t('profile.ungroupTones') : t('profile.sortByTone')}
                        </button>
                    )}

                    {colors.length === 0 && (
                        <p className="mt-2 text-sm text-surface-500">
                            {t('profile.noColors')}
                        </p>
                    )}
                </div>

                {modelImages.length > 0 && (
                    <div>
                        <div className="relative my-4">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-surface-200" />
                            </div>
                            <div className="relative flex justify-center text-sm">
                                <span className="bg-white px-2 text-surface-500">
                                    {t('profile.orDetect')}
                                </span>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            {modelImages.map((image) => (
                                <button
                                    key={image.id}
                                    type="button"
                                    onClick={() => setSelectedImageId(image.id)}
                                    className={`h-16 w-16 overflow-hidden rounded-lg border-2 transition ${
                                        selectedImageId === image.id
                                            ? 'border-brand-500 ring-2 ring-brand-200'
                                            : 'border-surface-200 hover:border-surface-400'
                                    }`}
                                >
                                    <img
                                        src={image.thumbnail_url}
                                        alt={t('profile.modelPhoto')}
                                        className="h-full w-full object-cover"
                                    />
                                </button>
                            ))}

                            <div className="flex items-center gap-1.5">
                                <label htmlFor="colorCount" className="text-sm text-surface-600">
                                    {t('profile.colorsLabel')}
                                </label>
                                <input
                                    id="colorCount"
                                    type="number"
                                    min={1}
                                    max={50}
                                    value={colorCount}
                                    onChange={(e) => setColorCount(Math.min(50, Math.max(1, Number(e.target.value))))}
                                    className="w-16 rounded-md border-surface-300 text-sm shadow-sm"
                                />
                            </div>

                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                onClick={detectColors}
                                disabled={!selectedImageId || detecting}
                                loading={detecting}
                            >
                                {detecting ? t('profile.detecting') : t('profile.detectPalette')}
                            </Button>
                        </div>

                        {detectError && (
                            <p className="mt-2 text-sm text-red-600">{detectError}</p>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <Button disabled={processing || colors.length === 0}>
                        {t('profile.savePalette')}
                    </Button>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-surface-600">{t('profile.saved')}</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
