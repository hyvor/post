<script lang="ts">
	import Icon from "./Icon.svelte";

    interface Props {
		heading: string;
		message: string;
	}

    let { heading, message }: Props = $props();
    
</script>

<div class="inner-container hds-box">
    <div class="icon">
        <Icon />
    </div>
    <div class="message">
        <h2>{heading}</h2>
        <p>
            {@html message}
        </p>
    </div>
</div>

<style>
    .inner-container {
        padding: 20px 0;
    }
    .icon {
        width: 50%;
        height: 50%;
        margin: 0 auto;
    }
    .message {
        width: 70%;
        margin: 0 auto;
        margin-top: -10px;
        margin-bottom: 30px;
    }
</style>