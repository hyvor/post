<script lang="ts">
	import { Tag } from '@hyvor/design/components';

	export let size: 'x-small' | 'small' | 'medium' | 'large' = 'medium';
	export let role: 'owner' | 'admin' = 'owner';
</script>

{#if role === 'owner'}
	<Tag {size} color="blue">Owner</Tag>
{:else if role === 'admin'}
	<Tag {size} color="orange">Admin</Tag>
{/if}
