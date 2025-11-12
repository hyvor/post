<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { ExportStatus } from '../../../../types';
	import { getI18n } from '../../../../lib/i18n';
	export let status: ExportStatus;
	export let size: 'small' | 'medium' | 'large' = 'medium';
	$: color = getColor(status);
	function getColor(status: ExportStatus): 'blue' | 'green' | 'red' {
		if (status === 'pending') {
			return 'blue';
		} else if (status === 'completed') {
			return 'green';
		} else if (status === 'failed') {
			return 'red';
		}
		return 'blue';
	}
	const I18n = getI18n();
	$: text = I18n.t(('console.tools.export.status.' + status) as any);
</script>

<Tag {size} {color}>
	{text}
</Tag>