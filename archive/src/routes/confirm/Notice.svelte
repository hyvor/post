<script lang="ts">
    import {Button, IconMessage} from "@hyvor/design/components";
    import IconEvelopeCheck from "@hyvor/icons/IconEnvelopeCheck";
    import IconExclamationOctagon from "@hyvor/icons/IconExclamationOctagon";

    interface Props {
        heading: string;
        message: string;
        isError?: boolean;
    }

    let {heading, message, isError = false}: Props = $props();

</script>

<IconMessage
    icon={isError ? IconExclamationOctagon : IconEvelopeCheck}
    iconColor="var(--hp-box-text)"
>
    <div class="message-wrap">
        <h2>{heading}</h2>
        <p>
            {@html message}
        </p>
        {#if !isError}
            <Button
                style="background-color: var(--hp-accent); color: var(--hp-accent-text);"
                as="a"
                href="/"
            >
                Show Archives
            </Button>
        {/if}
    </div>
</IconMessage>

<style>
    .message-wrap {
        color: var(--hp-box-text);
    }
</style>
