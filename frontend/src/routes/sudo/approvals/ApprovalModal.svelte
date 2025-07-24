<script lang="ts">
    import {Modal, SplitControl, Textarea, TextInput} from "@hyvor/design/components";
    import type {Approval} from "../types";

    interface Props {
        show: boolean;
        approval: Approval;
        onApprove: (id: number) => void;
        onReject: (id: number) => void;
    }

    let { show = $bindable(), approval, onApprove, onReject }: Props = $props();


</script>

<Modal
    bind:show
    title="Approval Details"
    on:confirm={ () => onApprove(approval.id) }
    on:cancel={ () => onReject(approval.id) }
    footer={{
        confirm: {
            text: 'Approve'
        },
        cancel: {
            text: 'Reject',
        }
    }}
    autoClose={true}
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
    </div>
</Modal>

<style>
    .content {
        height: 70vh;
        overflow-y: auto;
    }
</style>
