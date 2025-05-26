<script lang="ts">
	import ListRow from './ListRow.svelte';
	import CreateListButton from './CreateListButton.svelte';
	import { listStore } from '../../lib/stores/newsletterStore';
	import { getI18n } from '../../lib/i18n';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { Tooltip } from '@hyvor/design/components';

	const I18n = getI18n();
</script>

<div id="list-view">
	<div class="list-header">
		<Tooltip text={I18n.t('console.home.lists.tooltip')}>
			<div class="left">
				{I18n.t('console.home.lists.title')}
				<IconInfoCircle />
			</div>
		</Tooltip>

		<CreateListButton />
	</div>
	<div class="rows">
		{#each $listStore as list}
			<ListRow {list} />
		{/each}
	</div>
</div>

<style>
	.list-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20px;
		padding-left: 35px;
		padding-right: 35px;
		font-weight: 600;
	}
	.left {
		display: flex;
		align-items: center;
		gap: 5px;
		cursor: pointer;
	}
	.rows {
		padding: 10px 35px;
	}
</style>
