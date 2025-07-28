<script lang="ts">
    import { Button, Link, Tag } from "@hyvor/design/components";
    import type {Approval} from "../types";
    import ApprovalStatusTag from "../../console/@components/Nav/ApprovalStatusTag.svelte";
    import FriendlyDate from "../../console/@components/utils/FriendlyDate.svelte";
    import { configStore } from "../lib/stores/sudoStore";
    interface Props {
        approval: Approval;
        handleSelect: (approval: Approval) => void;
    }

    let { approval, handleSelect }: Props = $props();
</script>

<button class="row" onclick={ () => handleSelect(approval) }>

    <div class="company">
        {approval.company_name}
    </div>

    <div class="country">
        {approval.country}
    </div>

    <div class="status">
        <ApprovalStatusTag
            status={approval.status}
            size="medium"
            iconSize={12}
        />
    </div>

    <div class="user">
        <Button
            as="a"
            href={`${$configStore.hyvor.instance}/sudo/core/users/${approval.user_id}`}
            target="_blank"
            size="small"
            color="input"
            >
            User ID: {approval.user_id}
        </Button>
    </div>

    <div class="created-at">
        <FriendlyDate time={approval.created_at} />
    </div>

</button>

<style>
    .row {
        padding: 15px 25px;
        border-radius: var(--box-radius);
        display: flex;
        gap: 20px;
        text-align: left;
        width: 100%;
        align-items: center;
        overflow-wrap: break-word;

    }
    .row:hover {
        background: var(--hover);
    }
    .company {
        width: 350px;
    }
    .country {
        width: 250px;
    }
    .status {
        flex: 1;
    }
    .user {
        flex: 1;
    }
    .created-at {
        flex: 1;
    }
</style>
