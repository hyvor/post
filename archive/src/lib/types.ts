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

export interface Palette {
    text: string;
    accent: string;
    accent_text: string;
    input: string;
    input_text: string;
    input_box_shadow: string;
    input_border: string;
    border_radius: number;
}
