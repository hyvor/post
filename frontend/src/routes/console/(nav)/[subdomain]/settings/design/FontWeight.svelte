<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown } from '@hyvor/design/components';
	import { newsletterEditingStore } from '../../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../../lib/stores/consoleStore';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';

	interface Props {
		heading?: boolean;
	}

	let { heading = false }: Props = $props();

	let show = $state(false);
	const newsletterDefaults = getAppConfig().newsletter_defaults;

	let currentValue = $state(
		heading
			? ($newsletterEditingStore.template_font_weight_heading ??
					newsletterDefaults.TEMPLATE_FONT_WEIGHT_HEADING)
			: ($newsletterEditingStore.template_font_weight ??
					newsletterDefaults.TEMPLATE_FONT_WEIGHT)
	);

	const fontWeights = [
		'normal',
		'bold',
		'100',
		'200',
		'300',
		'400',
		'500',
		'600',
		'700',
		'800',
		'900'
	];

	function handleChange(weight: string) {
		currentValue = weight;
		if (heading) {
			$newsletterEditingStore.template_font_weight_heading = weight;
		} else {
			$newsletterEditingStore.template_font_weight = weight;
		}
		show = false;
	}
</script>

<Dropdown bind:show width={200} position="top" align="center">
	{#snippet trigger()}
		<Button color="input">
			<span class="weight-value">{currentValue}</span>
			{#snippet end()}
				<IconCaretDown />
			{/snippet}
		</Button>
	{/snippet}
	{#snippet content()}
		<ActionList selection="single" selectionAlign="end">
			{#each fontWeights as weight}
				<ActionListItem
					on:click={() => handleChange(weight)}
					selected={currentValue === weight}
				>
					<span class="weight-value">{weight}</span>
				</ActionListItem>
			{/each}
		</ActionList>
	{/snippet}
</Dropdown>

<style>
	.weight-value {
		text-transform: capitalize;
	}
</style>
