<script lang="ts">
	import { Loader, Validation } from '@hyvor/design/components';
	import { fade } from 'svelte/transition';
	import { previewConfirmationEmail } from '../../../../lib/actions/newsletterActions';
	import { getAppConfig } from '../../../../lib/stores/consoleStore';

	interface Props {
		// null means the default template, same as the editing store
		subject: string | null;
		content: string | null;
	}

	let { subject, content }: Props = $props();

	const defaults = getAppConfig().newsletter_defaults;

	let html = $state('');
	let previewSubject = $state('');
	let iframe: HTMLIFrameElement | undefined = $state();
	let loading = $state(false);
	let error: string | null = $state(null);

	function resizeIframe() {
		if (!iframe?.contentWindow) return;
		iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
	}

	function fetchPreview(subject: string | null, content: string | null) {
		loading = true;
		// The preview API treats null as "use the saved email". Here null means the unsaved default.
		previewConfirmationEmail(
			subject ?? defaults.CONFIRMATION_EMAIL_SUBJECT,
			content ?? defaults.CONFIRMATION_EMAIL_CONTENT
		)
			.then((res) => {
				html = res.html;
				previewSubject = res.subject;
				error = null;
			})
			.catch((e) => {
				error = e.message;
			})
			.finally(() => {
				loading = false;
			});
	}

	$effect(() => {
		const s = subject;
		const c = content;
		const timeout = setTimeout(() => fetchPreview(s, c), 800);
		return () => clearTimeout(timeout);
	});
</script>

<div class="preview">
	{#if error}
		<div class="error-container">
			<Validation state="error">{error}</Validation>
		</div>
	{:else if previewSubject}
		<div class="subject"><span>Subject:</span> {previewSubject}</div>
	{/if}

	<iframe
		srcdoc={html}
		title="Confirmation email preview"
		frameborder="0"
		scrolling="no"
		width="100%"
		bind:this={iframe}
		onload={resizeIframe}
		class:hidden={!!error}
	></iframe>

	{#if loading}
		<div class="loader" transition:fade>
			<Loader size="large" colorTrack="transparent" />
		</div>
	{/if}
</div>

<style>
	.preview {
		position: relative;
		min-height: 300px;
	}

	.subject {
		padding: 12px 20px;
		border-bottom: 1px solid var(--border);
		font-size: 14px;
	}

	.subject span {
		color: var(--text-light);
	}

	.error-container {
		padding: 20px;
	}

	iframe {
		display: block;
		min-height: 300px;
	}

	iframe.hidden {
		display: none;
	}

	.loader {
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		z-index: 2;
		display: flex;
		justify-content: center;
		align-items: center;
		background-color: rgba(0, 0, 0, 0.04);
	}
</style>
