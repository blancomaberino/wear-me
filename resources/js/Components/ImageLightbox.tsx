import { Dialog as HDialog, Transition, TransitionChild } from '@headlessui/react';
import { Fragment, useState, useRef, useCallback } from 'react';
import { X, ZoomIn, ZoomOut, RotateCcw } from 'lucide-react';

interface ImageLightboxProps {
    src: string;
    alt?: string;
    children: (open: () => void) => React.ReactNode;
}

export default function ImageLightbox({ src, alt = '', children }: ImageLightboxProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [scale, setScale] = useState(1);
    const [position, setPosition] = useState({ x: 0, y: 0 });
    const [dragging, setDragging] = useState(false);
    const dragStart = useRef({ x: 0, y: 0 });
    const posStart = useRef({ x: 0, y: 0 });

    const open = () => {
        setScale(1);
        setPosition({ x: 0, y: 0 });
        setIsOpen(true);
    };

    const close = () => setIsOpen(false);

    const zoomIn = () => setScale(s => Math.min(s + 0.5, 4));
    const zoomOut = () => setScale(s => Math.max(s - 0.5, 0.5));
    const resetZoom = () => {
        setScale(1);
        setPosition({ x: 0, y: 0 });
    };

    const handleWheel = useCallback((e: React.WheelEvent) => {
        e.preventDefault();
        setScale(s => {
            const delta = e.deltaY > 0 ? -0.15 : 0.15;
            return Math.min(Math.max(s + delta, 0.5), 4);
        });
    }, []);

    const handlePointerDown = (e: React.PointerEvent) => {
        if (scale <= 1) return;
        setDragging(true);
        dragStart.current = { x: e.clientX, y: e.clientY };
        posStart.current = { ...position };
        (e.target as HTMLElement).setPointerCapture(e.pointerId);
    };

    const handlePointerMove = (e: React.PointerEvent) => {
        if (!dragging) return;
        setPosition({
            x: posStart.current.x + (e.clientX - dragStart.current.x),
            y: posStart.current.y + (e.clientY - dragStart.current.y),
        });
    };

    const handlePointerUp = () => setDragging(false);

    const handleDoubleClick = () => {
        if (scale > 1) {
            resetZoom();
        } else {
            setScale(2);
        }
    };

    return (
        <>
            {children(open)}

            <Transition show={isOpen} as={Fragment}>
                <HDialog onClose={close} className="relative z-[60]">
                    <TransitionChild
                        as={Fragment}
                        enter="ease-out duration-200"
                        enterFrom="opacity-0"
                        enterTo="opacity-100"
                        leave="ease-in duration-150"
                        leaveFrom="opacity-100"
                        leaveTo="opacity-0"
                    >
                        <div className="fixed inset-0 bg-black/90" />
                    </TransitionChild>

                    <div className="fixed inset-0 flex flex-col">
                        {/* Toolbar */}
                        <div className="flex items-center justify-between px-4 py-3 bg-black/40">
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={zoomOut}
                                    className="p-2 rounded-full text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                                >
                                    <ZoomOut className="h-5 w-5" />
                                </button>
                                <span className="text-white/70 text-caption tabular-nums min-w-[3.5rem] text-center">
                                    {Math.round(scale * 100)}%
                                </span>
                                <button
                                    onClick={zoomIn}
                                    className="p-2 rounded-full text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                                >
                                    <ZoomIn className="h-5 w-5" />
                                </button>
                                <button
                                    onClick={resetZoom}
                                    className="p-2 rounded-full text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                                >
                                    <RotateCcw className="h-5 w-5" />
                                </button>
                            </div>
                            <button
                                onClick={close}
                                className="p-2 rounded-full text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        {/* Image area */}
                        <div
                            className="flex-1 flex items-center justify-center overflow-hidden cursor-grab active:cursor-grabbing"
                            onWheel={handleWheel}
                            onDoubleClick={handleDoubleClick}
                        >
                            <img
                                src={src}
                                alt={alt}
                                draggable={false}
                                className="max-w-full max-h-full select-none transition-transform duration-100"
                                style={{
                                    transform: `translate(${position.x}px, ${position.y}px) scale(${scale})`,
                                    cursor: scale > 1 ? (dragging ? 'grabbing' : 'grab') : 'zoom-in',
                                }}
                                onPointerDown={handlePointerDown}
                                onPointerMove={handlePointerMove}
                                onPointerUp={handlePointerUp}
                            />
                        </div>
                    </div>
                </HDialog>
            </Transition>
        </>
    );
}
