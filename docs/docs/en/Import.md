<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
	import imgImport from '../images/import.png';
	import imgImportFields from '../images/import-fields.png';
</script>

# Import

You can import subscribers from a CSV file to Hyvor Post. This is useful when you want to migrate your subscribers from another service or when you have a list of subscribers that you want to add to Hyvor Post.

<DocsImage src={imgImport} alt="Import" />

<h2 id="how-to">How to import</h2>

- Go to **Console &rarr; Tools &rarr; Import**.
- Upload your CSV file.
- Click **Upload**.
- Map columns in your CSV file into supported fields in Hyvor Post.
- Click **Import**.

<DocsImage src={imgImportFields} alt="Fields" />

Your import will happen in the background. You can check the status of the import by refreshing the page.

<h2 id="fields">Fields</h2>

You can find the supported fields in the table below.

<Table columns="1.5fr 3fr 1fr 1fr">
	<TableRow head>
		<div>Field</div>
		<div>Description</div>
		<div>Format</div>
		<div>Required</div>
	</TableRow>
	<TableRow>
		<div>Email</div>
		<div>The email address of the subscriber.</div>
		<div><code>string</code></div>
		<div>Yes</div>
	</TableRow>
	<TableRow>
		<div>Lists</div>
		<div>
			An array of list names that subscriber should be subscribed to. If unmapped, user will be
			subscribed to all lists.
		</div>
		<div><code>string[]</code></div>
		<div>No</div>
	</TableRow>
	<TableRow>
		<div>Subscribed At</div>
		<div>Timestamp when the subscriber was subscribed. If unmapped, current time will be used.</div>
		<div><code>string</code></div>
		<div>No</div>
	</TableRow>
	<TableRow>
		<div>Subscribe IP</div>
		<div>
			Subscribed IP address of the user. If unmapped, it will default to <code>NULL</code>.
		</div>
		<div><code>string</code></div>
		<div>No</div>
	</TableRow>
	<TableRow>
		<div>Subscriber Metadata</div>
		<div>
			You can map values to all <a href="/docs/api-console#subscriber-metadata">Subscriber Metadata</a>
			which you have defined in your console.
		</div>
		<div><code>string</code></div>
		<div>No</div>
	</TableRow>
</Table>
