<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import IconPersonFillCheck from '@hyvor/icons/IconPersonFillCheck';
	import { getI18n } from '../../lib/i18n';
	import { approvalStore } from '../../lib/stores/consoleStore';
	import dayjs from 'dayjs';

	const I18n = getI18n();
</script>

<div class="banner">
	{#if $approvalStore?.approved_at}
		<Callout type="success">
			{#snippet icon()}
				<IconPersonFillCheck size={18} />
			{/snippet}
			{I18n.t('console.approve.approvedNotice', {
				date: dayjs.unix($approvalStore.approved_at).format('MMM D, YYYY')
			})}
		</Callout>
	{/if}
</div>

<style>
	.banner {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100%;
	}
</style>
