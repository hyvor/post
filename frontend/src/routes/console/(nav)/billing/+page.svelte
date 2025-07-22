<script lang="ts">
	import { onMount } from 'svelte';
	import Plans from './Plans.svelte';
	import Usage from './Usage.svelte';
	import { getI18n } from '../../lib/i18n';
    import {approvalStore} from "../../lib/stores/consoleStore";
    import {goto} from "$app/navigation";
    import {toast} from "@hyvor/design/components";

	const I18n = getI18n();

	onMount(() => {
        if (!$approvalStore.is_approved) {
            toast.info(I18n.t('console.billing.approvalRequired'));
            goto('/console/approve');
        }
		// if ($ownerStore) getBillingData().then((r) => (data = r));
	});
</script>

<!-- {#if $ownerStore} -->
<div id="billing">
	<div class="column">
		<div class="billing-section hds-box no-padding">
			<Plans />
		</div>
	</div>

	<div class="column">
		<div class="billing-section hds-box">
			<div class="section-title">{I18n.t('console.billing.usage.title')}</div>
			<Usage />
		</div>
	</div>
</div>

<!-- {:else}
	<div class="hds-box not-owner">
		<div class="msg">You need to <b>own</b> at least one website to start a subscription.</div>
		<div class="btn">
			<Button as="a" href="/console/new">
				{I18n.t('console.home.newWebsite')}
			</Button>
		</div>
	</div>
{/if} -->

<style>
	#billing {
		display: flex;
		flex: 1;
	}

	.column {
		flex: 1;
	}

	.billing-section {
		margin: 0 5px 10px;
		padding: 30px;
	}

	.billing-section.no-padding {
		padding: 0;
	}

	.section-title {
		font-size: 20px;
		font-weight: 600;
		margin-bottom: 15px;
	}

	@media (max-width: 992px) {
		#billing {
			flex-direction: column;
		}
		.column {
			width: 100%;
		}
	}
</style>
