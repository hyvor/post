<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
	import imgFormCustomize from '../images/form-customize.png';
	import imgFormMultiDefault from '../images/form-multi-default.png';
	import imgFormListsFilter from '../images/form-lists-filter.png';
	import imgFormListsUnselected from '../images/form-lists-unselected.png';
	import imgFormListsHidden from '../images/form-lists-hidden.png';
</script>

# Formulaire d'inscription

Vous pouvez intégrer le formulaire d'inscription de Hyvor Post à votre site web pour permettre aux utilisateurs de s'abonner à votre newsletter. Le formulaire utilise le double opt-in par défaut, ce qui garantit que les abonnés confirment leur inscription par e-mail.

- [Intégrer le formulaire d'inscription](#embed)
- [Personnaliser le formulaire](#customize)
- [Personnaliser l'e-mail de confirmation](#confirmation-email)
- [Utiliser plusieurs listes](#multiple-lists)
- [Attributs du formulaire](#attributes)

<h2 id="embed">Intégrer le formulaire d'inscription</h2>

Ajoutez d'abord la balise script suivante dans le `<head>` de votre site web :

```html
<script src="https://post.hyvor.com/form/form.js" type="module" async></script>
```

Ajoutez ensuite la balise HTML suivante à l'endroit où vous souhaitez afficher le formulaire :

```html
<hyvor-post-form newsletter="{subdomain}"></hyvor-post-form>
```

Remplacez `{subdomain}` par le sous-domaine de votre newsletter. Vous le trouverez dans **Console &rarr; Paramètres &rarr; Newsletter &rarr; Sous-domaine de la newsletter**.

<h2 id="customize">Personnaliser le formulaire</h2>

Vous pouvez personnaliser le texte et l'apparence du formulaire dans **Console &rarr; Paramètres &rarr; Formulaire d'inscription**.

<DocsImage src={imgFormCustomize} alt="Personnaliser le formulaire" />

Cela inclut des options comme le titre, la description, le texte du bouton, les couleurs et l'interface. Vous pouvez également ajouter du CSS personnalisé pour aller plus loin. Notez que le CSS personnalisé est ajouté au formulaire, qui est un web component avec un shadow DOM : votre CSS n'affectera donc que le formulaire, et non le reste de votre site.

<h2 id="confirmation-email">Personnaliser l'e-mail de confirmation</h2>

Lorsqu'une personne s'inscrit, elle reçoit un e-mail lui demandant de confirmer son inscription. Vous pouvez personnaliser son sujet et son contenu dans **Console &rarr; Paramètres &rarr; Confirmation Email**. Cliquez sur **Edit Content** pour ouvrir l'éditeur, avec un aperçu de l'e-mail en direct.

Les variables suivantes sont disponibles dans le sujet et le contenu :

- `{{newsletter_name}}` - le nom de votre newsletter
- `{{confirm_url}}` - le lien de confirmation. Le contenu doit contenir un bouton ou un lien vers cette URL.

Cliquez sur **Reset to Default** pour revenir à l'e-mail par défaut.

<h2 id="multiple-lists">Utiliser plusieurs listes</h2>

Supposons que votre newsletter comporte deux listes : Weekly Updates et Product Announcements.

1. Par défaut, les deux listes sont affichées dans le formulaire d'inscription, et les utilisateurs peuvent choisir à quelle(s) liste(s) s'abonner.

```html
<hyvor-post-form newsletter="{subdomain}"></hyvor-post-form>
```

<DocsImage src={imgFormMultiDefault} alt="Formulaire avec plusieurs listes" width={400} />

2. Si vous souhaitez n'afficher que la liste Product Announcements, vous pouvez définir l'attribut `lists` sur « Product Announcements » (plusieurs listes peuvent être séparées par des virgules). Toute personne qui s'abonne via ce formulaire sera abonnée uniquement à la liste Product Announcements.

```html
<hyvor-post-form
	newsletter="{subdomain}"
	lists="Product Announcements"
></hyvor-post-form>
```

<DocsImage src={imgFormListsFilter} alt="Plusieurs listes : filtrage" width={400} />

3. Par défaut, toutes les listes sont sélectionnées dans le formulaire. Si vous souhaitez qu'une liste soit désélectionnée par défaut, définissez l'attribut `lists-default-unselected` sur le nom de la liste (là encore, plusieurs listes peuvent être séparées par des virgules).

```html
<hyvor-post-form
	newsletter="{subdomain}"
	lists-default-unselected="Product Announcements"
></hyvor-post-form>
```

<DocsImage src={imgFormListsUnselected} alt="Plusieurs listes : désélectionnées" width={400} />

4. Si vous souhaitez masquer la sélection des listes, définissez l'attribut `lists-hidden`. L'utilisateur sera abonné à toutes les listes par défaut. Pour l'abonner à des listes précises, utilisez l'attribut `lists`.

```html
<hyvor-post-form
	newsletter="{subdomain}"
	lists="Product Announcements"
	lists-hidden
></hyvor-post-form>
```

<DocsImage src={imgFormListsHidden} alt="Plusieurs listes : masquées" width={400} />

Dans l'exemple ci-dessus, l'utilisateur sera abonné à la liste Product Announcements sans voir la sélection des listes.

<h2 id="attributes">Attributs du formulaire</h2>

<Table columns="1fr 2fr">
	<TableRow head>
		<div>Attribut</div>
		<div>Valeur</div>
	</TableRow>
	<TableRow>
		<div><code>newsletter</code></div>
		<div>Le sous-domaine de votre newsletter (obligatoire)</div>
	</TableRow>
	<TableRow>
		<div><code>lists</code></div>
		<div>
			Une liste de noms de listes de la newsletter, séparés par des virgules, à afficher dans le
			formulaire. Si elle n'est pas définie, toutes les listes sont affichées (s'il y en a plusieurs).
		</div>
	</TableRow>
	<TableRow>
		<div><code>lists-default-unselected</code></div>
		<div>
			Une liste de noms de listes, séparés par des virgules, qui doivent être désélectionnées par
			défaut dans le formulaire. Si elle n'est pas définie, toutes les listes sont sélectionnées par
			défaut.
		</div>
	</TableRow>
	<TableRow>
		<div><code>lists-hidden</code></div>
		<div>
			Si défini, la sélection des listes est masquée et les utilisateurs sont abonnés à toutes les
			listes par défaut. Pour les abonner à des listes précises, utilisez l'attribut <code>lists</code>.
		</div>
	</TableRow>
	<TableRow>
		<div><code>colors</code></div>
		<div>
			<code>light</code>, <code>dark</code> ou <code>os</code>
		</div>
	</TableRow>
</Table>
