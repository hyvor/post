<script lang="ts">
	import { DocsImage } from "@hyvor/design/marketing";
    import newImport from "./import.png";
    import fields from "./fields.png";
	import { Link, Table, TableRow } from "@hyvor/design/components";


</script>

<h1>Import</h1>

<p>
    You can import subscribers from a CSV file to Hyvor Post. This is useful when you want to
    migrate your subscribers from another service or when you have a list of subscribers that you
    want to add to Hyvor Post.
</p>

<DocsImage src={newImport} alt="Import" />

<h2 id="how-to">How to import</h2>

<ul>
    <li>Go to <strong>Console &rarr; Tools &rarr; Import</strong>.</li>
    <li>Upload your CSV file.</li>
    <li>Click <strong>Upload</strong>.</li>
    <li>Map columns in your CSV file into supported fields in Hyvor Post.</li>
    <DocsImage src={fields} alt="Fields" />
    <li>Click <strong>Import</strong> </li>
</ul>

<p>
    Your import will happen in the background. You can check the status of the import
    by refreshing the page.
</p>

<h2 id="fields">Fields</h2>

<p>
    You can find the supported fields in the table below.
</p>

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
<!--
Lists
Subscribed At
Subscribe IP
Subscriber Metadata
-->
    <TableRow>
        <div>Lists</div>
        <div>
            An array of list ids that subscriber should be subscribed to. If unmapped, user
            will be subscribed to all lists.
        </div>
        <div><code>number[]</code></div>
        <div>No</div>
    </TableRow>
    <TableRow>
        <div>Subscribed At</div>
        <div>
            Timestamp when the subscriber was subscribed. If unmapped, current time will be used.
        </div>
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
            You can map values to all <Link href="/subscriber-metadata">Subscriber Metadata</Link> which you have have defined in your
            console.
        </div>
        <div><code>string</code></div>
        <div>No</div>
    </TableRow>
</Table>

