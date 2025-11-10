<script lang="ts">
	import { InternationalizationProvider } from '@hyvor/design/components';
	import Footer from './Footer.svelte';
	import Header from './Header.svelte';
	import { MARKETING_LANGUAGES } from './locale';
</script>

<InternationalizationProvider languages={MARKETING_LANGUAGES}>
	<Header />
	<slot />
	<div class="footer-wrap">
		<Footer />
	</div>
</InternationalizationProvider>

<style>
	.footer-wrap {
		margin-top: 100px;
		background-color: var(--background, var(--accent-lightest));
	}
</style>
