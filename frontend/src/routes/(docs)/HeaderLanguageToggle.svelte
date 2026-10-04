<script lang="ts" module>
	export interface LanguageOption {
		code: string;
		flag: string;
		name: string;
	}

	/**
	 * Rewrites `path` from `currentLang` to `targetLang`, where the default
	 * language is served without a prefix and others under `/{code}`.
	 */
	export function buildLocalizedUrl(
		path: string,
		currentLang: string,
		targetLang: string,
		defaultLang: string
	): string {
		let basePath = path;

		if (currentLang !== defaultLang) {
			const prefix = `/${currentLang}`;
			if (basePath === prefix) {
				basePath = '/';
			} else if (basePath.startsWith(`${prefix}/`)) {
				basePath = basePath.slice(prefix.length);
			}
		}

		if (targetLang === defaultLang) {
			return basePath;
		}

		return basePath === '/' ? `/${targetLang}` : `/${targetLang}${basePath}`;
	}
</script>

<script lang="ts">
	import { Dropdown } from '@hyvor/design/components';
	import { HeaderNavLink } from '@hyvor/design/marketing';

	interface Props {
		languages: LanguageOption[];
		current: string;
		href: (code: string) => string;
		label?: string;
	}

	let { languages, current, href, label = 'Change language' }: Props = $props();

	const currentLanguage = $derived(languages.find((l) => l.code === current) ?? languages[0]);

	let show = $state(false);
</script>

{#if currentLanguage}
	<div class="header-language-toggle">
		<Dropdown bind:show align="center" position="bottom" contentPadding={8}>
			{#snippet trigger()}
				<HeaderNavLink aria-label={label} aria-expanded={show}>
					<span class="flag">{currentLanguage.flag}</span>
				</HeaderNavLink>
			{/snippet}
			{#snippet content()}
				<div class="menu">
					{#each languages as language (language.code)}
						<HeaderNavLink
							href={href(language.code)}
							active={language.code === current}
							onclick={() => (show = false)}
							menu
						>
							<span class="lang-row">
								<span class="flag">{language.flag}</span>
								<span class="name">{language.name}</span>
								<span class="code">{language.code.toUpperCase()}</span>
							</span>
						</HeaderNavLink>
					{/each}
				</div>
			{/snippet}
		</Dropdown>
	</div>
{/if}

<style>
	.header-language-toggle {
		position: relative;
		display: inline-flex;
		margin-left: 12px;
		padding-left: 12px;
	}

	.header-language-toggle::before {
		content: '';
		position: absolute;
		left: 0;
		top: 50%;
		transform: translateY(-50%);
		width: 1px;
		height: 16px;
		background: var(--border);
	}

	.flag {
		font-size: 15px;
		line-height: 1;
	}

	.menu {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	.lang-row {
		display: flex;
		align-items: center;
		gap: 8px;
		width: 100%;
	}

	.code {
		margin-left: auto;
		padding-left: 8px;
		font-size: 11px;
		font-weight: 600;
		color: var(--text-light);
	}
</style>
