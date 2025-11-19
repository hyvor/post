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
			<strong>
				{I18n.t('console.approve.approvedNotice', {
					date: dayjs.unix($approvalStore.approved_at).format('MMM D, YYYY')
				})}
			</strong>
			{#if $approvalStore.public_note}
				<div>
					<br />
					{$approvalStore.public_note}
				</div>
			{/if}
		</Callout>
	{/if}
</div>

<style>
	.banner {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100%;
		max-width: 50vw;

		:global(.icon) {
			margin-right: 16px !important;
			margin-top: 1px !important;
		}
	}
</style>
