<script lang="ts">
	import { Tag } from '@hyvor/design/components';
    import type {ExportStatus, ImportStatus} from '../../../../types';
	import { getI18n } from '../../../../lib/i18n';
	export let status: ImportStatus;
	export let size: 'small' | 'medium' | 'large' = 'medium';
	$: color = getColor(status);
	function getColor(status: ImportStatus): 'blue' | 'green' | 'red' {
		if (status === 'requires_input') {
			return 'blue';
		} else if (status === 'completed') {
			return 'green';
		} else if (status === 'failed') {
			return 'red';
		}
		return 'blue';
	}
	const I18n = getI18n();
	$: text = I18n.t(('console.tools.import.status.' + status) as any);
</script>

<Tag {size} {color}>
	{text}
</Tag>
