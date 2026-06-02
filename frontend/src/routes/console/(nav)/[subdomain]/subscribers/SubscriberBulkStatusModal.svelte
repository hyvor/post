<script lang="ts">
	import { FormControl, Modal, Radio, SplitControl, toast } from '@hyvor/design/components';
	import { getI18n } from '../../../lib/i18n';
	import { selectedSubscriberIdsStore, subscriberStore } from '../../../lib/stores/newsletterStore';
	import type { NewsletterSubscriberStatus } from '../../../types';
	import { createSubscriber } from '../../../lib/actions/subscriberActions';
	import { SimpleLoadingProgress } from './bulkLoader.svelte';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();
	let status: NewsletterSubscriberStatus = $state('pending');
	let loader = new SimpleLoadingProgress();

	async function handleStatusChange() {
		loader.start($selectedSubscriberIdsStore.length);

		for (const subscriberId of $selectedSubscriberIdsStore) {
			const subscriber = $subscriberStore.find((s) => s.id === subscriberId);

			if (!subscriber) {
				loader.next();
				continue;
			}

			if (subscriber.status === status) {
				loader.next();
				continue;
			}

			try {
				const newSubscriber = await createSubscriber(subscriber.email, {
					status
				});
				subscriberStore.update((subs) =>
					subs.map((sub) => (sub.id === newSubscriber.id ? newSubscriber : sub))
				);
			} catch (e: any) {
				toast.error('Unable to update subscriber: ' + e.message);
				break;
			}

			loader.next();
		}

		loader.done();
		toast.success(I18n.t('console.subscribers.bulk.statusUpdateSuccess'));
		show = false;
	}

	const I18n = getI18n();
</script>

<Modal
	bind:show
	title={I18n.t('console.subscribers.bulk.updateStatus')}
	footer={{
		cancel: {
			text: I18n.t('console.common.cancel')
		}
	}}
	loading={loader.getLoading()}
	on:confirm={handleStatusChange}
>
	<SplitControl label="Status">
		<FormControl>
			<Radio name="status" bind:group={status} value="pending">Pending</Radio>
			<Radio name="status" bind:group={status} value="subscribed">Subscribed</Radio>
		</FormControl>
	</SplitControl>
</Modal>
