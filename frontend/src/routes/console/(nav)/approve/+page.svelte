<script lang="ts">

import SingleBox from "../../@components/content/SingleBox.svelte";
import ApprovalForm from "./ApprovalForm.svelte";
</script>

<SingleBox>
    <div class="content">
        <ApprovalForm />
    </div>
</SingleBox>


<style>
    .content {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 20px 30px;
        overflow: auto;
    }
</style>
