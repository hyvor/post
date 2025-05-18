<script lang="ts">
	import {
		HyvorBar,
		InternationalizationProvider,
		DarkProvider,
		Loader,
		toast
	} from '@hyvor/design/components';
	import en from '../../../../shared/locale/en.json';
	import fr from '../../../../shared/locale/fr.json';
	import type { AppConfig, Project } from './types';

	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import { page } from "$app/stores";
	import { setAppConfig, getAppConfig } from './lib/stores/consoleStore';
	import { projectRoleStore, projectStore } from "./lib/stores/projectStore";
	import { userProjectsStore } from "./lib/stores/userProjectsStore";
	import ProjectList from "./@components/Nav/ProjectList.svelte";

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		config: AppConfig
		projects: ProjectList[]
	}

	let isLoading = $state(true);

	onMount(() => {
		consoleApi
			.get<InitResponse>({
				userApi: true,
				endpoint: 'init'
			})
			.then((res) => {
				setAppConfig(res.config);

				userProjectsStore.set(res.projects);
				if (res.projects.length != 0) {
					projectStore.set(res.projects[0]); // Set the first project as the active project
					projectRoleStore.set(res.projects[0].role);
				}
				isLoading = false;
			})
			.catch((err) => {
				if (err.code === 401) {
					const toPage = $page.url.searchParams.has('signup') ? 'signup' : 'login';
					location.href =
						`/api/auth/${toPage}?redirect=` + encodeURIComponent(location.href);
				} else {
					toast.error(err.message);
				}
			});
	});
</script>

<svelte:head>
	<title>Console · Hyvor Post</title>
	<meta name="robots" content="noindex" />
</svelte:head>

<InternationalizationProvider
	languages={[
		{
			code: 'en',
			flag: '🇬🇧',
			name: 'English',
			region: 'United Kindgom',
			strings: en,
			default: true
		},
		{
			code: 'fr',
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
				<Loader size="large"></Loader>
			</div>
		{:else}
			<HyvorBar product="post" instance={getAppConfig().hyvor.instance} />
			{@render children?.()}
		{/if}
	</main>
</InternationalizationProvider>

<DarkProvider></DarkProvider>

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
