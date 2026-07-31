<script lang="ts">
	import {
		InternationalizationProvider,
		DarkProvider,
		Loader,
		toast
	} from '@hyvor/design/components';
	import { isEmbedded } from './lib/embedded';
	import {
		CloudContext,
		type CloudContextOrganization,
		type CloudContextUser,
		type ResolvedLicense,
		HyvorBar
	} from '@hyvor/design/cloud';
	import en from '../../../../shared/locale/en.json';
	import fr from '../../../../shared/locale/fr.json';
	import type { AppConfig, NewsletterList } from './types';
	import { onMount } from 'svelte';
	import consoleApi from './lib/consoleApi';
	import { page } from '$app/state';
	import {
		setAppConfig,
		getAppConfig,
		authOrganizationStore,
		authUserStore,
		resolvedLicenseStore
	} from './lib/stores/consoleStore';
	import { setNewsletterStoreByNewsletterList } from './lib/stores/newsletterStore';
	import { userNewslettersStore } from './lib/stores/userNewslettersStore';
	import { beforeNavigate, goto } from '$app/navigation';
	import { consoleUrlWithNewsletter } from './lib/consoleUrl';

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
	}

	let isLoading = $state(true);

	function startConsole(switchingOrg = false) {
		/**
		 * support for /console?newsletter_id=1234 to force a newsletter to be selected
		 * usually used with embedded mode
		 */
		const forceNewsletterId = page.url.searchParams.has('newsletter_id')
			? parseInt(page.url.searchParams.get('newsletter_id') ?? '0')
			: null;

		if (page.url.searchParams.has('embedded')) {
			$isEmbedded = true;
			document.body.style.backgroundColor = 'transparent';
		}

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

				if (forceNewsletterId) {
					const forcedNewsletterList = res.newsletters.find(
						(nl) => nl.newsletter.id === forceNewsletterId
					);
					if (!forcedNewsletterList) {
						toast.error('Newsletter not found or you do not have access to it.');
					} else {
						setNewsletterStoreByNewsletterList(forcedNewsletterList);
					}
				} else if (res.newsletters.length > 0) {
					setNewsletterStoreByNewsletterList(res.newsletters[0]);
				}

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

	beforeNavigate((nav) => {
		if ($isEmbedded) {
			// cannot change the newsletter, so open none-newsletter URLs in a new tab
			const newsletterUrl = consoleUrlWithNewsletter('').replace(/\/$/, '');

			if (
				nav.to?.url.origin !== location.origin ||
				!nav.to?.url.pathname.startsWith(newsletterUrl)
			) {
				nav.cancel();
				window.open(nav.to?.url.toString(), '_blank');
			}
		}
	});
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
	<main class:embedded={$isEmbedded}>
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
				{#if !$isEmbedded}
					<HyvorBar />
				{/if}
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

	main.embedded :global(#nav-wrap) {
		height: 100%;
	}
	main.embedded :global(#nav-wrap > .newsletter-nav) {
		flex: 1;
	}
	main.embedded :global(#nav-wrap > .newsletter-nav > .wrap) {
		height: 100%;
	}
	main.embedded :global(#nav-wrap > .newsletter-nav > .wrap) {
		height: 100%;
	}
</style>
