<script lang="ts">
	import { Button, Link, Loader, toast } from '@hyvor/design/components';
	import { getI18n } from '../../lib/i18n';
	import { deleteSubscribers, updateSubscribersStatus } from '../../lib/actions/subscriberActions';
	import { slide } from 'svelte/transition';
	import { selectedSubscriberIds } from './subscriberStore';
	import type { NewsletterSubscriberStatus } from '../../types';

	interface Props {
		refreshList: () => void;
	}

	let { refreshList }: Props = $props();

	let loading = false;
	let success = false;

	function handleBulkAction(action: 'delete' | 'update_status', status?: NewsletterSubscriberStatus) {
		loading = true;
		const ids = $selectedSubscriberIds;

		if (action === 'delete') {
			deleteSubscribers(ids)
				.then(() => {
					toast.success('Subscribers deleted successfully');
					success = true;
					selectedSubscriberIds.set([]);
					refreshList();
				})
				.catch((error: unknown) => {
					if (error instanceof Error) {
						toast.error(error.message);
					} else {
						toast.error('An unknown error occurred');
					}
				})
				.finally(() => {
					loading = false;
				});
		} else if (action === 'update_status' && status) {
			updateSubscribersStatus(ids, status)
				.then(() => {
					toast.success('Subscribers status updated successfully');
					success = true;
					selectedSubscriberIds.set([]);
					refreshList();
				})
				.catch((error: unknown) => {
					if (error instanceof Error) {
						toast.error(error.message);
					} else {
						toast.error('An unknown error occurred');
					}
				})
				.finally(() => {
					loading = false;
				});
		}
	}

	const I18n = getI18n();
</script>

{#if $selectedSubscriberIds.length}
	<div class="selected-subscribers" transition:slide>
		<div class="inner">
			<div class="title">
				{I18n.t('console.subscribers.count', {
					count: $selectedSubscriberIds.length
				})}
				<Link href="javascript:void()" on:click={() => selectedSubscriberIds.set([])}>
					{I18n.t('console.common.cancel')}
				</Link>
			</div>
			<div class="actions">
				<Button size="small" variant="fill-light" color="green" on:click={() => handleBulkAction('update_status', 'subscribed')}>
					{I18n.t('console.subscribers.status.subscribed')}
				</Button>
				<Button size="small" variant="fill-light" color="orange" on:click={() => handleBulkAction('update_status', 'unsubscribed')}>
					{I18n.t('console.subscribers.status.unsubscribed')}
				</Button>
				<Button size="small" variant="fill-light" color="gray" on:click={() => handleBulkAction('update_status', 'pending')}>
					{I18n.t('console.subscribers.status.pending')}
				</Button>
				<Button size="small" variant="fill-light" color="red" on:click={() => handleBulkAction('delete')}>
					{I18n.t('console.common.delete')}
				</Button>
			</div>

			{#if loading}
				<div class="loader-wrap">
					<Loader />
				</div>
			{/if}
		</div>
	</div>
{/if}

<style>
	.selected-subscribers {
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 25px 15px;
		z-index: 1000;
	}
	.inner {
		background-color: var(--accent-lightest);
		border-radius: var(--box-radius);
		box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
		background-color: var(--accent-lightest);
		display: flex;
		padding: 10px 30px;
		align-items: center;
		position: relative;
		overflow: hidden;
	}
	.title {
		border-right: 1px solid var(--accent);
		padding-right: 15px;
		font-size: 14px;
	}
	.actions {
		display: flex;
		gap: 6px;
		padding-left: 15px;
	}
	.loader-wrap {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 1;
		background-color: var(--accent-lightest);
	}

	.success {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: 2;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--green);
		background-color: var(--accent-lightest);
	}
	.success .message {
		font-size: 14px;
		color: var(--text);
	}

	@media (max-width: 992px) {
		.inner {
			flex-direction: column;
			padding: 15px;
			gap: 15px;
		}
		.title {
			padding-right: 0;
			border-right: none;
		}
	}
</style>
