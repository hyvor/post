<?php

namespace Api\Console\Template;

use App\Api\Console\Controller\TemplateController;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Template;
use App\Service\Template\TemplateService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\TemplateFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateController::class)]
#[CoversClass(TemplateService::class)]
#[CoversClass(TemplateObject::class)]
#[CoversClass(Template::class)]
class GetTemplateTest extends WebTestCase
{
    public function test_get_default_template(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/templates',
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertArrayHasKey('template', $json);
        $this->assertSame(
            '{% apply inline_css %}

<!DOCTYPE html>
<html lang="{{ lang }}" dir="{{ direction }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ subject }}</title>

    <style>
        body > * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px 5px;
            font-family: {{ font_family | raw }};
            font-weight: {{ font_weight }};
            line-height: {{ font_line_height }};
            background-color: {{ color_background }};
            color: {{ color_background_text }};
            font-size: {{ font_size }};
        }

        .content-box {
            width: 625px;
            max-width: 100%;
            margin: auto;
            background-color: {{ color_box }};
            color: {{ color_box_text }};
            border-radius: {{ box_radius }};
            box-shadow: {{ box_shadow }};
            border: {{ box_border }};
        }

        .brand-wrap {
            padding: 30px 35px 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand-wrap a {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .brand-wrap img {
            max-height: 26px;
            width: auto;
            padding-right: 6px;
        }

        .brand-wrap .name {
            font-weight: 600;
        }

        .content p {
            margin: 0 0 20px;
        }

        .content h1,
        .content h2,
        .content h3,
        .content h4,
        .content h5,
        .content h6 {
            margin: 0 0 20px;
            font-weight: {{ font_weight_heading }};
        }

        .content h1 {
            font-size: 1.75rem;
        }

        .content h2 {
            font-size: 1.75rem;
        }

        .content h3 {
            font-size: 1.5rem;
        }

        .content h4 {
            font-size: 1.25rem;
        }

        .content h5 {
            font-size: 1.125rem;
        }

        .content h6 {
            font-size: 1rem;
        }

        .content a {
            color: {{ color_accent }};
        }

        .content .button-wrap {
            text-align: center;
            margin: 0 0 20px;
        }

        .content a.button {
            display: inline-block;
            padding: 10px 20px;
            background-color: {{ color_accent }};
            color: {{ color_accent_text }};
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }

        .content :not(pre) code {
            background: rgba(135, 131, 120, 0.15);
            color: #a33a3a;
            border-radius: 3px;
            font-size: 0.85em;
            padding: 0.2em 0.4em;
            font-family: monospace;
        }

        .content pre code {
            display: block;
            font-family: \'Courier New\', Courier, monospace;
            font-size: 15px;
            line-height: 1.6;
            color: #4b3b2f;
            background-color: #fdf8f4;
            border: 1px solid #f0e1d2;
            border-radius: 10px;
            padding: 14px 18px;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 14px 0;
            box-shadow: 0 1px 3px rgba(120, 90, 70, 0.15);
        }

        .content blockquote {
            border-left: 4px solid;
            border-color: {{ color_accent }};
            margin: 0 0 20px;
            padding: 15px;
        }

        .content blockquote > *:last-child {
            margin-bottom: 0;
        }

        .content figure {
            margin: 0 0 20px;
        }

        .content figcaption {
            padding: 7px;
            font-size: 14px;
            text-align: center;
            margin-top: 16px;
        }

        .content figure img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: auto;
        }

        .content hr {
            margin: 0 0 20px;
            height: 1px;
            background-color: currentColor;
            opacity: 0.3;
        }

        .content div.callout {
            border-radius: 5px;
            margin: 0 0 20px;
        }

        .content div.callout > span {
            float: left;
            padding: 10px 12px;
        }

        .content div.callout > div {
            padding: 10px 10px 10px 0;
        }

        .content hr {
            margin: 0 0 20px;
            height: 1px;
            background-color: currentColor;
            opacity: 0.3;
        }

        .content ul, .content ol {
            margin: 0 0 20px 0;
        }

        .content li > * {
            margin: 5px 0;
        }

        .branding-tag {
            display: inline-block;
            font-size: 14px;
            opacity: 0.6;
            color: {{ color_background_text }};
            text-decoration: none;
        }

        /* RTL Support */
        [dir="rtl"] .content blockquote {
            border-left: none;
            border-right: 4px solid {{ color_accent }};
        }

        [dir="rtl"] .content div.callout > span {
            float: right;
        }

        [dir="rtl"] .content div.callout > div {
            padding: 10px 0 10px 10px;
        }

        [dir="rtl"] .brand-wrap img {
            padding-right: 0;
            padding-left: 6px;
        }
    </style>
</head>

<body>

<div class="content-box">
    <div class="brand-wrap">
        <a href="{{ brand_url }}" target="_blank">
            {% if brand_logo %}
                <img src="{{ brand_logo }}" alt="{{ brand_logo_alt }}">
            {% endif %}
            <span class="name">
                {{ name }}
            </span>
        </a>
    </div>
    <div class="content" style="
            padding: 10px 35px 20px;
        ">
        <h1>{{ subject }}</h1>
        {{ content | raw }}
    </div>
</div>

<div class="mail-footer" style="
        padding: 25px 35px;
        font-size: 14px;
        text-align: center;
        ">
    {{ address }}

    {% if unsubscribe_url %}
        <div>
            <a href="{{ unsubscribe_url }}" target="_blank" style="color: inherit;">{{ unsubscribe_text }}</a>
        </div>
    {% endif %}
</div>

{% if branding %}
    <div style="text-align:center;">
        <a class="branding-tag" target="_blank"
           href="https://post.hyvor.com?source=mail-branding&newsletter={{ subdomain }}">
            [<span style="vertical-align:middle">Sent privately via</span> <strong style="vertical-align:middle">Hyvor
                Post</strong> <img style="vertical-align:middle" src="https://post.hyvor.com/img/logo.png" width="14"/>]
        </a>
    </div>
{% endif %}

</body>

{% endapply %}
',
            $json['template']
        );
    }

    public function test_get_custom_template(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $template = TemplateFactory::createOne(
            [
                'newsletter' => $newsletter,
                'template' => 'MyCustomTemplate'
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/templates',
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame('MyCustomTemplate', $json['template']);
    }
}
