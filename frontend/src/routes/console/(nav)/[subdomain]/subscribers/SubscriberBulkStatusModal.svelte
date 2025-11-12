<script lang="ts">
    import {Button, Modal, toast} from '@hyvor/design/components';
    import {getI18n} from '../../../lib/i18n';
    import {updateSubscribersStatus} from '../../../lib/actions/subscriberActions';
    import {selectedSubscriberIdsStore} from "../../../lib/stores/newsletterStore";
    import type {NewsletterSubscriberStatus} from '../../../types';

    interface Props {
        show: boolean;
        onStatusUpdate: (ids: number[], status: NewsletterSubscriberStatus) => void;
    }

    let {show = $bindable(), onStatusUpdate}: Props = $props();

    let loading = $state(false);

    async function handleStatusChange(status: NewsletterSubscriberStatus) {
        loading = true;
        const ids = $selectedSubscriberIdsStore;

        updateSubscribersStatus(ids, status)
            .then(() => {
                toast.success(I18n.t('console.subscribers.bulk.statusUpdateSuccess'));
                selectedSubscriberIdsStore.set([]);
                show = false;
                onStatusUpdate(ids, status);
            })
            .catch((error: unknown) => {
                if (error instanceof Error) {
                    toast.error(error.message);
                } else {
                    toast.error(I18n.t('console.subscribers.bulk.statusUpdateSuccess'));
                }
            })
            .finally(() => {
                loading = false;
            });
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
        {loading}
>
    <div class="status-options">
        <Button color="input" on:click={() => handleStatusChange('subscribed')}>
            {I18n.t('console.subscribers.status.subscribed')}
        </Button>
        <Button color="input" on:click={() => handleStatusChange('unsubscribed')}>
            {I18n.t('console.subscribers.status.unsubscribed')}
        </Button>
        <Button color="input" on:click={() => handleStatusChange('pending')}>
            {I18n.t('console.subscribers.status.pending')}
        </Button>
    </div>
</Modal>

<style>
    .status-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
</style> 