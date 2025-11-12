<script>
    import {Radio, SplitControl, Tooltip} from '@hyvor/design/components';
    import {sendingProfilesStore} from '../../../../../../lib/stores/newsletterStore.ts';
    import {Tag} from "@hyvor/design/components";
    import {draftIssueEditingStore, draftIssueStore} from "../draftStore.ts";
    import {debouncedUpdateDraftIssue} from "../draftActions.ts";
    import {getI18n} from "../../../../../../lib/i18n.ts";

    let sendingProfileId = $state(
        $draftIssueStore.sending_profile_id ??
        $sendingProfilesStore.find(p => p.is_default)?.id
        ?? 0
    );

    const I18n = getI18n();

    $effect(() => {
        $draftIssueEditingStore.sending_profile_id = sendingProfileId;
        debouncedUpdateDraftIssue();
    })
</script>

<SplitControl label={I18n.t('console.issues.draft.sendingProfile.title')}>
    {#each $sendingProfilesStore as profile (profile.id)}
        <div class="profile">
            <Radio bind:group={sendingProfileId} value={profile.id} name="sending-profile">
                <div class="profile-content">
                    <div class="email-wrap">
                        <div class="email">
                            {profile.from_email}

                            {#if profile.is_system}
                                <Tooltip text={I18n.t('console.issues.draft.sendingProfile.system')}>
                                    <Tag size="small" color="blue">System</Tag>
                                </Tooltip>
                            {/if}

                            {#if profile.is_default}
                                <Tooltip text={I18n.t('console.issues.draft.sendingProfile.default')}>
                                    <Tag size="small" color="green">Default</Tag>
                                </Tooltip>
                            {/if}
                        </div>
                        <div class="name-wrap">
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
                    </div>

                    <div class="brand-wrap">
                        {#if !profile.brand_name && !profile.brand_logo}
                            <div class="no-brand">{I18n.t('console.issues.draft.sendingProfile.noBranding')}</div>
                        {:else}
                            <div class="brand">
                                {#if profile.brand_logo}
                                    <img src={profile.brand_logo}
                                         alt={I18n.t('console.issues.draft.sendingProfile.brandLogo')}/>
                                {/if}
                                {#if profile.brand_name}
                                    <span class="brand-name">{profile.brand_name}</span>
                                {/if}
                            </div>
                        {/if}
                    </div>
                </div>
            </Radio>
        </div>
    {/each}
</SplitControl>

<style>
    .profile {
        display: flex;
        flex-direction: column;
        padding: 6px 0;
    }

    .profile :global(label) {
        height: initial;
    }

    .profile-content {
        display: flex;
        width: 100%;
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

    .name-wrap {
        display: flex;
        gap: 6px;
    }

    .brand-wrap {
        margin-left: auto;
        width: 35%;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
