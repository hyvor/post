<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { IssueSend } from '../../../../types';
	import RelativeTime from '../../../@components/utils/RelativeTime.svelte';

	export let send: IssueSend;
</script>

<div class="send-row">
	<div class="email">
		{send.email}
	</div>
	<div class="time">
		{#if send.delivered_at}
			Delivered <RelativeTime unix={send.delivered_at} />
		{:else if send.sent_at}
			Sent <RelativeTime unix={send.sent_at} />
		{/if}
	</div>
	<div class="good-tags tags">
		{#if send.first_opened_at}
			<Tag size="small" color="blue">
				Opened
				{#if send.open_count > 1}
					({send.open_count} times)
				{/if}
			</Tag>
		{/if}
		{#if send.first_clicked_at}
			<Tag size="small" color="green">
				Clicked
				{#if send.click_count > 1}
					({send.click_count} times)
				{/if}
			</Tag>
		{/if}
	</div>
	<div class="bad-tags tags">
		{#if send.bounced_at}
			<Tag size="small" color="orange">Bounced {send.hard_bounce ? '(Hard)' : '(Soft)'}</Tag>
		{/if}
		{#if send.unsubscribed_at}
			<Tag size="small" color="red">Unsubscribed</Tag>
		{/if}
		{#if send.complained_at}
			<Tag size="small" color="red">Complained</Tag>
		{/if}
		{#if send.failed_at}
			<!-- This should not happen -->
			<Tag size="small" color="red">Failed</Tag>
		{/if}
	</div>
</div>

<style>
	.send-row {
		display: flex;
		align-items: center;
		padding: 10px 0;
	}
	.time {
		flex: 1;
	}
	.email {
		flex: 1;
	}
	.tags {
		flex: 1;
	}
</style>
