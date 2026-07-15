<script>
	import Lists from './Lists.svelte';
	import SendingProfile from './SendingProfile.svelte';
	import TestEmail from './TestEmail.svelte';
	import { Button, Callout } from '@hyvor/design/components';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
	import {
		canSendIssues,
		resolvedLicenseStore
	} from '../../../../../../lib/stores/consoleStore.js';
</script>

<div class="audience-wrap">
	<div class="audience">
		<Lists />
		<SendingProfile />
	</div>

	<div class="test-email">
		{#if !canSendIssues($resolvedLicenseStore)}
			<div class="subscription-callout">
				<Callout type="warning">
					{#snippet icon()}
						<IconExclamationCircle />
					{/snippet}

					<div class="message">
						You must have an active subscription before you can send newsletters. Please upgrade
						your subscription to continue.
						<Button size="small" as="a" href="/console/billing">Go to Billing</Button>
					</div>
				</Callout>
			</div>
		{/if}

		<TestEmail />
	</div>
</div>

<style>
	.audience-wrap {
		flex: 1;
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.audience {
		padding: 30px 50px;
	}

	.test-email {
		margin-top: auto;
	}

	.subscription-callout {
		padding: 15px 30px;
	}

	.message {
		display: flex;
		align-items: center;
		justify-items: flex-end;
		gap: 8px;
	}

	:global(.text-wrap) {
		align-items: center !important;
	}
</style>
