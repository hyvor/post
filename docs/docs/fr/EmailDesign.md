# Design des e-mails

Lorsque vous envoyez un numéro à vos abonnés, Hyvor Post « génère » ce numéro à l'aide d'un modèle. Par défaut, Hyvor Post utilise un modèle d'e-mail par défaut. Son design est simple et peut être adapté à vos besoins dans **Console &rarr; Paramètres &rarr; Modèle d'e-mail**.

<h2 id="custom">Modèles personnalisés</h2>

Pour la plupart des utilisateurs, les options de personnalisation de base proposées dans les paramètres suffisent. Cependant, si vous souhaitez créer un modèle d'e-mail entièrement personnalisé, vous pouvez créer un nouveau modèle personnalisé.

Nous utilisons le moteur de templates <a href="https://twig.symfony.com/" target="_blank">Twig</a> pour les modèles. Si vous connaissez le HTML, vous pouvez facilement créer un modèle personnalisé.

<h2 id="variables">Variables</h2>

Les variables suivantes sont transmises au modèle :

```json
{
    // code de langue à utiliser dans la balise <html>
    "lang": "en",

    // objet de l'e-mail à utiliser dans la balise <title>
    "subject": "Most popular blog posts of the week",

    // contenu au format HTML
    "content": "<p>...</p>",

    // en-tête
    "logo": "https://example.com/logo.png",
    "logo_alt": "Hyvor Post",
    "brand": "Hyvor Post",
    "brand_url": "https://example.com",

    // pied de page
    "address": "10 Rue de Penthièvre, 75008 Paris, France",
    "unsubscribe_url": "https://example.com/unsubscribe",
    "unsubscribe_text": "Unsubscribe",

    // couleurs au format HEX
    "color_accent": "#007bff",
    "color_background": "#f8f9fa",
    "color_text": "#000000",
    "color_box_background": "#ffffff",
    "color_box_radius": "5px",
    "color_box_shadow": "0 0 10px rgba(0, 0, 0, 0.1)",
    "color_box_border": "1px solid #e9ecef",

    // listes du numéro en cours
    "lists": ["Physics", "Mathematics"]
}
```

<h3 id="example">Exemple</h3>

Voici un exemple très simple d'utilisation de ces variables dans un modèle :

```twig
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
```

Notez que toutes les variables sont échappées par défaut. Vous devez donc utiliser `{{ content | raw }}` pour afficher le contenu en HTML. Consultez <a href="https://twig.symfony.com/doc/3.x/templates.html" target="_blank">Twig for Template Designers</a> pour plus d'informations.

<h3 id="list-based-customizations">Personnalisations selon les listes</h3>

Vous pouvez parfois vouloir personnaliser le modèle d'e-mail en fonction des listes du numéro en cours. La fonction `has_list()` sert à cela. Dans l'exemple suivant, une citation est ajoutée si le numéro est envoyé à la liste « Physics ».

```twig
...
<body>
    {{ content | raw }}

    {% if has_list('Physics') %}
        <blockquote>
            "Imagination is more important than knowledge."
        </blockquote>
    {% endif %}
</body>
```
