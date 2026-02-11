import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Link2, Download, Loader2 } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';
import { ScrapedProduct } from '@/types';

interface Props {
    open: boolean;
    onClose: () => void;
}

export default function ImportFromUrlDialog({ open, onClose }: Props) {
    const { t } = useTranslation();
    const [url, setUrl] = useState('');
    const [loading, setLoading] = useState(false);
    const [importing, setImporting] = useState(false);
    const [product, setProduct] = useState<ScrapedProduct | null>(null);
    const [category, setCategory] = useState('upper');
    const [error, setError] = useState<string | null>(null);

    const categories = [
        { value: 'upper', label: t('wardrobe.catTop') },
        { value: 'lower', label: t('wardrobe.catBottom') },
        { value: 'dress', label: t('wardrobe.catDress') },
    ];

    const handlePreview = async () => {
        setLoading(true);
        setError(null);
        setProduct(null);

        try {
            const response = await fetch(route('import.preview'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ url }),
            });

            const data = await response.json();

            if (data.success && data.product) {
                setProduct(data.product);
            } else {
                setError(data.error || t('import.importError'));
            }
        } catch {
            setError(t('import.importError'));
        } finally {
            setLoading(false);
        }
    };

    const handleConfirm = () => {
        if (!product) return;
        setImporting(true);

        router.post(route('import.confirm'), {
            source_url: product.source_url,
            image_url: product.image_url,
            name: product.name,
            category,
            brand: product.brand,
            material: product.material,
            source_provider: product.source_provider,
        }, {
            onSuccess: () => handleClose(),
            onFinish: () => setImporting(false),
        });
    };

    const handleClose = () => {
        setUrl('');
        setProduct(null);
        setError(null);
        setLoading(false);
        setImporting(false);
        setCategory('upper');
        onClose();
    };

    return (
        <Dialog open={open} onClose={handleClose} title={t('import.title')} size="lg">
            <div className="space-y-5">
                {/* URL Input */}
                <div className="space-y-2">
                    <div className="flex gap-2">
                        <div className="flex-1">
                            <Input
                                value={url}
                                onChange={(e) => setUrl(e.target.value)}
                                placeholder={t('import.urlPlaceholder')}
                                label={t('import.pasteUrl')}
                            />
                        </div>
                        <div className="flex items-end">
                            <Button onClick={handlePreview} loading={loading} disabled={!url.trim()}>
                                <Link2 className="h-4 w-4" /> {t('import.preview')}
                            </Button>
                        </div>
                    </div>
                    <p className="text-caption text-surface-400">{t('import.supportedSites')}</p>
                </div>

                {error && <p className="text-caption text-red-600">{error}</p>}

                {/* Product Preview */}
                {product && (
                    <div className="space-y-4 animate-fade-in">
                        <div className="flex gap-4 p-4 rounded-card bg-surface-50 border border-surface-200">
                            {product.image_url ? (
                                <img
                                    src={product.image_url}
                                    alt={product.name}
                                    className="w-24 h-24 object-cover rounded-input bg-white"
                                />
                            ) : (
                                <div className="w-24 h-24 rounded-input bg-surface-200 flex items-center justify-center text-surface-400 text-caption">
                                    {t('import.noImage')}
                                </div>
                            )}
                            <div className="flex-1 min-w-0">
                                <h3 className="text-body-sm font-medium text-surface-900 line-clamp-2">{product.name}</h3>
                                {product.brand && (
                                    <p className="text-caption text-surface-500 mt-1">{product.brand}</p>
                                )}
                                {product.material && (
                                    <p className="text-caption text-surface-400 mt-0.5">{product.material}</p>
                                )}
                            </div>
                        </div>

                        {/* Category Selection */}
                        <div>
                            <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('wardrobe.category')}</label>
                            <div className="flex gap-2">
                                {categories.map((cat) => (
                                    <button
                                        key={cat.value}
                                        type="button"
                                        onClick={() => setCategory(cat.value)}
                                        className={cn(
                                            'px-4 py-1.5 rounded-pill text-body-sm font-medium transition-colors',
                                            category === cat.value
                                                ? 'bg-brand-600 text-white'
                                                : 'bg-surface-100 text-surface-600 hover:bg-surface-200',
                                        )}
                                    >
                                        {cat.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Import Button */}
                        <div className="flex justify-end gap-3">
                            <Button variant="ghost" onClick={handleClose}>{t('common.cancel')}</Button>
                            <Button onClick={handleConfirm} loading={importing} disabled={!product.image_url}>
                                <Download className="h-4 w-4" /> {t('import.confirm')}
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </Dialog>
    );
}
