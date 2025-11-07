<script lang="ts">
    import {Button, Link, Loader, toast, confirm} from '@hyvor/design/components';
    import {getI18n} from '../../lib/i18n';
    import {deleteSubscribers} from '../../lib/actions/subscriberActions';
    import {slide} from 'svelte/transition';
    import {selectedSubscriberIdsStore, subscriberStore} from "../../lib/stores/newsletterStore";

    const I18n = getI18n();
    const MAX_SELECTABLE_SUBSCRIBERS = 100;

    interface Props {
        onUpdateMetadata: () => void;
        onUpdateStatus: () => void;
        onDelete: (ids: number[]) => void;
    }

    let {onDelete, onUpdateMetadata, onUpdateStatus}: Props = $props();

    let loading = $state(false);

    function handleSelectAll() {
        const availableSubscribers = $subscriberStore.slice(0, MAX_SELECTABLE_SUBSCRIBERS);
        if ($subscriberStore.length > MAX_SELECTABLE_SUBSCRIBERS) {
            toast.warning(I18n.t('console.subscribers.bulk.selectAllWarning', {
                count: MAX_SELECTABLE_SUBSCRIBERS
            }));
        }
        selectedSubscriberIdsStore.set(availableSubscribers.map(s => s.id));
    }

    async function handleDelete() {
        const confirmation = await confirm({
            title: I18n.t('console.subscribers.bulk.deleteTitle'),
            content: I18n.t('console.subscribers.bulk.deleteConfirm'),
            confirmText: I18n.t('console.common.delete'),
            cancelText: I18n.t('console.common.cancel'),
            danger: true
        });

        if (!confirmation) return;

        loading = true;
        const ids = $selectedSubscriberIdsStore;

        deleteSubscribers(ids)
            .then(() => {
                toast.success(I18n.t('console.subscribers.bulk.deleteSuccess'));
                onDelete(ids)
                selectedSubscriberIdsStore.set([]);
            })
            .catch((error: unknown) => {
                if (error instanceof Error) {
                    toast.error(error.message);
                } else {
                    toast.error(I18n.t('console.subscribers.bulk.deleteSuccess'));
                }
            })
            .finally(() => {
                loading = false;
            });
    }
</script>

{#if $selectedSubscriberIdsStore.length}
    <div class="selected-subscribers" transition:slide>
        <div class="inner">
            <div class="title">
                {I18n.t('console.subscribers.count', {
                    count: $selectedSubscriberIdsStore.length
                })}
                <div class="links">
                    <Link href="javascript:void()" on:click={handleSelectAll}>
                        {I18n.t('console.common.selectAll')}
                    </Link>
                    <Link href="javascript:void()" on:click={() => selectedSubscriberIdsStore.set([])}>
                        {I18n.t('console.subscribers.bulk.deselect')}
                    </Link>
                </div>
            </div>
            <div class="actions">
                <Button size="small" color="input" on:click={onUpdateStatus}>
                    {I18n.t('console.subscribers.bulk.updateStatus')}
                </Button>
                <Button size="small" color="input" on:click={onUpdateMetadata}>
                    {I18n.t('console.settings.metadata.update')}
                </Button>
                <Button size="small" color="red" variant="fill-light" on:click={handleDelete}>
                    {I18n.t('console.common.delete')}
                </Button>
            </div>

            {#if loading}
                <div class="loader-wrap">
                    <Loader/>
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
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .links {
        display: flex;
        gap: 10px;
    }

    .actions {
        display: flex;
        gap: 6px;
        padding-left: 15px;
        align-items: center;
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
