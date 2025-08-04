export interface Newsletter {
    uuid: string;
    slug: string;
    name: string;
    logo: string | null;
}

export interface IssueList {
    uuid: string;
    subject: string;
    sent_at: number;
}

export interface Issue {
    subject: string;
    html: string;
}

export interface List {
    id: number;
    created_at: number;
    name: string;
    description: string | null;
}

export interface Palette {
    accent: string;
    accent_text: string;
    background: string;
    background_text: string;
    box: string;
    box_text: string;

    box_radius: string;
    box_shadow: string;
    box_border: string;

    font_family: string;
    font_size: string;
    font_weight: string;
    font_weight_heading: string;
    font_line_height: string;
}
