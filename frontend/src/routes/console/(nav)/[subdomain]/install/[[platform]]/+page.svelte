<script lang="ts">
	import { page } from '$app/stores';
	import SingleBox from '../../../../@components/content/SingleBox.svelte';
	import { newsletterStore } from '../../../../lib/stores/newsletterStore';
	import IconEnvelope from '@hyvor/icons/IconEnvelope';
	import Install from '$lib/install/Install.svelte';

	$: platform = $page.params.platform ?? 'html';
</script>

<SingleBox>
	<div class="install">
		<h2>
			Signup Form

			<span class="icon">
				<IconEnvelope size={25} />
			</span>
		</h2>
		<Install
			{platform}
			prefix={`/console/${$newsletterStore.subdomain}/install`}
			websiteId={$newsletterStore.subdomain}
		/>
	</div>
</SingleBox>

<style>
	.install {
		padding: 20px 50px;
		height: 100%;
		overflow: auto;
		display: flex;
		flex-direction: column;

		:global(ol) {
			margin: 1em 0 0 !important;
			padding: 0 !important;
			display: flex;
			flex-direction: column;
			gap: 1rem;
			counter-reset: steps;
			list-style: none;
		}

		:global(ol > li) {
			padding: 25px 0 0 25px !important;
			margin: 16px 0 0 !important;
			position: relative;
			border-top: 1px solid var(--accent-light);
		}

		:global(ol > li:before) {
			counter-increment: steps;
			content: counter(steps);
			position: absolute;
			left: 0;
			top: -10px;
			width: 20px;
			height: 20px;
			background-color: var(--accent);
			border-radius: 50%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			z-index: 1;
			font-weight: 600;
			font-size: 10px;
			color: #fff;
		}

		:global(ol > li:after) {
			content: '';
			position: absolute;
			left: -4px;
			top: -14px;
			width: 28px;
			height: 28px;
			background-color: var(--accent-light);
			border-radius: 50%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			z-index: 0;
		}
	}

	.icon {
		margin-left: 6px;
		vertical-align: middle;
		color: var(--accent);
	}

	h2 {
		text-align: center;
		padding-top: 35px !important;
		margin-top: 0 !important;
		margin-bottom: 30px !important;
		font-size: 30px !important;
	}

	.code {
		width: 50vw;
	}
</style>
