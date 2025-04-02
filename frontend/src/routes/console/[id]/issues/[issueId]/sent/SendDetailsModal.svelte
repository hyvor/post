<script lang="ts">
	import { Modal } from '@hyvor/design/components';
	import type { IssueSend } from '../../../../types';
	import RelativeTime from '../../../@components/utils/RelativeTime.svelte';

	export let send: IssueSend;
	export let show: boolean;
</script>

<Modal 
    bind:show 
    title="Send Details"
    on:cancel={() => show = false}
>
	<div class="modal-content">
		<div class="info-section">
			<h3>Email</h3>
			<p>{send.email}</p>
		</div>

		<div class="info-section">
			<h3>Timeline</h3>
			<ul class="timeline">
				<li>
					Created: {#if send.created_at}<RelativeTime unix={send.created_at} />{:else}Not available{/if}
				</li>
				<li>
					Sent: {#if send.sent_at}<RelativeTime unix={send.sent_at} />{:else}Not available{/if}
				</li>
				<li>
					Delivered: {#if send.delivered_at}<RelativeTime unix={send.delivered_at} />{:else}Not available{/if}
				</li>
				<li>
					First opened: {#if send.first_opened_at}<RelativeTime unix={send.first_opened_at} />{:else}Not opened{/if}
				</li>
				<li>
					Last opened: {#if send.last_opened_at}<RelativeTime unix={send.last_opened_at} />{:else}Not opened{/if}
				</li>
				<li>
					First clicked: {#if send.first_clicked_at}<RelativeTime unix={send.first_clicked_at} />{:else}Not clicked{/if}
				</li>
				<li>
					Last clicked: {#if send.last_clicked_at}<RelativeTime unix={send.last_clicked_at} />{:else}Not clicked{/if}
				</li>
				<li>
					Bounced: {#if send.bounced_at}<RelativeTime unix={send.bounced_at} /> ({send.hard_bounce ? 'Hard' : 'Soft'} bounce){:else}Not bounced{/if}
				</li>
				<li>
					Unsubscribed: {#if send.unsubscribed_at}<RelativeTime unix={send.unsubscribed_at} />{:else}Not unsubscribed{/if}
				</li>
				<li>
					Complained: {#if send.complained_at}<RelativeTime unix={send.complained_at} />{:else}Not complained{/if}
				</li>
				<li>
					Failed: {#if send.failed_at}<RelativeTime unix={send.failed_at} />{:else}Not failed{/if}
				</li>
			</ul>
		</div>

		<div class="info-section">
			<h3>Statistics</h3>
			<ul class="stats">
				<li>Opened: {send.open_count || 0} time{#if send.open_count !== 1}s{/if}</li>
				<li>Clicked: {send.click_count || 0} time{#if send.click_count !== 1}s{/if}</li>
			</ul>
		</div>
	</div>
</Modal>

<style>
	.modal-content {
		padding: 20px;
	}

	.info-section {
		margin-bottom: 20px;
	}

	.info-section h3 {
		margin: 0 0 10px 0;
		font-size: 16px;
		color: var(--text);
	}

	.info-section p {
		margin: 0;
		color: var(--text);
	}

	.timeline, .stats {
		list-style: none;
		padding: 0;
		margin: 0;
	}

	.timeline li, .stats li {
		margin-bottom: 8px;
		color: var(--text);
	}

	.timeline li:last-child, .stats li:last-child {
		margin-bottom: 0;
	}

</style> 