import { useEffect, useRef, useCallback } from 'react';

interface UsePollingOptions {
    url: string;
    interval?: number;
    enabled?: boolean;
    onData?: (data: any) => void;
    stopWhen?: (data: any) => boolean;
}

export function usePolling({ url, interval = 5000, enabled = true, onData, stopWhen }: UsePollingOptions) {
    const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const stoppedRef = useRef(false);

    const poll = useCallback(async () => {
        if (stoppedRef.current) return;
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (response.ok) {
                const data = await response.json();
                onData?.(data);
                if (stopWhen?.(data)) {
                    stoppedRef.current = true;
                    if (timerRef.current) clearInterval(timerRef.current);
                }
            }
        } catch (e) {
            // Silently continue polling
        }
    }, [url, onData, stopWhen]);

    useEffect(() => {
        if (!enabled) return;
        stoppedRef.current = false;
        poll();
        timerRef.current = setInterval(poll, interval);
        return () => {
            if (timerRef.current) clearInterval(timerRef.current);
        };
    }, [enabled, interval, poll]);

    return { stop: () => { stoppedRef.current = true; if (timerRef.current) clearInterval(timerRef.current); } };
}
