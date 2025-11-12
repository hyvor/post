<script lang="ts">
	interface Props {
		value: string;
		name: string;
	}

	let { value, name }: Props = $props();
</script>

<div class="sent-stat">
	<span class="value">{value}</span>
	<span class="name">{name}</span>
</div>

<style>
	.sent-stat {
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.value {
		font-weight: 600;
	}

	.name {
		color: var(--text-light);
		font-size: 12px;
	}
</style>
