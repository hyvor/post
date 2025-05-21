<script>
	import { SplitControl } from '@hyvor/design/components';
	import SettingsBody from '../../settings/@components/SettingsBody.svelte';
</script>

<SettingsBody>
	<SplitControl label="New Import">
		{#snippet nested()}
			<SplitControl label="Import File">
				<input type="file" />
			</SplitControl>
		{/snippet}
	</SplitControl>

	<SplitControl label="Import History">
		<!--  -->
	</SplitControl>
</SettingsBody>
