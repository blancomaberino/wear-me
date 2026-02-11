export interface User {
    id: number;
    name: string;
    email: string;
    locale?: string;
    email_verified_at?: string;
    avatar?: string;
    google_id?: string;
    color_palette?: string[];
    measurement_unit?: 'metric' | 'imperial';
    height_cm?: number | null;
    weight_kg?: number | null;
    chest_cm?: number | null;
    waist_cm?: number | null;
    hips_cm?: number | null;
    inseam_cm?: number | null;
    shoe_size_eu?: number | null;
    has_measurements?: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    locale?: string;
    availableLocales?: string[];
    flash?: {
        success?: string;
        error?: string;
    };
};

export interface ModelImage {
    id: number;
    url: string;
    thumbnail_url: string | null;
    original_filename: string;
    is_primary: boolean;
    width?: number;
    height?: number;
    created_at: string;
}

export interface Garment {
    id: number;
    url: string;
    thumbnail_url: string | null;
    original_filename: string;
    name: string | null;
    category: 'upper' | 'lower' | 'dress';
    description?: string | null;
    color_tags?: string[] | null;
    size_label?: string | null;
    brand?: string | null;
    material?: string | null;
    measurement_chest_cm?: number | null;
    measurement_length_cm?: number | null;
    measurement_waist_cm?: number | null;
    measurement_inseam_cm?: number | null;
    measurement_shoulder_cm?: number | null;
    measurement_sleeve_cm?: number | null;
    created_at: string;
}

export interface TryOnResultGarment {
    id: number;
    name: string;
    url?: string;
    thumbnail_url: string | null;
    category: 'upper' | 'lower' | 'dress';
}

export interface TryOnResult {
    id: number;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    result_url: string | null;
    error_message: string | null;
    is_favorite: boolean;
    created_at: string;
    model_image: {
        url?: string;
        thumbnail_url: string | null;
    };
    garment: {
        name: string;
        url?: string;
        thumbnail_url: string | null;
        category?: string;
    } | null;
    garments?: TryOnResultGarment[];
}

export interface TryOnVideo {
    id: number;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    video_url: string | null;
    duration_seconds: number | null;
    error_message: string | null;
    created_at: string;
    model_image: {
        thumbnail_url: string | null;
    };
    garment: {
        name: string;
        thumbnail_url: string | null;
    };
}

export interface OutfitSuggestion {
    id: number;
    garment_ids: number[];
    garments: {
        id: number;
        name: string;
        thumbnail_url: string | null;
        category: string;
    }[];
    suggestion_text: string;
    occasion: string | null;
    is_saved: boolean;
    created_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface WardrobeStats {
    total: number;
    upper: number;
    lower: number;
    dress: number;
}
