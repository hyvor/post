<script lang="ts">
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
	import {
		draftIssueEditingStore,
		draftPreviewKey,
		draftSendableSubscribersCountStore
	} from './draftStore';

	let html = $state('');
	let iframe: HTMLIFrameElement = $state({} as HTMLIFrameElement);
	let reloading = $state(false);

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

		previewIssue($draftIssueEditingStore.id)
			.then((res) => {
				html = res.html;
				resizeIframe();

				draftSendableSubscribersCountStore.set({
					loading: false,
					count: res.sendable_subscribers_count
				});
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				reloading = false;
			});
	}

	let previewUpdateTimeout: ReturnType<typeof setTimeout>;

	draftPreviewKey.subscribe((key) => {
		if (previewUpdateTimeout) {
			clearTimeout(previewUpdateTimeout);
		}
		previewUpdateTimeout = setTimeout(fetchPreview, 1000);

		return () => previewUpdateTimeout && clearTimeout(previewUpdateTimeout);
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
	{#snippet caption()}
		<Caption>
			Settings:&nbsp;&nbsp;<Link
				target="_blank"
				href={consoleUrlWithNewsletter('/settings/notifications')}>Email branding</Link
			>&nbsp;&nbsp;<Link
				target="_blank"
				href={consoleUrlWithNewsletter('/settings/appearance')}>Styles & colors</Link
			>
		</Caption>
	{/snippet}
	<div class="preview">
		<iframe
			srcdoc={html}
			title="Issue preview"
			frameborder="0"
			scrolling="no"
			height={600}
			width="100%"
			bind:this={iframe}
			onload={resizeIframe}
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
