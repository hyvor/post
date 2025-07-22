<script>
	import Content from './Content.svelte';
	import Preview from '../Preview.svelte';
</script>

<div class="split">
	<Content />
	<Preview />
</div>

<style>
	.split {
		display: flex;
		height: 100%;
		overflow: hidden;
	}
</style>
