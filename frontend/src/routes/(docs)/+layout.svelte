<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { Header, HeaderNavLink } from '@hyvor/design/marketing';
	import HeaderLanguageToggle, { buildLocalizedUrl } from './HeaderLanguageToggle.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconGithub from '@hyvor/icons/IconGithub';
	import { page } from '$app/state';

	// docs are also rendered at hyvor.com/post (the canonical location), synced from ../docs
	const CANONICAL_BASE = 'https://hyvor.com';

	const LANGUAGES = [
		{ code: 'en', flag: '🇬🇧', name: 'English' },
		{ code: 'fr', flag: '🇫🇷', name: 'Français' }
	];

	let { children } = $props();

	const lang = $derived((page.data.lang as string | undefined) ?? 'en');
	const langPrefix = $derived(lang === 'en' ? '' : `/${lang}`);

	const canonical = $derived.by(() => {
		const path = page.url.pathname.slice(langPrefix.length);
		return `${CANONICAL_BASE}${langPrefix}/post${path}`;
	});
</script>

<svelte:head>
	<link rel="canonical" href={canonical} />
</svelte:head>

<Header
	product="post"
	name="Hyvor Post"
	logo="/img/logo.png"
	href="{langPrefix}/docs"
	darkToggle={false}
	max
>
	{#snippet center()}
		<HeaderNavLink
			href="{langPrefix}/docs"
			active={page.url.pathname.startsWith(`${langPrefix}/docs`)}
		>
			Docs
		</HeaderNavLink>
		<HeaderNavLink
			href="{langPrefix}/hosting"
			active={page.url.pathname.startsWith(`${langPrefix}/hosting`)}
		>
			Hosting
		</HeaderNavLink>
		<HeaderNavLink href="https://github.com/hyvor/post" target="_blank">
			<IconGithub size={12} />
			Github
			<IconBoxArrowUpRight size={11} />
		</HeaderNavLink>
		<HeaderLanguageToggle
			languages={LANGUAGES}
			current={lang}
			href={(code) => buildLocalizedUrl(page.url.pathname, lang, code, 'en')}
		/>
	{/snippet}

	{#snippet end()}
		<Button href="/console" as="a">Go to Console</Button>
	{/snippet}
</Header>

{@render children?.()}
