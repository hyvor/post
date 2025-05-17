<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import TrialChecks from './TrialChecks.svelte';

	interface Props {
		title?: string;
		[key: string]: any;
	}

	let { title = 'Ready to start your newsletter?', ...rest }: Props = $props();
</script>

<div class="wrap" {...rest}>
	<div class="hds-container inner">
		<h3>
			{title}
		</h3>

		<div class="button-wrap">
			<Button as="a" size="large" href="/console?signup">Start your newsletter</Button>
		</div>

		<div class="trial-checks">
			<TrialChecks style="display:flex;justify-content:space-around" />
		</div>
	</div>
</div>

<style>
	.wrap {
		background-color: var(--accent-light);
		padding: 80px 15px;
		margin: 60px 0;
	}

	.inner {
		width: 700px;
		max-width: 100%;
		margin: auto;
	}

	h3 {
		text-align: center;
		font-size: 25px;
		font-weight: 600;
	}

	.button-wrap {
		padding: 20px;
		text-align: center;
	}

	.trial-checks {
		margin-top: 15px;
	}
</style>
