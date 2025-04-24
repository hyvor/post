<script>
	import { CodeBlock } from '@hyvor/design/components';
</script>

<h1>Email Design</h1>

<p>
	When you send an issue to your subscribers, Hyvor Post "renders" the issue using a template. By
	default, Hyvor Post uses a default email template. Its design is simple and can be customized
	for your needs at <strong>Console &rarr; Settings &rarr; Email Template</strong>.
</p>

<h2 id="custom">Custom Templates</h2>

<p>
	For most users, the basic customization options provided in the settings are enough. However, if
	you want to create a completely custom email template, you can do so by creating a new custom
	template.
</p>

<p>
	We use <a href="https://twig.symfony.com/" target="_blank">Twig</a> templating engine for templates.
	If you are familiar with HTML, you can easily create a custom template.
</p>

<h2 id="variables">Variables</h2>

<p>The following variables are passed to the template:</p>

<CodeBlock
	language="json"
	code={`
    {
        // language code to be used in <html> tag
        "lang": "en",

        // subject of the email to be used in <title> tag
        "subject": "Most popular blog posts of the week",

        // content in HTML format
        "content": "<p>...</p>",

        // header
        "logo": "https://example.com/logo.png",
        "logo_alt": "Hyvor Post",
        "brand": "Hyvor Post",
        "brand_url": "https://example.com",

        // footer
        "address": "10 Rue de Penthièvre, 75008 Paris, France",
        "unsubscribe_url": "https://example.com/unsubscribe",
        "unsubscribe_text": "Unsubscribe",

        // colors in HEX format
        "color_accent": "#007bff",
        "color_background": "#f8f9fa",
        "color_text": "#000000",
        "color_box_background": "#ffffff",
        "color_box_radius": "5px",
        "color_box_shadow": "0 0 10px rgba(0, 0, 0, 0.1)",
        "color_box_border": "1px solid #e9ecef",

        // lists of the current issue
        "lists": ["Physics", "Mathematics"}]
    }
`}
/>

<h3 id="example">Example</h3>

<p>Here is a very basic example of how to use these variables in a template:</p>

<CodeBlock
	code={`
<!DOCTYPE html>
<html lang="{{ lang }}">
<head>
    <meta charset="UTF-8">
    <title>{{ subject }}</title>
</head>
<body>
    {{ content | raw }}
</body>
</html>
`}
/>

<p>
	Note that all variables are escapred by default. Hence, you need to use <code
		>{`{{ content | raw }}`}</code
	>
	to render the content as HTML. See
	<a href="https://twig.symfony.com/doc/3.x/templates.html" target="_blank"
		>Twig for Template Designers</a
	>
	for more information.
</p>

<h3 id="list-based-customizations">List-based customizations</h3>

<p>
	Sometimes, you may want to customize the email template based on the lists of the current issue.
	The <code>has_list()</code> function can be used for this purpose. In the following example, a quote
	is added if the issue is sent to the "Physics" list.
</p>

<CodeBlock
	code={`
...
<body>
    {{ content | raw }}

    {% if has_list('Physics') %}
        <blockquote>
            "Imagination is more important than knowledge."
        </blockquote>
    {% endif %}
</body>
`}
/>
