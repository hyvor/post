<script>
	import IconSend from "@hyvor/icons/IconSend";
    import IconPlus from "@hyvor/icons/IconPlus";
	import { issueStore } from "../../lib/stores/projectStore";
	import CreateIssueButton from "./CreateIssueButton.svelte";
	import SingleBox from "../@components/content/SingleBox.svelte";
	import IssueRow from "./IssueRow.svelte";
	import { onMount } from "svelte";
	import { getIssues } from "../../lib/actions/issueActions";
	import { LoadButton, Loader } from "@hyvor/design/components";

	let loading = true;
	let hasMore = true;
	let loadingMore = false;
	const ISSUES_PER_PAGE = 50;

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getIssues(ISSUES_PER_PAGE, more ? $issueStore.length : 0)
			.then((data) => {
				$issueStore = more ? [...$issueStore, ...data] : data;
				hasMore = data.length === ISSUES_PER_PAGE;
			})
			.catch((e) => {

			})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	onMount(() => {
		load();
	});

</script>

<SingleBox>
    <div class="issues">
		{#if loading}
			<Loader full />
		{:else}
			{#if $issueStore.length}
				<div class="issues-title">
					Sent Issues
					<CreateIssueButton 
						text="New"
						icon={IconPlus}
						size="small" 
					/>
				</div>
				{#each $issueStore as issue}
					<IssueRow {issue} />
				{/each}
				<LoadButton
					text={"Load more"}
					loading={loadingMore}
					show={hasMore}
					on:click={() => load(true)}
				/>
			{:else}
				<div class="create-first">
					<IconSend size={60} />
					<div class="message">
						No issues sent yet
					</div>
					<CreateIssueButton
						size="large"
						text="Create your first issue"
						icon={IconSend}
					/>
				</div>
			{/if}
		{/if}
    </div>
</SingleBox>

<style>
	.create-first {
		padding: 100px;
		text-align: center;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}
	.create-first .message {
		margin-top: 20px;
		margin-bottom: 20px;
		color: var(--text-light);
	}
	.issues {
		margin-top: 15px;
		padding: 20px;
	}
	.issues-title {
		font-weight: 600;
		font-size: 18px;
		margin-bottom: 10px;
		padding: 8px;
		text-align: center;
	}
</style>