<script lang="ts">
	import type { Newsletter } from '../types';
	import RelativeTime from '../../console/@components/utils/RelativeTime.svelte';

	interface Props {
		newsletter: Newsletter;
		handleSelect: (newsletter: Newsletter) => void;
	}

	let { newsletter, handleSelect }: Props = $props();
</script>

<button class="row" onclick={() => handleSelect(newsletter)}>
	<div class="name">
		{newsletter.name}
	</div>

	<div class="subdomain">
		{newsletter.subdomain}
	</div>

	<div class="created-at">
		<RelativeTime unix={newsletter.created_at} />
	</div>
</button>

<style>
	.row {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 15px;
		background-color: var(--bg-light);
		border-radius: var(--box-radius);
		text-align: left;
		width: 100%;
	}

	.row:hover {
		background: var(--hover);
	}

	.name {
		width: 30%;
	}

	.subdomain {
		width: 25%;
	}

	.org {
		width: 20%;
	}

	.created-at {
		width: 25%;
	}
</style>
