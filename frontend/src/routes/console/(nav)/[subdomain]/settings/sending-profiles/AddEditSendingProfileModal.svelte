<script lang="ts">
	import { Modal, SplitControl, TextInput, toast } from '@hyvor/design/components';
	import type { SendingProfile } from '../../../../types';
	import { getI18n } from '../../../../lib/i18n';
	import { createSendingProfile, updateSendingProfile, type createSendingProfileParams } from '../../../../lib/actions/sendingProfileActions';
	import { sendingProfilesStore } from '../../../../lib/stores/newsletterStore';
	import ImageUploader from '../../../../@components/utils/ImageUploader.svelte';

    interface Props {
        profile?: SendingProfile;
        show: boolean;
    }

    let {profile, show = $bindable(false)}: Props = $props();

    let loading = $state(false);
    let fromEmail = $state(profile?.from_email ?? '');
    let fromName = $state(profile?.from_name ?? '');
    let replyToEmail = $state(profile?.reply_to_email ?? '');
    let brandName = $state(profile?.brand_name ?? '');
    let brandLogo = $state(profile?.brand_logo ?? '');

    let isCreating = $state(false);
    let isUpdating = $state(false);

    const I = getI18n();
    const sendingProfile = I.t('console.settings.sendingProfiles.sendingProfile');

    $effect(() => {
        isUpdating = !!profile &&
            (fromEmail !== (profile.from_email ?? '') ||
                fromName !== (profile.from_name ?? '') ||
                replyToEmail !== (profile.reply_to_email ?? '') ||
                brandName !== (profile.brand_name ?? '') ||
                brandLogo !== (profile.brand_logo ?? ''));
    })

    $effect(() => {
        isCreating = !profile && !!fromEmail;
    })

    async function handleConfirm() {

        loading = true;

        if (!fromEmail) {
            toast.error(I.t('console.settings.sendingProfiles.fromEmailRequired'));
            loading = false;
            return;
        }

        if (profile) {

            const changes = {
                from_email: fromEmail,
                from_name: fromName === '' ? null : fromName,
                reply_to_email: replyToEmail === '' ? null : replyToEmail,
                brand_name: brandName === '' ? null : brandName,
                brand_logo: brandLogo === '' ? null : brandLogo
            };

            const params = Object.fromEntries(
                Object.entries(changes).filter(
                    ([key, value]) => value !== profile[key as keyof typeof profile]
                )
            );

            updateSendingProfile(profile.id, params).then((res) => {
                toast.success(
                    I.t('console.common.updated', {
                        field: sendingProfile
                    })
                );
                sendingProfilesStore.update((profiles) =>
                    profiles.map((p) => (p.id === res.id ? res : p))
                );
                show = false;
            }).catch((error) => {
                toast.error(error.message);
            }).finally(() => {
                loading = false;
            })
        } else {
            createSendingProfile({
                from_email: fromEmail,
                from_name: fromName === '' ? null : fromName,
                reply_to_email: replyToEmail === '' ? null : replyToEmail,
                brand_name: brandName === '' ? null : brandName,
                brand_logo: brandLogo === '' ? null : brandLogo
            }).then((res) => {
                toast.success(
                    I.t('console.common.created', {
                        field: sendingProfile
                    })
                );
                sendingProfilesStore.update((profiles) => [...profiles, res]);
                show = false;
            }).catch((error) => {
                toast.error(error.message);
            }).finally(() => {
                loading = false;
            })
        }
    }
</script>

<Modal
        bind:show
        title={profile
		? I.t('console.common.updateField', {
				field: sendingProfile
			})
		: I.t('console.common.createField', {
				field: sendingProfile
			})}
        footer={{
		cancel: {
			text: I.t('console.common.cancel'),
		},
		confirm: {
			text: profile
				? I.t('console.common.update')
				: I.t('console.common.create'),
			props: {
				disabled: profile ? !isUpdating : !isCreating,
			}
		}
	}}
        on:confirm={handleConfirm}
        {loading}
        closeOnOutsideClick={false}
>
    <SplitControl label={`${I.t('console.settings.sendingProfiles.fromEmail')}*`}>
        <TextInput
                placeholder="from@hyvor.com"
                bind:value={fromEmail}
                block
        />
    </SplitControl>

    <SplitControl label={I.t('console.settings.sendingProfiles.fromName')}>
        <TextInput
                placeholder="Hyvor Post"
                bind:value={fromName}
                block
        />
    </SplitControl>

    <SplitControl label={I.t('console.settings.sendingProfiles.replyToEmail')}>
        <TextInput
                placeholder="to@hyvor.com"
                bind:value={replyToEmail}
                block
        />
    </SplitControl>

    <SplitControl label={I.t('console.settings.sendingProfiles.brandName')}>
        <TextInput
                placeholder="HYVOR"
                bind:value={brandName}
                block
        />
    </SplitControl>

    <SplitControl label={I.t('console.settings.sendingProfiles.brandLogo')}>
        <ImageUploader
                url={brandLogo}
                title="Upload Brand Logo"
                change={(url: string | null) => {
				brandLogo = url || '';
			}}
        />
    </SplitControl>
</Modal>
