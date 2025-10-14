<script lang="ts">
	import { Slider } from '@hyvor/design/components';
	import { getAppConfig } from '../../../lib/stores/consoleStore';
	import { newsletterEditingStore } from '../../../lib/stores/newsletterStore';

	const newsletterDefaults = getAppConfig().newsletter_defaults;
	const startValue = parseInt(
		$newsletterEditingStore.template_font_size ?? newsletterDefaults.TEMPLATE_FONT_SIZE
	);
</script>

<Slider
	min={8}
	max={30}
	step={1}
	value={startValue}
	onchange={(val: number) => ($newsletterEditingStore.template_font_size = val + 'px')}
/>
