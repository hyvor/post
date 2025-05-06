<script lang="ts">
    import {HyvorBar, InternationalizationProvider, Loader, toast} from "@hyvor/design/components";
    import en from '../../../../shared/locale/en-US.json';
    import fr from '../../../../shared/locale/fr-FR.json';
	import Nav from "./@components/Nav/Nav.svelte";
	import type { AppConfig, Project } from "./types";

	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import { userProjectAdminStore, userProjectsOwnerStore } from "./lib/stores/userProjectsStore";
	import { page } from "$app/stores";
	import { appConfig } from "./lib/stores/consoleStore";
	import { projectStore } from "./lib/stores/projectStore";
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		config: AppConfig
		projects_owner: Project[];
		projects_admin: Project[];
	}

	let isLoading = $state(true);;

	onMount(() => {
		consoleApi
			.get<InitResponse>({
				userApi: true,
				endpoint: 'init',
			})
			.then((res) => {
				appConfig.set(res.config)

				userProjectsOwnerStore.set(res.projects_owner);
				userProjectAdminStore.set(res.projects_admin);
				projectStore.set(res.projects_owner[0]); // Set the first project as the active project
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
		{@render children?.()}
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
</style>
