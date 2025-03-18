<script>
	import IconSend from "@hyvor/icons/IconSend";
    import IconPlus from "@hyvor/icons/IconPlus";
	import { issueStore } from "../../lib/stores/projectStore";
	import CreateIssueButton from "./CreateIssueButton.svelte";
	import SingleBox from "../@components/content/SingleBox.svelte";
	import IssueRow from "./IssueRow.svelte";

</script>

<SingleBox>
    <div class="issues">
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
	}
	.issues-title {
		font-weight: 600;
		font-size: 18px;
		margin-bottom: 10px;
		padding: 8px;
		text-align: center;
	}
</style>