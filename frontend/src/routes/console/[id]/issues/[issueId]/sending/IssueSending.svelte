<script lang="ts">
	import { onMount } from "svelte";
	import type { Issue } from "../../../../types";
	import IconSend from "@hyvor/icons/IconSend";
	import { getIssueProgress } from "../../../../lib/actions/issueActions";

	export let issue: Issue;
    export let complete: () => void;

	let progress = 2;
	let currentSend = 0;
	let total = 0;

	let updating = false;
	let autoFetch = true;


	function updateProgress() {
		updating = true;
		getIssueProgress(issue.id)
			.then((res) => {
				progress = Math.max(2, res.progress);
				currentSend = res.sent;
				total = res.total;

				if (progress === 100) {
					autoFetch = false;
					complete();
				}
			})
			.catch((e) => {
				console.error(e);
			})
			.finally(() => {
				updating = false;
			});
	}

	onMount(function () {
		updateProgress();

		setInterval(function () {
			if (autoFetch && !updating) {
				updateProgress();
			}
		}, 3000);
	});
</script>

<div class="wrap">
	<div class="inner">
		<div class="icon">
			<IconSend size={80} />
		</div>
		<div class="title">Your newsletter is being sent</div>
		<div class="progress-track">
			<div class="progress-bar" style:width={progress + '%'}></div>
		</div>
		{#if total > 0}
			<div class="counts">
				<span class="current">{currentSend}</span>/<span class="total">{total}</span>
			</div>
		{/if}
	</div>
</div>

<style>
	.wrap {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.inner {
		text-align: center;
		width: 350px;
	}
	.title {
		margin-top: 20px;
		font-size: 20px;
	}

	.progress-track {
		margin: 40px 0 25px;
		width: 100%;
		height: 15px;
		background-color: var(--accent-light);
		border-radius: 20px;
		overflow: hidden;
		position: relative;
	}
	.progress-bar {
		height: 100%;
		background-color: var(--accent);
		transition: 0.3s width;
	}
	.counts {
		font-size: 18px;
		color: var(--text-light);
	}
	.counts .current {
		color: var(--text);
	}
</style>
