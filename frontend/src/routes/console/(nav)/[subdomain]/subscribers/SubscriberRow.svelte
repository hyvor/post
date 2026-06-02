<script lang="ts">
	import { Checkbox, IconButton, Tag, Tooltip, confirm, toast } from '@hyvor/design/components';
	import type { Subscriber } from '../../../types';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';
	import IconEnvelopeAt from '@hyvor/icons/IconEnvelopeAt';
	import RelativeTime from '../../../@components/utils/RelativeTime.svelte';
	import SubscriberStatus from './SubscriberStatus.svelte';
	import { listStore } from '../../../lib/stores/newsletterStore';
	import { deleteSubscriber, resendOptInEmail } from '../../../lib/actions/subscriberActions';
	import SubscriberEdit from './SubscriberEdit.svelte';
	import { getI18n } from '../../../lib/i18n';
	import { selectedSubscriberIdsStore } from '../../../lib/stores/newsletterStore';

	interface Props {
		subscriber: Subscriber;
		handleDelete: (ids: number[]) => void;
		handleUpdate: (subscriber: Subscriber) => void;
	}

	let { subscriber, handleDelete, handleUpdate }: Props = $props();

	let editing = $state(false);
	let resending = $state(false);
	let isSelected = $derived($selectedSubscriberIdsStore.includes(subscriber.id));

	function toggleSelection() {
		if (isSelected) {
			selectedSubscriberIdsStore.update((ids: number[]) =>
				ids.filter((id: number) => id !== subscriber.id)
			);
		} else {
			selectedSubscriberIdsStore.update((ids: number[]) => [...ids, subscriber.id]);
		}
	}

	async function onDelete() {
		const confirmation = await confirm({
			title: 'Delete Subscriber',
			content: 'Are you sure you want to delete this subscriber?',
			confirmText: 'Delete',
			cancelText: 'Cancel',
			danger: true,
			autoClose: false
		});

		if (!confirmation) return;

		confirmation.loading();

		deleteSubscriber(subscriber.id)
			.then(() => {
				toast.success('Subscriber deleted successfully');
				handleDelete([subscriber.id]);
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				confirmation.close();
			});
	}

	async function onResendOptIn() {
		resending = true;
		resendOptInEmail(subscriber.id)
			.then(() => {
				toast.success('Opt-in email sent successfully');
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				resending = false;
			});
	}

	let listNames = $derived(
		subscriber.list_ids.map((id) => $listStore.find((l) => l.id === id)?.name || 'Unknown')
	);

	const I18n = getI18n();
</script>

<button class="subscriber">
	<label class="checkbox">
		<Checkbox checked={isSelected} on:change={toggleSelection} />
	</label>

	<div class="email-wrap">
		<div class="email">{subscriber.email}</div>
		<div class="status-tag">
			<SubscriberStatus status={subscriber.status} size="small" />
			{#if subscriber.status === 'subscribed' && subscriber.subscribed_at}
				<span class="status-time">
					<RelativeTime unix={subscriber.subscribed_at} />
				</span>
			{/if}
		</div>
	</div>

	<div class="lists-wrap">
		{#if listNames.length > 0}
			<div class="list-names">
				{#each listNames.sort((a, b) => a.localeCompare(b)) as name}
					<Tag color="default" size="small">{name}</Tag>
				{/each}
			</div>
		{:else}
			<span class="no-lists">No lists</span>
		{/if}
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
		{#if subscriber.status === 'pending'}
			<Tooltip text="Resend opt-in email">
				<IconButton color="input" size="small" on:click={onResendOptIn} disabled={resending}>
					<IconEnvelopeAt size={12} />
				</IconButton>
			</Tooltip>
		{/if}
		<IconButton color="input" size="small" on:click={() => (editing = true)}>
			<IconPencil size={12} />
		</IconButton>
		<IconButton color="red" variant="fill-light" size="small" on:click={onDelete}>
			<IconTrash size={12} />
		</IconButton>
	</div>
</button>

{#if editing}
	<SubscriberEdit bind:show={editing} {subscriber} {handleUpdate} />
{/if}

<style>
	.subscriber {
		padding: 12px 20px 12px 55px;
		border-radius: var(--box-radius);
		display: grid;
		grid-template-columns: 2fr 1.5fr 1fr 100px;
		align-items: center;
		gap: 12px;
		text-align: left;
		width: 100%;
		position: relative;
	}
	.subscriber:hover {
		background: var(--hover);
	}

	.checkbox {
		position: absolute;
		left: 15px;
		width: 40px;
		height: 100%;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
	}

	.email {
		font-weight: 600;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.status-tag {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-top: 4px;
	}
	.status-time {
		font-size: 14px;
		color: var(--text-light);
	}

	.list-names {
		display: flex;
		flex-wrap: wrap;
		gap: 4px;
	}
	.no-lists {
		font-size: 14px;
		color: var(--text-light);
	}

	.tag {
		font-size: 14px;
		color: var(--text-light);
	}

	.actions {
		display: flex;
		gap: 4px;
		align-items: center;
		justify-content: flex-end;
	}

	@media (max-width: 992px) {
		.subscriber {
			grid-template-columns: 1fr auto;
			grid-template-rows: auto auto;
			padding-left: 55px;
		}

		.lists-wrap {
			grid-column: 1;
		}

		.source-wrap {
			display: none;
		}

		.actions {
			grid-column: 2;
			grid-row: 1 / 3;
			align-self: center;
		}
	}
</style>
