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
    import type {AppConfig, ApprovalStatus, NewsletterList} from './types';

	import { onMount } from 'svelte';
	import consoleApi from './lib/consoleApi';
	import { page } from '$app/stores';
    import {setAppConfig, getAppConfig, approvalStore, userApprovalStatusStore} from './lib/stores/consoleStore';
	import { newsletterRoleStore, newsletterStore } from './lib/stores/newsletterStore';
	import { userNewslettersStore } from './lib/stores/userNewslettersStore';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		config: AppConfig;
		newsletters: NewsletterList[];
        user_approval: ApprovalStatus;
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

				userNewslettersStore.set(res.newsletters);
				if (res.newsletters.length != 0) {
					newsletterStore.set(res.newsletters[0].newsletter); // Set the first newsletter as the active newsletter
					newsletterRoleStore.set(res.newsletters[0].role);
				}

                userApprovalStatusStore.set(res.user_approval);

				isLoading = false;
			})
			.catch((err) => {
				if (err.code === 401) {
					const toPage = $page.url.searchParams.has('signup') ? err.data['signup_url'] : err.data['login_url'];
					location.href = toPage;
					// const toPage = $page.url.searchParams.has('signup') ? 'signup' : 'login';
					// location.href =
					// 	`/api/auth/${toPage}?redirect=` + encodeURIComponent(location.href);
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
			strings: en,
			default: true
		},
		{
			code: 'fr',
			flag: '🇫🇷',
			name: 'Français',
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
