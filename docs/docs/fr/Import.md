<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
	import imgImport from '../images/import.png';
	import imgImportFields from '../images/import-fields.png';
</script>

# Importation

Vous pouvez importer des abonnés dans Hyvor Post à partir d'un fichier CSV. C'est utile lorsque vous souhaitez migrer vos abonnés depuis un autre service ou lorsque vous avez une liste d'abonnés à ajouter à Hyvor Post.

<DocsImage src={imgImport} alt="Importation" />

<h2 id="how-to">Comment importer</h2>

- Allez dans **Console &rarr; Outils &rarr; Importation**.
- Téléversez votre fichier CSV.
- Cliquez sur **Téléverser**.
- Associez les colonnes de votre fichier CSV aux champs pris en charge par Hyvor Post.
- Cliquez sur **Importer**.

<DocsImage src={imgImportFields} alt="Champs" />

Votre importation s'effectue en arrière-plan. Vous pouvez vérifier son état en actualisant la page.

<h2 id="fields">Champs</h2>

Vous trouverez les champs pris en charge dans le tableau ci-dessous.

<Table columns="1.5fr 3fr 1fr 1fr">
	<TableRow head>
		<div>Champ</div>
		<div>Description</div>
		<div>Format</div>
		<div>Obligatoire</div>
	</TableRow>
	<TableRow>
		<div>E-mail</div>
		<div>L'adresse e-mail de l'abonné.</div>
		<div><code>string</code></div>
		<div>Oui</div>
	</TableRow>
	<TableRow>
		<div>Listes</div>
		<div>
			Un tableau de noms de listes auxquelles l'abonné doit être abonné. Si ce champ n'est pas
			associé, l'utilisateur sera abonné à toutes les listes.
		</div>
		<div><code>string[]</code></div>
		<div>Non</div>
	</TableRow>
	<TableRow>
		<div>Date d'abonnement</div>
		<div>Horodatage de l'abonnement. Si ce champ n'est pas associé, l'heure actuelle sera utilisée.</div>
		<div><code>string</code></div>
		<div>Non</div>
	</TableRow>
	<TableRow>
		<div>IP d'abonnement</div>
		<div>
			Adresse IP de l'utilisateur lors de l'abonnement. Si ce champ n'est pas associé, la valeur par défaut est <code>NULL</code>.
		</div>
		<div><code>string</code></div>
		<div>Non</div>
	</TableRow>
	<TableRow>
		<div>Métadonnées de l'abonné</div>
		<div>
			Vous pouvez associer des valeurs à toutes les
			<a href="/docs/api-console#subscriber-metadata">métadonnées d'abonné</a> que vous avez définies
			dans votre console.
		</div>
		<div><code>string</code></div>
		<div>Non</div>
	</TableRow>
</Table>
