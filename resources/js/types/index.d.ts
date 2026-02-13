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
    color_tags?: Array<{ hex: string; name: string }> | null;
    size_label?: string | null;
    brand?: string | null;
    material?: string | null;
    source_url?: string | null;
    source_provider?: string | null;
    perceptual_hash?: string | null;
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
        color_tags?: Array<{ hex: string; name: string }> | null;
    }[];
    suggestion_text: string;
    occasion: string | null;
    is_saved: boolean;
    harmony_score?: number | null;
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

export interface Lookbook {
    id: number;
    name: string;
    description: string | null;
    cover_image_url: string | null;
    is_public: boolean;
    slug: string;
    items_count: number;
    items?: LookbookItem[];
    created_at: string;
}

export interface LookbookItem {
    id: number;
    itemable_type: string;
    itemable_id: number;
    note: string | null;
    sort_order: number;
    item: TryOnResult | OutfitSuggestion;
}

export interface ShareLink {
    id: number;
    token: string;
    url: string;
    shareable_type: string;
    expires_at: string | null;
    is_active: boolean;
    view_count: number;
    reactions_summary: Record<string, number>;
}

export interface OutfitTemplate {
    id: number;
    name: string;
    occasion: string;
    description: string | null;
    icon: string | null;
    slots: TemplateSlot[];
    is_system: boolean;
}

export interface TemplateSlot {
    label: string;
    category: 'upper' | 'lower' | 'dress';
    required: boolean;
}

export interface Outfit {
    id: number;
    name: string;
    occasion: string | null;
    notes: string | null;
    harmony_score: number | null;
    template: OutfitTemplate | null;
    garments: Garment[];
    created_at: string;
}

export interface PackingList {
    id: number;
    name: string;
    destination: string | null;
    start_date: string | null;
    end_date: string | null;
    occasions: string[] | null;
    notes: string | null;
    items: PackingListItem[];
    packed_count: number;
    total_count: number;
    created_at: string;
}

export interface PackingListItem {
    id: number;
    garment: Garment;
    day_number: number | null;
    occasion: string | null;
    is_packed: boolean;
}

export interface ScrapedProduct {
    name: string;
    brand: string | null;
    image_url: string;
    category_hint: string | null;
    material: string | null;
    description: string | null;
    source_url: string;
    source_provider: string;
}

export interface ExportStatus {
    id: number;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    file_size_bytes: number | null;
    download_url: string | null;
    created_at: string;
}
