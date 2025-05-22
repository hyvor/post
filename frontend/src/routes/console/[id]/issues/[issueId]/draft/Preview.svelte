<script lang="ts">
	import { onDestroy, onMount, tick } from 'svelte';
	import {
		Caption,
		IconButton,
		Tooltip,
		Label,
		Link,
		SplitControl,
		Loader,
		toast
	} from '@hyvor/design/components';
	import { fade } from 'svelte/transition';
	import { previewIssue } from '../../../../lib/actions/issueActions';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import { consoleUrlWithNewsletter } from '../../../../lib/consoleUrl';
	import { contentUpdateId } from '../issueStore';

	export let id: number;

	let html = '';
	let iframe: HTMLIFrameElement;
	let reloading = false;
	let unsubscribe: () => void;

	function resizeIframe() {
		if (!iframe) return;
		iframe.style.height = iframe.contentWindow!.document.body.scrollHeight + 'px';
	}

	function refresh() {
		reloading = true;
		fetchPreview();
	}

	function fetchPreview() {
		// Don't fetch preview if the component is destroyed or issue is deleted
		if (!iframe) return;

		previewIssue(id)
			.then((res) => {
				html = res.html;
				resizeIframe();
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				reloading = false;
			});
	}

	let contentUpdateTimeout: ReturnType<typeof setTimeout>;

	function listenToUpdates() {
		unsubscribe = contentUpdateId.subscribe(() => {
			if (contentUpdateTimeout) {
				clearTimeout(contentUpdateTimeout);
			}
			contentUpdateTimeout = setTimeout(() => {
				fetchPreview();
			}, 5000);
		});
	}

	onMount(() => {
		fetchPreview();
		listenToUpdates();
	});

	onDestroy(() => {
		if (contentUpdateTimeout) {
			clearTimeout(contentUpdateTimeout);
		}
		if (unsubscribe) {
			unsubscribe();
		}
	});
</script>

<SplitControl column>
	<Label>
		Preview
		<Tooltip text="Refresh preview">
			<IconButton size="small" color="input" on:click={refresh} disabled={reloading}>
				<IconArrowClockwise size={12} />
			</IconButton>
		</Tooltip>
	</Label>
	<Caption slot="caption">
		Settings:&nbsp;&nbsp;<Link
			target="_blank"
			href={consoleUrlWithNewsletter('/settings/notifications')}>Email branding</Link
		>&nbsp;&nbsp;<Link target="_blank" href={consoleUrlWithNewsletter('/settings/appearance')}
			>Styles & colors</Link
		>
	</Caption>
	<div class="preview">
		<iframe
			srcdoc={html}
			title="Issue preview"
			frameborder="0"
			scrolling="no"
			height={600}
			width="100%"
			bind:this={iframe}
			on:load={resizeIframe}
		></iframe>

		{#if reloading}
			<div class="loader" transition:fade>
				<Loader size="large" />
			</div>
		{/if}
	</div>
</SplitControl>

<style>
	.preview {
		/* padding: 40px 20px;
		background-color: #fafafa; */
		border-radius: 20px;
		max-height: 700px;
		overflow: auto;
		position: relative;
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
		background-color: rgba(0, 0, 0, 0.1);
	}
</style>
