<script>
    import Lists from './Lists.svelte';
    import SendingProfile from './SendingProfile.svelte';
    import TestEmail from "./TestEmail.svelte";
</script>

<div class="audience-wrap">

    <div class="audience">
        <Lists/>
        <SendingProfile/>
    </div>

    <div class="test-email">
        <TestEmail/>
    </div>
</div>

<style>
    .audience-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .audience {
        padding: 30px 50px;
    }

    .test-email {
        margin-top: auto;
    }
</style>
