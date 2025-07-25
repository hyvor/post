<script lang="ts">
    import {Divider, Modal, SplitControl, Textarea, TextInput} from "@hyvor/design/components";
    import type {Approval} from "../types";

    interface Props {
        show: boolean;
        approval: Approval;
        onApprove: (approval: Approval) => void;
        onReject: (approval: Approval) => void;
    }

    let { show = $bindable(), approval, onApprove, onReject }: Props = $props();


</script>

<Modal
    bind:show
    title="Approval Details"
    on:confirm={ () => onApprove(approval) }
    on:cancel={ () => onReject(approval) }
    footer={{
        confirm: {
            text: 'Approve'
        },
        cancel: {
            text: 'Reject',
        }
    }}
>
    <div class="content">
        <SplitControl label="Company name">
            <TextInput
                bind:value={approval.company_name}
                disabled
                block
            />
        </SplitControl>

        <SplitControl label="Country">
            <TextInput
                bind:value={approval.country}
                disabled
                block
            />
        </SplitControl>

        <SplitControl label="Website">
            <TextInput
                type="url"
                bind:value={approval.website}
                disabled
                block
            />
        </SplitControl>

        <SplitControl label="Social Links">
        <Textarea
            bind:value={approval.social_links}
            disabled
            block
        />
        </SplitControl>

        <SplitControl label="Type of Content">
            <TextInput
                bind:value={approval.type_of_content}
                disabled
                block
            />
        </SplitControl>

        <SplitControl label="Frequency">
            <TextInput
                bind:value={approval.frequency}
                disabled
                block
            />
        </SplitControl>

        <SplitControl label="Existing List">
        <Textarea
            bind:value={approval.existing_list}
            disabled
            block
        />
        </SplitControl>

        <SplitControl label="Sample">
            <TextInput
                bind:value={approval.sample}
                disabled
                block
            />
        </SplitControl>

        <SplitControl label="Why Post?">
        <Textarea
            bind:value={approval.why_post}
            disabled
            block
        />
        </SplitControl>

        <Divider color={"var(--accent-light)"} margin={10} />

        <SplitControl label="Public Note">
        <Textarea
            bind:value={approval.public_note}
            block
        />
        </SplitControl>

        <SplitControl label="Private Note">
        <Textarea
            bind:value={approval.private_note}
            block
        />
        </SplitControl>
    </div>
</Modal>

<style>
    .content {
        height: 70vh;
        overflow-y: auto;
    }
</style>
