
export interface Project {
    id: number;

    form: {
        width: number | null,
        custom_css: string | null,
        title: string | null,
        description: string | null,
        footer_text: string | null,
        button_text: string | null,
        success_message: string | null,
    },

    palette_light: Palette,
    palette_dark: Palette,

}

export interface Palette {
    text: string,
    accent: string,
    accent_text: string,
    input: string;
    input_text: string;
}

export interface List {
    id: number;
    created_at: number;
    name: string;
    description: string | null;
}