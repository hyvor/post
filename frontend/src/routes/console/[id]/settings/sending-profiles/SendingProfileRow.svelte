<script lang="ts">
    import {confirm, IconButton, Tag, toast, Tooltip} from '@hyvor/design/components';
    import type {SendingProfile} from '../../../types';
    import IconTrash from '@hyvor/icons/IconTrash';
    import {getI18n} from '../../../lib/i18n';
    import {sendingProfilesStore} from '../../../lib/stores/newsletterStore';
    import {deleteSendingProfile} from '../../../lib/actions/sendingProfileActions';
    import IconPencil from '@hyvor/icons/IconPencil';
    import AddEditSendingProfileModal from './AddEditSendingProfileModal.svelte';

    interface Props {
        profile: SendingProfile;
    }

    let {profile}: Props = $props();

    const I = getI18n();
    let editing = $state(false);

    async function handleDelete() {
        const confirmation = await confirm({
            title: I.t('console.settings.sendingProfiles.deleteSendingProfile'),
            content: I.t('console.settings.sendingProfiles.deleteSendingProfileContent'),
            confirmText: I.t('console.common.delete'),
            cancelText: I.t('console.common.cancel'),
            danger: true,
            autoClose: false
        });

        if (!confirmation) return;

        confirmation.loading();

        deleteSendingProfile(profile.id)
            .then((res) => {
                sendingProfilesStore.set(res);

                toast.success(
                    I.t('console.common.deleted', {
                        field: I.t('console.settings.sendingProfiles.sendingProfile')
                    })
                );
            })
            .catch((e) => {
                toast.error(e.message);
            })
            .finally(() => {
                confirmation.close();
            });
    }
</script>

<div class="profile">
    <div class="email-wrap">
        <div class="email">
            {profile.from_email}

            {#if profile.is_system}
                <Tooltip text="This is the default system profile.">
                    <Tag size="small" color="blue">System</Tag>
                </Tooltip>
            {/if}

            {#if profile.is_default}
                <Tooltip text="New issues will use this profile by default.">
                    <Tag size="small" color="green">Default</Tag>
                </Tooltip>
            {/if}
        </div>
        {#if profile.from_name}
            <div class="name">
                {profile.from_name}
            </div>
        {/if}

        {#if profile.reply_to_email}
            <div class="reply-to">
                (reply to: {profile.reply_to_email})
            </div>
        {/if}
    </div>

    <div class="brand-wrap">
        {#if !profile.brand_name && !profile.brand_logo}
            <div class="no-brand">No branding configured</div>
        {:else}
            <div class="brand">
                {#if profile.brand_logo}
                    <img src={profile.brand_logo} alt="Brand Logo"/>
                {/if}
                {#if profile.brand_name}
                    <span class="brand-name">{profile.brand_name}</span>
                {/if}
            </div>
        {/if}
    </div>

    <div class="action">
        <IconButton color="gray" variant="fill-light" size="small" on:click={() => (editing = true)}>
            <IconPencil size={12}/>
        </IconButton>
        {#if !profile.is_system}
            <IconButton color="red" variant="fill-light" size="small" on:click={handleDelete}>
                <IconTrash size={12}/>
            </IconButton>
        {/if}
    </div>
</div>

<AddEditSendingProfileModal {profile} bind:show={editing}/>

<style>
    .profile {
        display: flex;
        align-items: center;
        padding: 10px;
    }

    .email {
        font-weight: 600;
        width: 50%;
        word-break: break-word;
        overflow-wrap: anywhere;

    }

    .name {
        font-size: 14px;
        color: var(--text-light);
        margin-top: 2px;
    }

    .reply-to {
        font-size: 14px;
        color: var(--text-light);
        margin-top: 2px;
    }

    img {
        max-height: 30px;
        border-radius: 2px;
    }

    .email-wrap {
        flex: 1;
    }

    .brand-wrap {
        flex: 1;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action {
        width: 100px;
        text-align: right;
    }
</style>
