<script lang="ts">
    import {HyvorBar, InternationalizationProvider} from "@hyvor/design/components";
    import en from '../../../../shared/locale/en-US.json';
    import fr from '../../../../shared/locale/fr-FR.json';
	import Nav from "./[id]/@components/Nav/Nav.svelte";

</script>

<svelte:head>
	<title>Console · Hyvor Post</title>
	<meta name="robots" content="noindex" />
</svelte:head>

<InternationalizationProvider
	languages={[
		{
			code: 'en-US',
			flag: '🇺🇸',
			name: 'English',
			region: 'United States',
			strings: en,
			default: true
		},
		{
			code: 'fr-FR',
			flag: '🇫🇷',
			name: 'Français',
			region: 'France',
			strings: fr
		}
	]}
>

<main>
    <HyvorBar product='blogs'/>
	
	<div class="main-inner">
		<Nav />
		<div class="content">
			<slot />
		</div>
	</div>
</main>

</InternationalizationProvider>

<style>
	main {
		display: flex;
		flex-direction: column;
		width: 100%;
		height: 100vh;
	}

	.main-inner {
		display: flex;
		flex: 1;
		width: 100%;
		height: 100%;
		min-height: 0;
	}

	.content {
		display: flex;
		flex-direction: column;
		flex: 1;
		width: 100%;
		height: 100%;
		min-width: 0;
	}

	@media (max-width: 992px) {
		main {
			display: block;
		}
		.main-inner {
			display: block;
		}
		.content {
			padding-bottom: 150px;
			height: initial;
			min-height: calc(100vh - 50px);
		}
	}
</style>
