import { Play } from 'lucide-react';

interface VideoPlayerProps {
    src: string;
    className?: string;
}

export default function VideoPlayer({ src, className = '' }: VideoPlayerProps) {
    return (
        <div className={`relative rounded-xl overflow-hidden bg-black ${className}`}>
            <video
                src={src}
                controls
                className="w-full h-full"
                playsInline
                preload="metadata"
            >
                Your browser does not support the video tag.
            </video>
        </div>
    );
}
