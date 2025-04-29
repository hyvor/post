
export interface Project {
    id: number;

    form: {
        title: string | null,
        description: string | null,
        footer_text: string | null,
        button_text: string | null,
        success_message: string | null,
    }
    
}

export interface List {
    id: number;
    created_at: number;
    name: string;
    description: string | null;
}