<script lang="ts">
    import {ActionList, ActionListItem, confirm, Dropdown, IconButton, Tag, toast, Tooltip} from '@hyvor/design/components';
    import type {SendingProfile} from '../../../../types';
    import {getI18n} from '../../../../lib/i18n';
    import {sendingProfilesStore} from '../../../../lib/stores/newsletterStore';
    import {deleteSendingProfile, updateSendingProfile} from '../../../../lib/actions/sendingProfileActions';
    import AddEditSendingProfileModal from './AddEditSendingProfileModal.svelte';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';

    interface Props {
        profile: SendingProfile;
    }

    let {profile}: Props = $props();

    const I = getI18n();
    let editing = $state(false);
    let showDropdown = $state(false);

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

    async function handleSetAsDefault() {
        const confirmation = await confirm({
            title: I.t('console.settings.sendingProfiles.setAsDefault'),
            content: I.t('console.settings.sendingProfiles.setAsDefaultContent'),
            autoClose: false
        });

        if (!confirmation) return;

        confirmation.loading();

        updateSendingProfile(profile.id, {is_default: true})
            .then((res) => {
                toast.success(
					I.t('console.common.updated', {
						field: I.t('console.settings.sendingProfiles.sendingProfile')
					})
				);
                sendingProfilesStore.update((profiles) =>
                    profiles.map((p) =>
                        p.id === res.id
                            ? res
                            : {...p, is_default: false}
                    )
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
        <Dropdown bind:show={showDropdown} align="end">
            {#snippet trigger()}
                <IconButton color="input">
                    <IconCaretDown size={12} />
                </IconButton>
            {/snippet}
            {#snippet content()}
                <ActionList>
                    {#if !profile.is_default}
                        <ActionListItem on:select={() => {showDropdown = false; handleSetAsDefault();}}>
                            Set as default
                        </ActionListItem>
                    {/if}
                    <ActionListItem on:select={() => {showDropdown = false; editing = true;}}>
                        Edit
                    </ActionListItem>
                    {#if !profile.is_system}
                        <ActionListItem on:select={() => {showDropdown = false; handleDelete();}}>
                            Delete
                        </ActionListItem>
                    {/if}
                </ActionList>
            {/snippet}
        </Dropdown>
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

    :global(.content-wrap) {
        margin-top: 10px;
    }
</style>
