/**
 * Body Measurement Fit Score Utility
 * Compares user body measurements against garment measurements.
 */

import { User, Garment } from '@/types';

export type FitLabel = 'great' | 'good' | 'tight' | 'loose' | 'unknown';

export interface FitResult {
    score: number;       // 0-100
    label: FitLabel;
    details: string[];
}

interface MeasurementPair {
    name: string;
    userValue: number | null | undefined;
    garmentValue: number | null | undefined;
    weight: number;
}

function getMeasurementPairs(user: User, garment: Garment): MeasurementPair[] {
    const pairs: MeasurementPair[] = [];

    if (garment.category === 'upper' || garment.category === 'dress') {
        pairs.push({
            name: 'chest',
            userValue: user.chest_cm,
            garmentValue: garment.measurement_chest_cm,
            weight: 3,
        });
        pairs.push({
            name: 'shoulder',
            userValue: undefined, // User model doesn't have shoulder
            garmentValue: garment.measurement_shoulder_cm,
            weight: 2,
        });
    }

    if (garment.category === 'lower' || garment.category === 'dress') {
        pairs.push({
            name: 'waist',
            userValue: user.waist_cm,
            garmentValue: garment.measurement_waist_cm,
            weight: 3,
        });
        pairs.push({
            name: 'hips',
            userValue: user.hips_cm,
            garmentValue: undefined, // Garment doesn't have hips
            weight: 2,
        });
    }

    if (garment.category === 'lower') {
        pairs.push({
            name: 'inseam',
            userValue: user.inseam_cm,
            garmentValue: garment.measurement_inseam_cm,
            weight: 2,
        });
    }

    return pairs;
}

function scorePair(userVal: number, garmentVal: number): { score: number; label: FitLabel; detail: string } {
    const diff = garmentVal - userVal;
    const absDiff = Math.abs(diff);

    if (absDiff <= 2) {
        return { score: 100, label: 'great', detail: 'perfect match' };
    }
    if (absDiff <= 5) {
        return { score: 75, label: 'good', detail: diff > 0 ? 'slightly loose' : 'slightly snug' };
    }
    if (diff < -5) {
        return { score: 30, label: 'tight', detail: `${absDiff.toFixed(0)}cm too small` };
    }
    return { score: 50, label: 'loose', detail: `${absDiff.toFixed(0)}cm too large` };
}

export function computeFitScore(user: User, garment: Garment): FitResult {
    const pairs = getMeasurementPairs(user, garment);

    // Filter to pairs where both values exist
    const validPairs = pairs.filter(
        (p) => p.userValue != null && p.garmentValue != null
    );

    if (validPairs.length < 1) {
        return { score: 0, label: 'unknown', details: ['Not enough measurements to compare'] };
    }

    let totalWeight = 0;
    let weightedScore = 0;
    const details: string[] = [];
    const labels: FitLabel[] = [];

    for (const pair of validPairs) {
        const result = scorePair(pair.userValue!, pair.garmentValue!);
        weightedScore += result.score * pair.weight;
        totalWeight += pair.weight;
        details.push(`${pair.name}: ${result.detail}`);
        labels.push(result.label);
    }

    const score = Math.round(weightedScore / totalWeight);

    // Determine overall label
    let label: FitLabel;
    if (score >= 85) label = 'great';
    else if (score >= 60) label = 'good';
    else if (labels.includes('tight')) label = 'tight';
    else if (labels.includes('loose')) label = 'loose';
    else label = 'good';

    return { score, label, details };
}
