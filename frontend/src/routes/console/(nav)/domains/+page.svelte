<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import SingleBox from '../../@components/content/SingleBox.svelte';
	import CreateDomainModal from './CreateDomainModal.svelte';
	import DomainList from './DomainList.svelte';
	import { getI18n } from '../../lib/i18n';
	import IconPlus from '@hyvor/icons/IconPlus';

	let showCreateModal = $state(false);
	let domainList: DomainList = $state({} as DomainList);

	function handleCreate() {
		domainList.refreshDomains();
	}

	const I18n = getI18n();
</script>

<SingleBox>
	<div class="content">
		<div class="header">
			<Button on:click={() => (showCreateModal = true)}
				>{I18n.t('console.domains.addDomain')}

				{#snippet end()}
					<IconPlus size={14} />
				{/snippet}
			</Button>
		</div>
		<DomainList bind:this={domainList} />
	</div>
</SingleBox>

<CreateDomainModal bind:show={showCreateModal} onCreate={handleCreate} />

<style>
	.content {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.header {
		display: flex;
		align-items: center;
		padding: 20px 30px;
		border-bottom: 1px solid var(--border);
	}
</style>
