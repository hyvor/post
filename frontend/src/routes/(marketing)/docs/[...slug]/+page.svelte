<script lang="ts">
	import { Docs } from '@hyvor/design/marketing';
	let { data } = $props();
</script>

<svelte:head>
	<title>
		{data.page.name} | Hyvor Post
	</title>
	<link rel="canonical" href="https://post.hyvor.com/docs{data.slug ? '/' + data.slug : ''}" />
</svelte:head>

<Docs {...data} />
