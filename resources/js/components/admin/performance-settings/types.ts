export interface CategoryOption {
    value: string;
    label: string;
}

export interface Criterion {
    id: number;
    name: string;
    nameAm: string | null;
    category: string;
    categoryLabel: string;
    weight: number;
    sortOrder: number;
    active: boolean;
    description: string | null;
    scoresCount: number;
}
