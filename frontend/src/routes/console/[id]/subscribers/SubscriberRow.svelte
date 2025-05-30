<script lang="ts">
	import { Checkbox, IconButton, Tooltip, confirm, toast } from '@hyvor/design/components';
	import type { Subscriber } from '../../types';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';
	import RelativeTime from '../../@components/utils/RelativeTime.svelte';
	import SubscriberStatus from './SubscriberStatus.svelte';
	import { listStore } from '../../lib/stores/newsletterStore';
	import { deleteSubscriber } from '../../lib/actions/subscriberActions';
	import SubscriberEdit from './SubscriberEdit.svelte';
	import { getI18n } from '../../lib/i18n';

	interface Props {
		subscriber: Subscriber;
		refreshList: () => void;
	}

	let { subscriber, refreshList }: Props = $props();

	let editing = $state(false);

	async function onDelete() {
		const confirmation = await confirm({
			title: 'Delete Subscriber',
			content: 'Are you sure you want to delete this subscriber?',
			confirmText: 'Delete',
			cancelText: 'Cancel',
			danger: true
		});

		if (!confirmation) return;

		confirmation.loading();

		deleteSubscriber(subscriber.id)
			.then(() => {
				toast.success('Subscriber deleted successfully');
				refreshList();
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				confirmation.close();
			});
	}

	let segmentsText = $derived(
		subscriber.list_ids
			.map((s) => {
				return $listStore.find((l) => l.id === s)?.name || 'Unknown';
			})
			.join(', ')
	);

	const I18n = getI18n();
</script>

<button class="subscriber">
	<div class="checkbox">
		<Checkbox />
	</div>

	<div class="email-wrap">
		<div class="email">{subscriber.email}</div>
		<div class="segments">
			<Tooltip text={segmentsText}>
				<span class="segments-text">
					{I18n.t('console.subscribers.listsCount', {
						count: subscriber.list_ids.length
					})}
				</span>
			</Tooltip>
		</div>
	</div>

	<div class="status-wrap">
		<div class="status">
			<SubscriberStatus status={subscriber.status} />
			<span class="status-time">
				{#if subscriber.status === 'subscribed' && subscriber.subscribed_at}
					<RelativeTime unix={subscriber.subscribed_at} />
				{:else if subscriber.status === 'unsubscribed' && subscriber.unsubscribed_at}
					<RelativeTime unix={subscriber.unsubscribed_at} />
				{/if}
			</span>
		</div>
	</div>

	<div class="source-wrap">
		<div>
			{I18n.t(('console.subscribers.source.' + subscriber.source) as any)}
		</div>
		<div class="tag">
			{I18n.t('console.subscribers.source.label')}
		</div>
	</div>

	<div class="actions">
		<IconButton color="input" size="small" on:click={() => (editing = true)}>
			<IconPencil size={12} />
		</IconButton>
		<IconButton color="red" variant="fill-light" size="small" on:click={onDelete}>
			<IconTrash size={12} />
		</IconButton>
	</div>
</button>

{#if editing}
	<SubscriberEdit {subscriber} bind:show={editing} {refreshList} />
{/if}

<style>
	.subscriber {
		padding: 15px 25px;
		border-radius: var(--box-radius);
		display: flex;
		text-align: left;
		width: 100%;
		align-items: center;
	}
	.subscriber:hover {
		background: var(--hover);
	}

	.checkbox {
		margin-right: 15px;
	}

	.email-wrap {
		flex: 1;
	}
	.email {
		font-weight: 600;
	}
	.segments {
		font-size: 14px;
	}
	.segments-text {
		cursor: pointer;
		text-decoration: underline;
		text-decoration-style: dotted;
	}
	.status-wrap {
		flex: 1;
	}
	.source-wrap {
		flex: 1;
	}

	.tag {
		font-size: 14px;
		color: var(--text-light);
	}

	.status-time {
		font-size: 14px;
		color: var(--text-light);
		margin-left: 4px;
	}

	@media (max-width: 992px) {
		.subscriber {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		}
	}
</style>
