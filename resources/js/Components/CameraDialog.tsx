import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { useRef, useState, useEffect, useCallback } from 'react';
import { Camera, SwitchCamera, Timer } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { cn } from '@/lib/utils';

interface CameraDialogProps {
    open: boolean;
    onClose: () => void;
    onCapture: (file: File) => void;
}

const TIMER_OPTIONS = [0, 3, 5, 10] as const;

export default function CameraDialog({ open, onClose, onCapture }: CameraDialogProps) {
    const { t } = useTranslation();
    const videoRef = useRef<HTMLVideoElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const streamRef = useRef<MediaStream | null>(null);
    const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const [facingMode, setFacingMode] = useState<'user' | 'environment'>('user');
    const [error, setError] = useState<string | null>(null);
    const [ready, setReady] = useState(false);
    const [timerDelay, setTimerDelay] = useState<number>(0);
    const [countdown, setCountdown] = useState<number | null>(null);

    const startCamera = useCallback(async (facing: 'user' | 'environment') => {
        if (streamRef.current) {
            streamRef.current.getTracks().forEach(track => track.stop());
        }
        setError(null);
        setReady(false);

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: facing, width: { ideal: 1280 }, height: { ideal: 960 } },
                audio: false,
            });
            streamRef.current = stream;
            if (videoRef.current) {
                videoRef.current.srcObject = stream;
                videoRef.current.onloadedmetadata = () => setReady(true);
            }
        } catch {
            setError(t('common.cameraError'));
        }
    }, [t]);

    useEffect(() => {
        if (open) {
            startCamera(facingMode);
        }
        return () => {
            if (streamRef.current) {
                streamRef.current.getTracks().forEach(track => track.stop());
                streamRef.current = null;
            }
            if (timerRef.current) {
                clearInterval(timerRef.current);
                timerRef.current = null;
            }
            setReady(false);
            setError(null);
            setCountdown(null);
        };
    }, [open, facingMode, startCamera]);

    const doCapture = useCallback(() => {
        const video = videoRef.current;
        const canvas = canvasRef.current;
        if (!video || !canvas) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        ctx.drawImage(video, 0, 0);
        canvas.toBlob((blob) => {
            if (blob) {
                const file = new File([blob], `camera-${Date.now()}.jpg`, { type: 'image/jpeg' });
                onCapture(file);
                onClose();
            }
        }, 'image/jpeg', 0.92);
    }, [onCapture, onClose]);

    const handleCapture = () => {
        if (timerDelay === 0) {
            doCapture();
            return;
        }

        setCountdown(timerDelay);
        timerRef.current = setInterval(() => {
            setCountdown(prev => {
                if (prev === null || prev <= 1) {
                    if (timerRef.current) {
                        clearInterval(timerRef.current);
                        timerRef.current = null;
                    }
                    doCapture();
                    return null;
                }
                return prev - 1;
            });
        }, 1000);
    };

    const cancelCountdown = () => {
        if (timerRef.current) {
            clearInterval(timerRef.current);
            timerRef.current = null;
        }
        setCountdown(null);
    };

    const toggleFacing = () => {
        setFacingMode(prev => prev === 'user' ? 'environment' : 'user');
    };

    const isCountingDown = countdown !== null;

    return (
        <Dialog open={open} onClose={isCountingDown ? cancelCountdown : onClose} title={t('common.takePhoto')} size="lg">
            <div className="space-y-4">
                <div className="relative rounded-xl overflow-hidden bg-black aspect-[4/3]">
                    {error ? (
                        <div className="absolute inset-0 flex flex-col items-center justify-center text-center p-6">
                            <Camera className="h-12 w-12 text-surface-400 mb-3" />
                            <p className="text-body-sm text-surface-400">{error}</p>
                        </div>
                    ) : (
                        <video
                            ref={videoRef}
                            autoPlay
                            playsInline
                            muted
                            className="w-full h-full object-cover"
                        />
                    )}

                    {/* Countdown overlay */}
                    {isCountingDown && (
                        <div className="absolute inset-0 flex items-center justify-center bg-black/30">
                            <span className="text-white text-7xl font-bold animate-pulse drop-shadow-lg tabular-nums">
                                {countdown}
                            </span>
                        </div>
                    )}

                    {/* Top controls */}
                    {!error && (
                        <div className="absolute top-3 right-3 flex gap-2">
                            <button
                                onClick={toggleFacing}
                                disabled={isCountingDown}
                                className="p-2 rounded-full bg-black/40 text-white hover:bg-black/60 transition-colors disabled:opacity-50"
                                title={t('common.switchCamera')}
                            >
                                <SwitchCamera className="h-5 w-5" />
                            </button>
                        </div>
                    )}
                </div>

                <canvas ref={canvasRef} className="hidden" />

                {/* Timer selector */}
                <div className="flex items-center justify-center gap-1">
                    <Timer className="h-4 w-4 text-surface-400 mr-1" />
                    {TIMER_OPTIONS.map((seconds) => (
                        <button
                            key={seconds}
                            onClick={() => setTimerDelay(seconds)}
                            disabled={isCountingDown}
                            className={cn(
                                'px-3 py-1.5 rounded-full text-caption font-medium transition-colors',
                                timerDelay === seconds
                                    ? 'bg-brand-600 text-white'
                                    : 'bg-surface-100 text-surface-600 hover:bg-surface-200',
                                isCountingDown && 'opacity-50 cursor-not-allowed',
                            )}
                        >
                            {seconds === 0 ? t('common.timerOff') : `${seconds}s`}
                        </button>
                    ))}
                </div>

                {/* Action buttons */}
                <div className="flex gap-3">
                    <Button variant="outline" className="flex-1" onClick={isCountingDown ? cancelCountdown : onClose}>
                        {isCountingDown ? t('common.cancel') : t('common.cancel')}
                    </Button>
                    <Button
                        variant="primary"
                        className="flex-1"
                        onClick={handleCapture}
                        disabled={!ready || !!error || isCountingDown}
                    >
                        <Camera className="h-4 w-4" />
                        {timerDelay > 0 && !isCountingDown
                            ? t('common.captureWithTimer', { seconds: timerDelay })
                            : t('common.capture')
                        }
                    </Button>
                </div>
            </div>
        </Dialog>
    );
}
