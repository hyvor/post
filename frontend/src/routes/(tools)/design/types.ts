export interface TemplateVariables {
    color_accent: string;
    color_background: string;
    color_box_background: string;
    color_box_radius: string;
    color_box_shadow: string;
    color_box_border: string;
    font_family: string;
    font_size: string;
    font_weight: string;
    font_weight_heading: string;
    font_color_on_background: string;
    font_color_on_box: string;
    font_line_height: string;
}

export interface TemplateResponse {
    template: string;
    variables: TemplateVariables;
}

export interface TemplateTestClass {
    template: string,
    variables: string;
}