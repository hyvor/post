<script lang="ts">
	import { Modal } from '@hyvor/design/components';
	import type { SendingProfile } from '../../../types';
	import { getI18n } from '../../../lib/i18n';

	interface Props {
		profile?: SendingProfile;
		show: boolean;
	}

	let { profile, show = $bindable(false) }: Props = $props();

	const I = getI18n();
	const sendingProfile = I.t('console.settings.sendingProfiles.sendingProfile');
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
>
	<!--  -->
</Modal>
