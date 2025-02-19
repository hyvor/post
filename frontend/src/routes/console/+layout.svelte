<script lang="ts">
    import {HyvorBar, InternationalizationProvider, Loader, toast} from "@hyvor/design/components";
    import en from '../../../../shared/locale/en-US.json';
    import fr from '../../../../shared/locale/fr-FR.json';
	import Nav from "./[id]/@components/Nav/Nav.svelte";
	import type { Project } from "./types";

	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import { projectListStore } from "./lib/stores/projectListStore";
	import { page } from "$app/stores";

	interface InitResponse {
		projects: Project[];
	}

	let isLoading = true;

	onMount(() => {

		/*consoleApi
			.post<InitResponse>({
				userApi: true,
				endpoint: 'projects',
				data: {
					'name': 'Project1'
				}
			})*/

		consoleApi
			.get<InitResponse>({
				userApi: true,
				endpoint: 'init',
			})
			.then((res) => {
				projectListStore.set(res.projects);
				isLoading = false;
			})
			.catch((err) => {
				if (err.code === 401) {
					const toPage = $page.url.searchParams.has('signup') ? 'signup' : 'login';
					location.href = `/api/auth/${toPage}?redirect=` + encodeURIComponent(location.href);
				} else {
					toast.error(err.message);
				}
			});
	})

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
	{#if isLoading}
		<div class="full-loader">
			<Loader size="large">

			</Loader>
		</div>
	{:else}
		<HyvorBar product='blogs'/>
		<div class="main-inner">
			<Nav />
			<div class="content">
				<slot />
			</div>
		</div>
	{/if}
</main>

</InternationalizationProvider>

<style>
	main {
		display: flex;
		flex-direction: column;
		width: 100%;
		height: 100vh;
	}

	.full-loader {
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
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
