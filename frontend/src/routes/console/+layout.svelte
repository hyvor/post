<script lang="ts">
	import {
		InternationalizationProvider,
		DarkProvider,
		Loader,
		toast
	} from '@hyvor/design/components';
	import {
		CloudContext,
		type CloudContextOrganization,
		type CloudContextUser,
		HyvorBar
	} from '@hyvor/design/cloud';
	import en from '../../../../shared/locale/en.json';
	import fr from '../../../../shared/locale/fr.json';
	import type { AppConfig, ApprovalStatus, NewsletterList, ResolvedLicense } from './types';
	import { onMount } from 'svelte';
	import consoleApi from './lib/consoleApi';
	import { page } from '$app/state';
	import {
		setAppConfig,
		getAppConfig,
		userApprovalStatusStore,
		authOrganizationStore,
		authUserStore,
		resolvedLicenseStore
	} from './lib/stores/consoleStore';
	import { setNewsletterStoreByNewsletterList } from './lib/stores/newsletterStore';
	import { userNewslettersStore } from './lib/stores/userNewslettersStore';
	import { goto } from '$app/navigation';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		config: AppConfig;
		user: CloudContextUser;
		organization: CloudContextOrganization;
		resolved_license: ResolvedLicense;
		newsletters: NewsletterList[];
		user_approval: ApprovalStatus;
	}

	let isLoading = $state(true);

	function startConsole(switchingOrg = false) {
		consoleApi
			.get<InitResponse>({
				userApi: true,
				endpoint: 'init'
			})
			.then((res) => {
				setAppConfig(res.config);

				authOrganizationStore.set(res.organization);
				authUserStore.set(res.user);
				resolvedLicenseStore.set(res.resolved_license);
				userNewslettersStore.set(res.newsletters);
				if (res.newsletters.length > 0) {
					setNewsletterStoreByNewsletterList(res.newsletters[0]);
				}

				userApprovalStatusStore.set(res.user_approval);

				if (switchingOrg && !page.url.pathname.startsWith('/console/new')) {
					goto('/console');
				}

				isLoading = false;
			})
			.catch((err) => {
				if (err.code === 401) {
					const toPage = page.url.searchParams.has('signup') ? 'signup' : 'login';
					const url = new URL(err.data[toPage + '_url'], location.origin);
					url.searchParams.set('redirect', location.href);
					location.href = url.toString();
				} else {
					toast.error(err.message);
				}
			});
	}

	onMount(startConsole);
</script>

<svelte:head>
	<title>Console | Hyvor Post</title>
	<meta name="robots" content="nofollow, noindex" />
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
			<CloudContext
				context={{
					component: 'post',
					deployment: 'cloud',
					instance: getAppConfig().hyvor.instance,
					user: $authUserStore,
					organization: $authOrganizationStore,
					license: $resolvedLicenseStore,
					callbacks: {
						onOrganizationSwitch: (switcher) => {
							isLoading = true;

							switcher
								.then(() => {
									startConsole(true);
								})
								.catch(() => {
									isLoading = false;
								});
						}
					}
				}}
				style="display:flex; flex-direction: column; width: 100%; height: 100vh"
			>
				<HyvorBar />
				{@render children?.()}
			</CloudContext>
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
