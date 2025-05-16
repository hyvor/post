<script lang="ts">
	import { Modal, SplitControl } from '@hyvor/design/components';
	import type { IssueSend } from '../../../../types';
	import RelativeTime from '../../../../@components/utils/RelativeTime.svelte';

	export let send: IssueSend;
	export let show: boolean;
</script>

<Modal
    bind:show
    title="Send Details"
    on:cancel={() => show = false}
>
	<div class="modal-content">
		<SplitControl label="Email">
			{send.email}
		</SplitControl>

		{#if send.sent_at}
			<SplitControl label="Sent">
				<RelativeTime unix={send.sent_at} />
			</SplitControl>
		{/if}
		{#if send.delivered_at}
			<SplitControl label="Delivered">
				<RelativeTime unix={send.delivered_at} />
			</SplitControl>
		{/if}
		{#if send.first_opened_at}
			<SplitControl label="First opened">
				<RelativeTime unix={send.first_opened_at} />
			</SplitControl>
		{/if}
		{#if send.last_opened_at}
			<SplitControl label="Last opened">
				<RelativeTime unix={send.last_opened_at} />
			</SplitControl>
		{/if}
		{#if send.first_clicked_at}
			<SplitControl label="First clicked">
				<RelativeTime unix={send.first_clicked_at} />
			</SplitControl>
		{/if}
		{#if send.last_clicked_at}
			<SplitControl label="Last clicked">
				<RelativeTime unix={send.last_clicked_at} />
			</SplitControl>
		{/if}
		{#if send.bounced_at}
			<SplitControl label="Bounced">
				<RelativeTime unix={send.bounced_at} /> ({send.hard_bounce ? 'Hard' : 'Soft'} bounce)
			</SplitControl>
		{/if}
		{#if send.unsubscribed_at}
			<SplitControl label="Unsubscribed">
				<RelativeTime unix={send.unsubscribed_at} />
			</SplitControl>
		{/if}
		{#if send.complained_at}
			<SplitControl label="Complained">
				<RelativeTime unix={send.complained_at} />
			</SplitControl>
		{/if}
		{#if send.failed_at}
			<SplitControl label="Failed">
				<RelativeTime unix={send.failed_at} />
			</SplitControl>
		{/if}

		<SplitControl label="Opened">
			{send.open_count || 0} time{#if (send.open_count || 0) !== 1}s{/if}
		</SplitControl>
		<SplitControl label="Clicked">
			{send.click_count || 0} time{#if (send.click_count || 0) !== 1}s{/if}
		</SplitControl>
	</div>
</Modal>

<style>
	.modal-content {
		padding: 20px;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}
</style>
