<?php

namespace App\Tests\Api\Public\Template;

use App\Tests\Case\WebTestCase;

class TemplateWithTest extends WebTestCase
{

    public function test_get_template_with(): void
    {
        $template = <<<HTML
            <!DOCTYPE html>
            <html lang="{{ lang }}">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{{ subject }}</title>

                <style>
                    * {
                        box-sizing: border-box;
                    }
                </style>
            </head>

            <body style="
                margin: 0;
                padding: 20px;
                font-family: {{ font_family }};
                font-size: {{ font_size }};
                font-weight: {{ font_weight }};
                line-height: {{ font_line_height }};
                background-color: {{ color_accent }}05
            ">
            <div class="box" style="
                    width: 625px;
                    max-width: 100%;
                    margin: auto;
                    color: {{ font_color_on_box }};
                    background-color: {{ color_box_background  }};
                    border-radius: {{ color_box_radius  }};
                    box-shadow: {{ color_box_shadow }};
                    border: {{ color_box_border }};
                    --accent: {{ color_accent }};
                    ">
                <div style="
                    padding: 30px 35px 10px;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                ">
                    <a href="{{ brand_url }}" target="_blank"
                       style="
                       display:inline-flex;
                       align-items:center;
                       text-decoration: none;
                       color:inherit
                   ">
                        <img src="{{ logo }}" alt="{{ logo_alt }}" style="max-height: 26px; width: auto;">
                        <span class="name" style="font-weight: 600; padding-left: 6px;">
                            {{ brand }}
                        </span>
                    </a>
                </div>
                <div class="mail-body" style="
                    padding: 10px 35px 20px;
                ">
                    {{ content | raw }}
                </div>
            </div>
            <div class="mail-footer" style="
                padding: 25px 35px;
                font-size: 14px;
                text-align: center;
                color: {{ font_color_on_background }};
            ">
                {{ address }}

                <div>
                    <a href="{{ unsubscribe_url }}" target="_blank" style="color: inherit;">{{ unsubscribe_text }}</a>
                </div>
            </div>
            </body>

            HTML;

        $variables = '{
                  "lang": "en",
                  "subject": "",
                  "content": "",
                  "logo": "",
                  "logo_alt": "",
                  "brand": "",
                  "brand_url": "",
                  "address": "",
                  "unsubscribe_url": "",
                  "unsubscribe_text": "",
                  "color_accent": "#007bff",
                  "color_background": "#f8f9fa",
                  "color_box_background": "#f8f9fa",
                  "color_box_radius": "5px",
                  "color_box_shadow": "0 0 10px rgba(0, 0, 0, 0.1)",
                  "color_box_border": "1px solid #e9ecef",
                  "font_family": "Arial, sans-serif",
                  "font_size": "16px",
                  "font_weight": "normal",
                  "font_weight_heading": "bold",
                  "font_color_on_background": "#007bff",
                  "font_color_on_box": "#333333",
                  "font_line_height": "1.5"
                }';

        $response = $this->publicApi(
            'POST',
            '/template/with',
            [
                'template' => $template,
                'variables' => $variables,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $this->getJson($response);

    }
}
