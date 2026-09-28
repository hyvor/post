<script lang="ts">
	import { Docs } from '@hyvor/design/marketing';
	let { data } = $props();
</script>

<svelte:head>
	<title>{data.page.name} - Docs | Hyvor Post</title>
</svelte:head>

<div class="docs">
	<Docs {...data} />
</div>

<style>
	.docs {
		margin-bottom: 80px;
	}
</style>
