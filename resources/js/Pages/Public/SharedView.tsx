import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { ThumbsUp, ThumbsDown, Heart, Flame, Eye } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    shareLink: {
        token: string;
        shareable_type: string;
        view_count: number;
        reactions_summary: Record<string, number>;
    };
    content: any;
}

const reactionIcons: Record<string, any> = {
    thumbs_up: ThumbsUp,
    thumbs_down: ThumbsDown,
    heart: Heart,
    fire: Flame,
};

const reactionLabels: Record<string, string> = {
    thumbs_up: '👍',
    thumbs_down: '👎',
    heart: '❤️',
    fire: '🔥',
};

export default function SharedView({ shareLink, content }: Props) {
    const { t } = useTranslation();
    const [reactions, setReactions] = useState(shareLink.reactions_summary);

    const handleReact = async (type: string) => {
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';
        try {
            const response = await fetch(route('share.react', shareLink.token), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ type }),
            });
            const data = await response.json();
            if (data.reactions_summary) {
                setReactions(data.reactions_summary);
            }
        } catch {}
    };

    return (
        <div className="min-h-screen bg-surface-50">
            <Head title={t('share.publicView')} />

            <div className="max-w-3xl mx-auto px-4 py-8">
                <h1 className="text-heading text-surface-900 mb-2">{t('share.publicView')}</h1>
                <p className="text-caption text-surface-400 flex items-center gap-1 mb-6">
                    <Eye className="h-3.5 w-3.5" /> {t('share.views', { count: shareLink.view_count })}
                </p>

                {/* Content */}
                <div className="bg-white rounded-card border border-surface-200 p-6 mb-6">
                    {shareLink.shareable_type === 'TryOnResult' && content?.result_url && (
                        <img src={content.result_url} alt="" className="w-full max-h-[600px] object-contain rounded-lg" />
                    )}
                    {shareLink.shareable_type === 'Lookbook' && content && (
                        <div>
                            <h2 className="text-heading-sm text-surface-900 mb-4">{content.name}</h2>
                            {content.description && <p className="text-body-sm text-surface-600 mb-4">{content.description}</p>}
                            {content.items && (
                                <div className="grid grid-cols-2 gap-3">
                                    {content.items.map((item: any) => (
                                        <div key={item.id} className="rounded-lg overflow-hidden bg-surface-50">
                                            {item.itemable?.result_url && (
                                                <img src={item.itemable.result_url} alt="" className="w-full h-48 object-cover" />
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Reactions */}
                <div className="bg-white rounded-card border border-surface-200 p-4">
                    <h3 className="text-body-sm font-medium text-surface-700 mb-3">{t('share.reactionsTitle')}</h3>
                    <div className="flex gap-2">
                        {Object.keys(reactionLabels).map((type) => (
                            <button
                                key={type}
                                onClick={() => handleReact(type)}
                                className="flex items-center gap-1.5 px-3 py-2 rounded-button border border-surface-200 hover:border-brand-400 hover:bg-brand-50 transition-colors text-body-sm"
                            >
                                <span>{reactionLabels[type]}</span>
                                <span className="text-surface-500 font-medium">{reactions[type] || 0}</span>
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
