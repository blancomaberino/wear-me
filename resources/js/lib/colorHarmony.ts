/**
 * Color Harmony Scoring Utility
 * Evaluates how well a set of colors work together based on color theory.
 */

interface HSL {
    h: number; // 0-360
    s: number; // 0-100
    l: number; // 0-100
}

export function hexToHsl(hex: string): HSL {
    hex = hex.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16) / 255;
    const g = parseInt(hex.substring(2, 4), 16) / 255;
    const b = parseInt(hex.substring(4, 6), 16) / 255;

    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const l = (max + min) / 2;

    if (max === min) {
        return { h: 0, s: 0, l: Math.round(l * 100) };
    }

    const d = max - min;
    const s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

    let h = 0;
    if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
    else if (max === g) h = ((b - r) / d + 2) / 6;
    else h = ((r - g) / d + 4) / 6;

    return {
        h: Math.round(h * 360),
        s: Math.round(s * 100),
        l: Math.round(l * 100),
    };
}

export function angularDistance(h1: number, h2: number): number {
    const diff = Math.abs(h1 - h2);
    return Math.min(diff, 360 - diff);
}

export type HarmonyType =
    | 'monochromatic'
    | 'analogous'
    | 'complementary'
    | 'triadic'
    | 'split-complementary'
    | 'neutral'
    | 'clashing';

export function getHarmonyType(colors: string[]): HarmonyType {
    if (colors.length < 2) return 'neutral';

    const hsls = colors.map(hexToHsl);

    // Filter out very low saturation (grays/whites/blacks) — they're neutral
    const chromatic = hsls.filter((c) => c.s > 10 && c.l > 10 && c.l < 90);
    if (chromatic.length < 2) return 'neutral';

    const hues = chromatic.map((c) => c.h);
    const distances: number[] = [];
    for (let i = 0; i < hues.length; i++) {
        for (let j = i + 1; j < hues.length; j++) {
            distances.push(angularDistance(hues[i], hues[j]));
        }
    }

    const avgDist = distances.reduce((a, b) => a + b, 0) / distances.length;
    const maxDist = Math.max(...distances);

    if (maxDist < 15) return 'monochromatic';
    if (maxDist < 45) return 'analogous';
    if (distances.some((d) => d >= 150 && d <= 210)) {
        if (distances.some((d) => d >= 30 && d <= 60)) return 'split-complementary';
        return 'complementary';
    }
    if (distances.some((d) => d >= 100 && d <= 140)) return 'triadic';
    if (avgDist > 60 && avgDist < 150) return 'clashing';

    return 'clashing';
}

export function getHarmonyScore(colors: string[]): number {
    if (colors.length < 2) return 100;

    const hsls = colors.map(hexToHsl);
    const chromatic = hsls.filter((c) => c.s > 10 && c.l > 10 && c.l < 90);

    if (chromatic.length < 2) return 90; // Neutral palette is fine

    const type = getHarmonyType(colors);

    // Base score by harmony type
    const baseScores: Record<HarmonyType, [number, number]> = {
        monochromatic: [85, 100],
        analogous: [75, 90],
        complementary: [70, 85],
        triadic: [65, 80],
        'split-complementary': [60, 75],
        neutral: [80, 95],
        clashing: [20, 50],
    };

    const [min, max] = baseScores[type];
    let score = (min + max) / 2;

    // Bonus for saturation harmony (colors with similar saturation work better)
    const saturations = chromatic.map((c) => c.s);
    const satRange = Math.max(...saturations) - Math.min(...saturations);
    if (satRange < 20) score += 5;
    else if (satRange > 50) score -= 5;

    // Bonus for lightness variety (some contrast is good)
    const lightnesses = chromatic.map((c) => c.l);
    const lightRange = Math.max(...lightnesses) - Math.min(...lightnesses);
    if (lightRange >= 15 && lightRange <= 50) score += 5;

    return Math.min(100, Math.max(0, Math.round(score)));
}
