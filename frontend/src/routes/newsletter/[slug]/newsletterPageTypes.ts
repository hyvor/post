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