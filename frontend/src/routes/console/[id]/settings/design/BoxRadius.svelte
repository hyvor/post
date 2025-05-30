<script lang="ts">
	import { Slider } from '@hyvor/design/components';
	import { getAppConfig } from '../../../lib/stores/consoleStore';
	import { newsletterEditingStore } from '../../../lib/stores/newsletterStore';

	const newsletterDefaults = getAppConfig().newsletter_defaults;
	const startValue = parseInt(
		$newsletterEditingStore.template_box_radius ?? newsletterDefaults.TEMPLATE_BOX_RADIUS
	);
</script>

<Slider
	min={0}
	max={30}
	step={1}
	value={startValue}
	onchange={(val: number) => ($newsletterEditingStore.template_box_radius = val + 'px')}
/>
