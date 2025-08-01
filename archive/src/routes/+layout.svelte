<script lang="ts">
	import { onMount, type Snippet } from 'svelte';
	import { initNewsletter } from '$lib/actions/archiveActions';
	import { page } from '$app/state';
	import { subdomainStore, issuesStore, newsletterStore } from '$lib/archiveStore';
	import { Loader, toast } from '@hyvor/design/components';
	import type { Palette } from '$lib/types';
    import type {LayoutProps} from "../../.svelte-kit/types/src/routes/$types";

    let {data, children}: LayoutProps = $props();

	// interface Props {
	// 	children: Snippet;
	// }
    //
	// let { children }: Props = $props();

	let loading = $state(true);
	let lightPalette = $state({} as Palette);
	let darkPalette = $state({} as Palette);

	onMount(() => {
        subdomainStore.set(data.subdomain)
        initNewsletter($subdomainStore)
			.then((res) => {
				newsletterStore.set(res.newsletter);
				issuesStore.set(res.issues);
				lightPalette = res.palette_light;
				darkPalette = res.palette_dark;
				loading = false;
			})
			.catch((err) => {
				toast.error(err.message);
			});
	});

</script>

{#if loading}
	<div class="loader-wrap">
		<Loader full size="large" />
	</div>
{:else}
	<div
	class="archive-wrap"
	style="
		--hp-text-light: {lightPalette.text};
		--hp-accent-light: {lightPalette.accent};
		--hp-accent-text-light: {lightPalette.accent_text};
		--hp-input-light: {lightPalette.input};
		--hp-input-text-light: {lightPalette.input_text};
		--hp-input-box-shadow-light: {lightPalette.input_box_shadow};
		--hp-input-border-light: {lightPalette.input_border};
		--hp-border-radius: {lightPalette.border_radius}px;

		--hp-text-dark: {darkPalette.text};
		--hp-accent-dark: {darkPalette.accent};
		--hp-accent-text-dark: {darkPalette.accent_text};
		--hp-input-dark: {darkPalette.input};
		--hp-input-text-dark: {darkPalette.input_text};
		--hp-input-box-shadow-dark: {darkPalette.input_box_shadow};
		--hp-input-border-dark: {darkPalette.input_border};
	"
	>
		{@render children?.()}
	</div>
{/if}

<style>
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style>
